<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class acl_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'acl_admin_menu' ) );
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

    public function acl_admin_page() {
        if ( isset( $_POST['acl_apply_roles'] ) ) {
            $manager = new acl_WishlistMemberRoleManager();
            $manager->acl_apply_roles_to_all_members();
            echo '<div class="updated"><p>Roles applied to all members.</p></div>';
        }
        ?>
        <div class="wrap">
            <h1>Apply Roles to All Members</h1>
            <form method="post" action="">
                <p>
                    <input type="submit" name="acl_apply_roles" class="button-primary" value="Apply Roles" />
                </p>
            </form>
        </div>
        <?php
    }
}

new acl_Admin();
