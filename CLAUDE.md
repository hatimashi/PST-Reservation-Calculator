# CLAUDE.md — pst-reservation-calculator

## Opis projektu

Wtyczka WordPress do rezerwacji pojazdów. Użytkownik wybiera okres wynajmu, wtyczka oblicza całkowity koszt (wynajem + koszty stałe) i umożliwia wysłanie zapytania przez formularz kontaktowy. Admin zarządza sezonami, typami pojazdów, opłatami i kodami rabatowymi przez panel WP.

**Główny plik:** `pst-reservation-calculator.php`

---

## Rzeczywista struktura projektu

```
pst-reservation-calculator/
├── pst-reservation-calculator.php          # Bootstrap wtyczki (stałe, activation hooks, run)
├── uninstall.php                           # Czyszczenie DB i opcji przy odinstalowaniu
├── includes/
│   ├── class-pst-reservation-calculator.php           # Rdzeń — rejestracja wszystkich hooków
│   ├── class-pst-reservation-calculator-loader.php    # Rejestr hooków add_action/add_filter
│   ├── class-pst-reservation-calculator-activator.php # Tworzenie tabeli DB + inicjalizacja opcji
│   ├── class-pst-reservation-calculator-deactivator.php
│   ├── class-pst-reservation-calculator-i18n.php
│   └── class-pst-reservation-calculator-tables.php    # Nazwa tabeli + get_vehicle_types()
├── admin/
│   ├── class-pst-reservation-calculator-admin.php     # Logika admina + handle_ajax()
│   ├── partials/
│   │   └── pst-reservation-calculator-admin-display.php  # Widok panelu (5 tabów)
│   ├── css/
│   │   ├── pst-reservation-calculator-admin.css
│   │   ├── bootstrap.min.css
│   │   └── jquery.dataTables.min.css
│   └── js/
│       ├── pst-reservation-calculator-admin.js        # Cały JS admina
│       ├── bootstrap.min.js
│       ├── jquery.dataTables.min.js
│       ├── jquery.notifyBar.js
│       └── validate.min.js
├── public/
│   ├── class-pst-reservation-calculator-public.php    # Logika publiczna + AJAX kalkulatora
│   ├── partials/
│   │   └── pst-reservation-calculator-public-display.php  # Widok kalkulatora + popup formularza
│   ├── css/
│   │   ├── pst-reservation-calculator-public.css
│   │   ├── form.css
│   │   ├── datepicker.css
│   │   └── jquery-ui.css
│   └── js/
│       ├── pst-reservation-calculator-public.js       # Kalkulator, rabat, formularz
│       ├── datepicker.js / datepicker.pl-PL.js
│       └── validate.min.js
├── languages/
│   └── pst-reservation-calculator.pot
└── assets/
    └── icon.svg
```

---

## Baza danych

**Tabela:** `{prefix}pst_reservation`

| Kolumna | Typ | Opis |
| --- | --- | --- |
| id | int AUTO_INCREMENT | PK |
| price | decimal(10,2) | Cena za dzień |
| type | tinytext | Slug pojazdu (np. `kamper`) |
| description | tinytext | Pomocniczy klucz sezonu (np. `niski_1`) |
| season | tinytext | Nazwa sezonu (np. `niski`) |
| date_start | date | Początek okresu |
| date_end | date | Koniec okresu |
| last_change | timestamp | Auto-update |

Rok w datach jest ignorowany przy obliczeniach — liczą się tylko miesiąc i dzień.

---

## Opcje WordPress (wp_options)

| Klucz opcji | Zawartość |
| --- | --- |
| `pst_reservation_calculator_vehicle_types` | `array( slug => label )` — dynamiczne typy pojazdów |
| `pst_reservation_calculator_fees` | `array( slug => array( service_pay_netto, service_pay_brutto, deposit, delivery_netto, delivery_brutto ) )` |
| `pst_reservation_calculator_vat` | int — stawka VAT w % |
| `pst_reservation_calculator_email` | string — adres do powiadomień |
| `pst_reservation_calculator_discount_codes` | `array` kodów: `{ code, type, value, active }` — type to `percent` lub `fixed` |

Wszystkie opcje są tworzone przy aktywacji wtyczki (`PST_Reservation_Calculator_Activator::init_options()`). Usuwane przy odinstalowaniu (`uninstall.php`).

---

## AJAX — endpointy

### Admin (wymaga `manage_options` + nonce `pst_rc_admin_nonce`)

Akcja WP: `wp_ajax_pst_rc_admin` → `handle_ajax()`, rozgałęzienie po `param`:

| param | Opis |
| --- | --- |
| `save_seasons` | UPDATE istniejących wierszy (klucz numeryczny) + INSERT nowych (klucz `new_N`) |
| `delete_season` | DELETE wiersza po `id` |
| `add_vehicle_type` | Dodaje slug+label do opcji, kopiuje sezony z `kamper` jako wzorzec |
| `delete_vehicle_type` | Usuwa z opcji + usuwa wszystkie sezony + usuwa z fees |
| `save_settings` | Zapisuje VAT i fees (dynamicznie dla wszystkich typów) |
| `add_discount_code` | Dodaje kod do opcji `discount_codes` |
| `delete_discount_code` | Usuwa kod po `code` (string) |
| `toggle_discount_code` | Przełącza `active` kodu |
| `save_contact` | Zapisuje adres e-mail |

