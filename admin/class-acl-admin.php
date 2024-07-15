<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class acl_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'acl_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'acl_enqueue_scripts' ) );
    }

    public function acl_admin_menu() {
        add_submenu_page(
            'tools.php',
            'ACL Apply Roles',
            'ACL Apply Roles',
            'manage_options',
            'acl-apply-roles',
            array( $this, 'acl_admin_page' )
        );
    }

    public function acl_enqueue_scripts( $hook ) {
        if ( 'tools_page_acl-apply-roles' !== $hook ) {
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
}

new acl_Admin();
