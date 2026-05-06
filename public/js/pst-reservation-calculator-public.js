(function ($) {
  "use strict";

  $(function () {
    $("#EndDate").datepicker({ language: "pl-PL" });
    $("#StartDate").datepicker({ language: "pl-PL" });
  });

  jQuery(document).ready(function () {
    var vat = parseInt(pst_rc_ajax.vat, 10) || 23;
    var discount = { type: null, value: 0 };
    var lastData = {};

    document.getElementById("h1-title").textContent = pst_rc_ajax.thetitleattribute || "Kalkulator rezerwacji"; 
    // ---- Oblicz cenę po rabacie ----
    function applyDiscount(netto) {
      if (!discount.type || !discount.value) {
        return netto;
      }
      var result =
        discount.type === "percent"
          ? netto * (1 - discount.value / 100)
          : netto - discount.value;
      return Math.max(0, Math.round(result * 100) / 100);
    }

    // ---- Wyrenderuj ceny w DOM ----
    function renderPrices(data) {
      lastData = data;

      jQuery(".service_pay_netto").text(data.service_pay_netto || "");
      jQuery(".service_pay_brutto").text(data.service_pay_brutto || "");
      jQuery(".deposit").text(data.deposit || "");
      jQuery(".delivery_netto").text(data.delivery_netto || "");
      jQuery(".delivery_brutto").text(data.delivery_brutto || "");

      if (data.sum) {
        var netto = applyDiscount(parseFloat(data.sum) || 0);
        var brutto = Math.round(((netto / 100) * vat + netto) * 100) / 100;

        jQuery(".wyniknetto").text(netto).val(netto);
        jQuery("#wyniknetto").val(netto);
        jQuery(".wynikbrutto").text(brutto).val(brutto);
        jQuery("#wynikbrutto").val(brutto);

        jQuery("#DateStartPop").val(jQuery("#StartDate").val());
        jQuery("#DateEndPop").val(jQuery("#EndDate").val());
      }
    }

    // ---- Wywołaj AJAX kalkulatora ----
    function postCalculate() {
      var postdata =
        jQuery("#formCalculate").serialize() +
        "&action=pst_rc_calculate" +
        "&param=calculate_prices" +
        "&nonce=" +
        pst_rc_ajax.nonce;

      jQuery.post(pst_rc_ajax.ajaxurl, postdata, function (response) {
        var data =
          typeof response === "string" ? JSON.parse(response) : response;
        renderPrices(data);
      });
    }

    // Wczytaj opłaty przy starcie strony (bez dat)
    postCalculate();

    // Przelicz przy każdej zmianie daty
    jQuery("#formCalculate").on("input", function () {
      postCalculate();
    });

    // ---- Kod rabatowy ----
    jQuery("#kc-apply-discount").on("click", function () {
      var code = jQuery.trim(jQuery("#discount_code").val().toUpperCase());
      jQuery("#discount_code").val(code);

      var $msg = jQuery("#kc-discount-msg");

      if (!code) {
        discount = { type: null, value: 0 };
        jQuery("#popup_discount_code").val("");
        $msg.hide();
        renderPrices(lastData);
        return;
      }

      jQuery.post(
        pst_rc_ajax.ajaxurl,
        {
          action: "pst_rc_validate_discount",
          nonce: pst_rc_ajax.nonce,
          code: code,
        },
        function (response) {
          if (response && response.success) {
            discount = response.data;
            var info =
              discount.type === "percent"
                ? "−" + discount.value + "%"
                : "−" + discount.value + " zł";
            $msg
              .text("Rabat zastosowany: " + info)
              .css("color", "green")
              .show();
            jQuery("#popup_discount_code").val(code);
          } else {
            discount = { type: null, value: 0 };
            jQuery("#popup_discount_code").val("");
            $msg
              .text((response && response.data) || "Nieprawidłowy kod.")
              .css("color", "red")
              .show();
          }
          renderPrices(lastData);
        },
      );
    });

    // ---- Walidacja formularza kalkulatora → otwórz popup ----
    jQuery("#formCalculate").validate({
      submitHandler: function () {
        jQuery(".popup-calc").css("display", "grid");
      },
    });

    // ---- Wysyłka e-maila rezerwacyjnego ----
    /* jQuery("#msf").validate({
      submitHandler: function () {
        var postdata =
          jQuery("#msf").serialize() +
          "&action=pst_rc_email" +
          "&param=send_email" +
          "&nonce=" +
          pst_rc_ajax.nonce;

        jQuery.post(pst_rc_ajax.ajaxurl, postdata, function (response) {
          jQuery(".popup-calc").css("display", "none");
          jQuery("#mailInfoPopup").css("display", "block");
          var msg =
            response == 1
              ? "Wiadomość wysłana poprawnie."
              : "Błąd wysyłania wiadomości. Skontaktuj się z nami telefonicznie.";
          jQuery("#mailInfoPopup span").text(msg);
        });
      },
      invalidHandler: function (event, validator) {
        var errors = validator.numberOfInvalids();
        if (errors) {
          var message =
            errors === 1
              ? "Nie uzupełniłeś 1 pola. Pole zostało podświetlone."
              : "Uzupełnij " + errors + " pola. Pola zostały podświetlone.";
          $("div.error span").html(message);
          $("div.error").show();
        } else {
          $("div.error").hide();
        }
      },
    }); */

    lucide && lucide.createIcons?.();
    const form = document.getElementById("msf");
    const formCalculate = document.getElementById("formCalculate");
    const steps = Array.from(document.querySelectorAll(".step"));
    const backBtn = document.getElementById("backBtn");
    const nextBtn = document.getElementById("nextBtn");
    const submitBtn = document.getElementById("submitBtn");
    const progress = document.getElementById("progress");
    const dots = Array.from(document.querySelectorAll(".step-dot"));
    const success = document.getElementById("success");
    let current = 0;
    // Simple validators
    const rules = {
      0: () => {
        const email = form.email;
        const fullname = form.fullname;
        const phone = form.phone;
        const tos = document.getElementById("tos1");
        let ok = true;
        const validEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value);
        toggleError("email", !validEmail);
        ok = ok && validEmail;
        const validName = fullname.value.trim().length > 1;
        toggleError("fullname", !validName);
        ok = ok && validName;
        const validPhone = phone.value.trim().length >= 8;
        toggleError("phone", !validPhone);
        ok = ok && validPhone;
        ok = ok && tos.checked;
        return ok;
      },
      1: () => {
        const extramessageValid = form.extramessage.value.trim().length > 0;
        toggleError("extramessage", !extramessageValid);
        return extramessageValid;
      },
      2: () => {
        const consent = document.getElementById("consent").checked;
        toggleError("consent", !consent);
        return consent;
      },
    };

    function toggleError(key, show) {
      const el = document.querySelector(`[data-error="${key}"]`);
      if (!el) return;
      el.classList.toggle("hidden", !show);
    }

    function showStep(i) {
      steps.forEach((s, idx) => {
        s.classList.toggle("hidden", idx !== i);
      });
      // Buttons
      backBtn.disabled = i === 0;
      nextBtn.classList.toggle("hidden", i === steps.length - 1);
      submitBtn.classList.toggle("hidden", i !== steps.length - 1);
      // Progress & dots
      const pct = (i / (steps.length - 1)) * 100;
      progress.style.width = `${pct}%`;
      dots.forEach((d, idx) => {
        d.className = `step-dot h-8 w-8 rounded-full grid place-items-center border 
         ${idx <= i ? "bg-white/20 border-white/40" : "bg-white/10 border-white/20"}`;
      });
      // Review data on step 3
      if (i === 2) {
        document.getElementById("r-email").textContent =
          form.email.value || "—";
        document.getElementById("r-fullname").textContent =
          form.fullname.value || "—";
        document.getElementById("r-extramessage").textContent =
          form.extramessage.value || "—";
        document.getElementById("r-phone").textContent =
          form.phone.value || "—";
        document.getElementById("r-dates").textContent =
          formCalculate.start_date.value +
            " do " +
            formCalculate.end_date.value || "—";
        document.getElementById("r-type").textContent =
          formCalculate.type.value || "—";
        document.getElementById("r-additional-fees").textContent =
          document.getElementById("additional_fees").textContent || "—";
        document.getElementById("r-reservation-sum").textContent =
          formCalculate.wyniknetto.value +
            " / " +
            formCalculate.wynikbrutto.value || "—";
      }
    }
    nextBtn.addEventListener("click", () => {
      const validate = rules[current] ?? (() => true);
      if (!validate()) return;
      current = Math.min(current + 1, steps.length - 1);
      showStep(current);
    });
    backBtn.addEventListener("click", () => {
      current = Math.max(current - 1, 0);
      showStep(current);
    });
form.addEventListener("submit", (e) => {
  e.preventDefault();
  const validate = rules[current] ?? (() => true);
  if (!validate()) return;

  submitBtn.disabled = true;
  submitBtn.textContent = "Wysyłanie…";

  // serializuj oba formularze - msf (dane kontaktowe) + formCalculate (daty, kwoty)
  var postdata =
    jQuery("#msf").serialize() +
    "&" +
    jQuery("#formCalculate").serialize() +
    "&action=pst_rc_email" +
    "&param=send_email" +
    "&nonce=" +
    pst_rc_ajax.nonce;

  jQuery.post(pst_rc_ajax.ajaxurl, postdata, function (response) {
    if (response == 1) {
      jQuery("#msf").addClass("hidden");
      jQuery("#success").removeClass("hidden");
    } else {
      submitBtn.disabled = false;
      submitBtn.textContent = "Submit";
      alert("Błąd wysyłania. Skontaktuj się z nami telefonicznie.");
    }
  });
});
    // Initial paint
    showStep(0);

    // ---- Zamknij popup ----
    jQuery(".close-popup").on("click", function () {
      jQuery(".popup-calc").css("display", "none");
    });
  });
})(jQuery);
