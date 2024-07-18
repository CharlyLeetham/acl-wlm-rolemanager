<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class acl_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'acl_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'acl_register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'acl_enqueue_scripts' ) );
    }

    public function acl_admin_menu() {
        add_menu_page(
            'ACL Plugins',
            'ACL Plugins',
            'manage_options',
            'acl-plugins',
            array( $this, 'acl_plugins_page' ),
            'dashicons-admin-generic',
            6
        );

        add_submenu_page(
            'acl-plugins',
            'ACL Apply Roles',
            'ACL Apply Roles',
            'manage_options',
            'acl-apply-roles',
            array( $this, 'acl_admin_page' )
        );

        add_submenu_page(
            'acl-plugins',
            'ACL Settings',
            'ACL Settings',
            'manage_options',
            'acl-settings',
            array( $this, 'acl_settings_page' )
        );
    }

    public function acl_plugins_page() {
        // This can be used to create a landing page for the ACL Plugins menu, if needed.
        echo '<h1>ACL Plugins</h1>';
    }

    public function acl_admin_page() {
        ?>
        <div class="wrap">
            <h1>Apply Roles to All Members</h1>
            <form id="acl-form">
                <p>
                    <label for="acl-batch-size">Batch Size:</label>
                    <input type="number" id="acl-batch-size" name="batch_size" value="50" min="1">
                </p>
                <p>
                    <label for="acl-user-start">User ID Start:</label>
                    <input type="number" id="acl-user-start" name="user_start" min="1">
                </p>
                <p>
                    <label for="acl-user-end">User ID End:</label>
                    <input type="number" id="acl-user-end" name="user_end" min="1">
                </p>
                <button id="acl-start" class="button-primary">Start Applying Roles</button>
            </form>
            <div id="acl-progress"></div>
        </div>
        <?php
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

    public function acl_settings_page() {
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

    public function acl_enqueue_scripts( $hook ) {
        if ( 'acl-plugins_page_acl-apply-roles' !== $hook ) {
            return;
        }

        wp_enqueue_script(
            'acl-scripts',
            plugin_dir_url( __FILE__ ) . '../scripts/acl-scripts.js',
            array( 'jquery' ),
            '1.0',
            true
        );

        wp_localize_script( 'acl-scripts', 'aclAjax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'acl_apply_roles_batch_nonce' ),
        ));
    }
}

new acl_Admin();
