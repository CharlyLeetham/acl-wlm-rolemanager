<?php
/*
Plugin Name: ACL Wishlist Member Role Manager
Plugin URI: https://askcharlyleetham.com/acl-wishlist-member-role-manager
Description: This plugin assigns WordPress roles to users based on their Wishlist Member levels. When a user is added to a level, they are assigned the corresponding role. When a user is removed from a level, the plugin ensures any roles not associated with other levels are also removed. If a user is moved to a different level, all levels are checked and roles are assigned accordingly.
Version: 1.0
Author: Charly Leetham
Author URI: https://askcharlyleetham.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: acl-wishlist-member-role-manager
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class acl_WishlistMemberRoleManager {

    public function __construct() {
        add_action( 'wishlistmember_add_user_levels', array( $this, 'acl_assign_roles' ), 10, 2 );
        add_action( 'wishlistmember_remove_user_levels', array( $this, 'acl_remove_roles' ), 10, 2 );
        add_action( 'wishlistmember_move_user_levels', array( $this, 'acl_update_roles_on_move' ), 10, 2 );
    }

    public function acl_assign_roles( $user_id, $levels ) {
        foreach ( $levels as $level ) {
            $role = 'level_' . $level;
            $user = new WP_User( $user_id );
            if ( ! in_array( $role, $user->roles ) ) {
                $user->add_role( $role );
            }
        }
    }

    public function acl_remove_roles( $user_id, $levels ) {
        $user = new WP_User( $user_id );
        $all_levels = wlmapiclass::GetUserLevels( $user_id );
        $all_roles = array_map( function( $level ) {
            return 'level_' . $level;
        }, $all_levels );

        foreach ( $user->roles as $role ) {
            if ( strpos( $role, 'level_' ) === 0 && ! in_array( $role, $all_roles ) ) {
                $user->remove_role( $role );
            }
        }
    }

    public function acl_update_roles_on_move( $user_id, $new_levels ) {
        $this->acl_remove_roles( $user_id, array() );
        $this->acl_assign_roles( $user_id, $new_levels );
    }
}

new acl_WishlistMemberRoleManager();
