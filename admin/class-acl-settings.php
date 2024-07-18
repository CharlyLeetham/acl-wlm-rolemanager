<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class acl_Settings {

    public function __construct() {
        add_action( 'admin_init', array( $this, 'acl_register_settings' ) );
    }

    public function acl_register_settings() {
        register_setting( 'acl-settings-group', 'acl_logging_enabled' );

        add_settings_section(
            'acl-settings-section',
            'ACL Wishlist Member Role Manager Settings',
            null,
            'acl-settings'
        );

        add_settings_field(
            'acl_logging_enabled',
            'Enable Logging',
            array( $this, 'acl_logging_enabled_callback' ),
            'acl-settings',
            'acl-settings-section'
        );
    }

    public function acl_logging_enabled_callback() {
        $logging_enabled = get_option( 'acl_logging_enabled', 'no' );
        ?>
        <input type="checkbox" name="acl_logging_enabled" value="yes" <?php checked( 'yes', $logging_enabled ); ?>>
        <?php
    }

    public static function acl_settings_page() {
        ?>
        <div class="wrap">
            <h1>ACL Wishlist Member Role Manager Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields( 'acl-settings-group' ); ?>
                <?php do_settings_sections( 'acl-settings' ); ?>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}

new acl_Settings();
