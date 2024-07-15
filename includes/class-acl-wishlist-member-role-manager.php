<?php

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
        $user = new WP_User( $user_id );
        if ( in_array( 'administrator', $user->roles ) ) {
            return;
        }

        foreach ( $levels as $level ) {
            $role = 'level_' . $level;
            if ( ! in_array( $role, $user->roles ) ) {
                $user->add_role( $role );
            }
        }
    }

    public function acl_remove_roles( $user_id, $levels ) {
        $user = new WP_User( $user_id );
        if ( in_array( 'administrator', $user->roles ) ) {
            return;
        }

        $all_levels = $this->acl_get_user_levels( $user_id );
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

    public function acl_apply_roles_to_all_members() {
        $args = array(
            'fields' => 'ID',
        );
        $user_ids = get_users( $args );
        foreach ( $user_ids as $user_id ) {
            $user = new WP_User( $user_id );
            if ( in_array( 'administrator', $user->roles ) ) {
                continue;
            }

            $levels = $this->acl_get_user_levels( $user_id );
            $this->acl_log( "User ID $user_id: Current levels: " . implode( ', ', $levels ) );

            $this->acl_remove_roles( $user_id, array() );
            $this->acl_log( "User ID $user_id: Roles removed." );

            $this->acl_assign_roles( $user_id, $levels );
            $this->acl_log( "User ID $user_id: Roles assigned based on levels: " . implode( ', ', $levels ) );
        }
    }

    private function acl_get_user_levels( $user_id ) {
        $levels = wlmapi_get_member_levels( $user_id );
        return is_array( $levels ) ? array_keys( $levels ) : array();
    }

    private function acl_log( $message ) {
        if ( WP_DEBUG === true ) {
            error_log( $message );
        }
    }
}