### Public (nonce `pst_rc_public_nonce`, dostępne też dla zalogowanych)

| Akcja WP | Metoda | Opis |
| --- | --- | --- |
| `pst_rc_calculate` | `ajax_calculate()` | Zwraca sumę + opłaty + delivery dla wybranego okresu |
| `pst_rc_email` | `ajax_email()` | Wysyła e-mail rezerwacyjny (zawiera kod rabatowy jeśli podany) |
| `pst_rc_validate_discount` | `ajax_validate_discount()` | Waliduje kod rabatowy, zwraca `{ type, value }` |

---

## Typy pojazdów — mechanizm

- Przechowywane w opcji `pst_reservation_calculator_vehicle_types` jako `array( slug => label )`
- Domyślne: `kamper`, `przyczepa`, `samochod`
- Dostęp wszędzie przez `PST_Reservation_Calculator_Tables::get_vehicle_types()` (metoda statyczna)
- Po dodaniu nowego typu — sezony kopiowane są automatycznie z typu `kamper`
- Przy usunięciu typu — sezony i opłaty są kasowane z DB/opcji
- Panel admin: tab **Pojazdy**

---

## Kody rabatowe — mechanizm

- Przechowywane w opcji `pst_reservation_calculator_discount_codes`
- Typy: `percent` (od wartości wynajmu) lub `fixed` (stała kwota w zł)
- Klient wpisuje kod w formularzu kalkulatora → JS wywołuje `pst_rc_validate_discount` → w razie sukcesu rabat stosowany jest do sumy netto przed wyliczeniem brutto
- Kod trafia do e-maila rezerwacyjnego w polu "Zastosowany kod rabatowy"
- Panel admin: tab **Kody rabatowe**

---

## Shortcode

```
[pst_reservation type="kamper"]
[pst_reservation type="przyczepa"]
[pst_reservation type="samochod"]
[pst_reservation type="<dowolny-slug>"]  ← dla dynamicznie dodanych typów
```

---

## Konwencje kodowania

- **Prefiks funkcji i klas:** `pst_` / `PST_`
- **Nazewnictwo:** snake_case dla funkcji/zmiennych, PascalCase dla klas
- Brak zewnętrznych zależności (composer/npm)
- Hooki rejestrowane przez `PST_Reservation_Calculator_Loader` w `class-pst-reservation-calculator.php`

---

## Stan projektu

### Zrealizowane

- Kalkulator kosztów sezonowych (wynajem dzienny × dni + opłaty stałe)
- Formularz zapytania z wysyłką e-mail
- Panel admina z 5 tabami: Sezony / Pojazdy / Ustawienia / Kody rabatowe / Kontakt
- Dodawanie i usuwanie sezonów w panelu (bez przeładowania strony)
- Dynamiczne typy pojazdów — dodawanie i usuwanie z panelu
- Konfiguracja opłat per typ pojazdu (serwisowa netto/brutto, kaucja, podstawienie netto/brutto)
- Kody rabatowe (procentowe i kwotowe) — zarządzanie w panelu, walidacja po stronie klienta i serwera
- Dynamiczne `delivery_netto` / `delivery_brutto` w widoku publicznym

### Backlog / przyszłe pomysły

- *(wpisuj tutaj)*

---

## Środowisko deweloperskie

- **Serwer lokalny:** (uzupełnij: LocalWP / XAMPP / Laragon / Docker)
- **WordPress:** lokalnie zainstalowany
- **PHP:** (uzupełnij wersję, np. 8.2)
- **Wdrożenie:** `wp-content/plugins/pst-reservation-calculator/`

---

## Ważne wskazówki dla Claude Code

- `PST_Reservation_Calculator_Tables::get_vehicle_types()` to jedyne źródło prawdy o dostępnych typach pojazdów — nigdy nie hardcoduj `['kamper', 'przyczepa', 'samochod']`
- Przy dodawaniu nowych pól do `fees` — zaktualizuj: `activator` (domyślne wartości), `admin handle_ajax save_settings`, `admin display` (formularz), `public add_fees()`, `public JS renderPrices()`
- Po zmianie schematu DB — wymagana dezaktywacja + aktywacja wtyczki (aktywator sprawdza `SHOW TABLES LIKE`)
- Zapis sezonów: klucz numeryczny → UPDATE, klucz `new_N` → INSERT; po zapisie strona przeładowuje się by nowe wiersze dostały prawdziwe DB ID
- Nonce admina: `pst_rc_admin_nonce`; nonce publiczny: `pst_rc_public_nonce`
- Przed modyfikacją pliku zawsze przeczytaj jego aktualną zawartość
- Ten plik aktualizuj po każdej większej zmianie w projekcie
