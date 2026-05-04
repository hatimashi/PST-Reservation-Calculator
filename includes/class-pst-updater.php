<?php
class PST_Updater {

    private $plugin_slug;
    private $plugin_file;
    private $update_url;
    private $version;

    public function __construct( $plugin_file, $update_url, $version ) {
        $this->plugin_file  = $plugin_file;
        $this->plugin_slug  = plugin_basename( $plugin_file );
        $this->update_url   = $update_url;
        $this->version      = $version;

        add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
        add_filter( 'plugins_api', array( $this, 'plugin_info' ), 10, 3 );
        add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
    }

    /**
     * Odpytuje serwer i porównuje wersje
     */
    public function check_update( $transient ) {
        if ( empty( $transient->checked ) ) {
            return $transient;
        }

        $remote = $this->get_remote_info();

        if ( $remote && version_compare( $this->version, $remote->version, '<' ) ) {
            $transient->response[ $this->plugin_slug ] = (object) [
                'slug'        => dirname( $this->plugin_slug ),
                'plugin'      => $this->plugin_slug,
                'new_version' => $remote->version,
                'url'         => $remote->download_url,
                'package'     => $remote->download_url,
                'tested'      => $remote->tested,
            ];
        }

        return $transient;
    }

    /**
     * Okno z informacjami o wtyczce (przycisk "View details")
     */
    public function plugin_info( $result, $action, $args ) {
        if ( $action !== 'plugin_information' ) {
            return $result;
        }

        if ( dirname( $this->plugin_slug ) !== $args->slug ) {
            return $result;
        }

        $remote = $this->get_remote_info();

        if ( ! $remote ) {
            return $result;
        }

        return (object) [
            'name'          => $remote->name,
            'slug'          => dirname( $this->plugin_slug ),
            'version'       => $remote->version,
            'tested'        => $remote->tested,
            'requires'      => $remote->requires,
            'requires_php'  => $remote->requires_php,
            'last_updated'  => $remote->last_updated,
            'sections'      => (array) $remote->sections,
            'banners'       => (array) $remote->banners,
            'download_link' => $remote->download_url,
        ];
    }

    /**
     * Poprawia nazwę folderu po instalacji (WP czasem dodaje sufiks)
     */
    public function after_install( $response, $hook_extra, $result ) {
        global $wp_filesystem;

        $plugin_folder = WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . dirname( $this->plugin_slug );
        $wp_filesystem->move( $result['destination'], $plugin_folder );
        $result['destination'] = $plugin_folder;

        activate_plugin( $this->plugin_slug );

        return $result;
    }

    /**
     * Pobiera dane z serwera z cachowaniem
     */
    private function get_remote_info() {
        $cache_key = 'pst_update_info';
        $remote    = get_transient( $cache_key );

        if ( false === $remote ) {
            $response = wp_remote_get(
                $this->update_url,
                [ 'timeout' => 10, 'headers' => [ 'Accept' => 'application/json' ] ]
            );

            if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
                return false;
            }

            $remote = json_decode( wp_remote_retrieve_body( $response ) );
            set_transient( $cache_key, $remote, 12 * HOUR_IN_SECONDS ); // cache na 12h
        }

        return $remote;
    }
}