<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class acl_WishlistMemberRoleManager {

    public function __construct() {
        add_action( 'wishlistmember_add_user_levels', array( $this, 'acl_assign_roles' ), 10, 2 );
        add_action( 'wishlistmember_remove_user_levels', array( $this, 'acl_remove_roles' ), 10, 2 );
        add_action( 'wishlistmember_move_user_levels', array( $this, 'acl_update_roles_on_move' ), 10, 3 );
        add_action( 'wp_ajax_acl_apply_roles_batch', array( $this, 'acl_apply_roles_batch' ) );
    }

    public function acl_assign_roles( $user_id, $levels ) {
        $user = new WP_User( $user_id );
        if ( in_array( 'administrator', $user->roles ) ) {
            return;
        }

        $this->acl_log( "ACL Role Manager: User ID $user_id: Current roles: " . implode( ', ', $user->roles ) );

        foreach ( $levels as $level_id ) {
            $level_data = wlmapi_the_level( $level_id );
            if ( !is_wp_error( $level_data ) && !empty( $level_data['level'] ) && !empty( $level_data['level']['wordpress_role'] ) ) {
                $role = $level_data['level']['wordpress_role'];
                if ( ! in_array( $role, $user->roles ) ) {
                    $user->add_role( $role );
                    $this->acl_log( "ACL Role Manager: User ID $user_id: Role assigned based on level '{$level_data['level']['name']}': $role" );
                }
            }
        }
    }

    public function acl_remove_roles( $user_id, $levels ) {
        $this->acl_log( "ACL Role Manager: Entered acl_remove_roles. User ID: $user_id, Levels: " . implode( ', ', $levels ) );

        $user = new WP_User( $user_id );
        if ( in_array( 'administrator', $user->roles ) ) {
            return;
        }

        $all_levels = $this->acl_get_user_levels( $user_id );
        $all_roles = array_map( function( $level_id ) {
            $level_data = wlmapi_the_level( $level_id );
            return !is_wp_error( $level_data ) && !empty( $level_data['level'] ) ? $level_data['level']['wordpress_role'] : null;
        }, $all_levels );
        $all_roles = array_filter( $all_roles );

        $this->acl_log( "ACL Role Manager: User ID $user_id: All roles: " . implode( ', ', $all_roles ) );

        foreach ( $user->roles as $role ) {
            $this->acl_log( "ACL Role Manager: User ID $user_id: Checking role: $role" );
            if ( ! in_array( $role, $all_roles ) ) {
                $user->remove_role( $role );
                $this->acl_log( "ACL Role Manager: User ID $user_id: Role removed: $role" );
            }
        }
    }

    public function acl_update_roles_on_move( $user_id, $removed_levels, $added_levels ) {
        $this->acl_remove_roles( $user_id, $removed_levels );
        $this->acl_assign_roles( $user_id, $added_levels );
    }

    public function acl_apply_roles_batch() {
        check_ajax_referer( 'acl_apply_roles_batch_nonce', 'nonce' );

        $batch_size = isset( $_POST['batch_size'] ) ? intval( $_POST['batch_size'] ) : 50;
        $user_start = isset( $_POST['user_start'] ) ? intval( $_POST['user_start'] ) : 0;
        $user_end = isset( $_POST['user_end'] ) ? intval( $_POST['user_end'] ) : 0;
        $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;

        $args = array(
            'fields' => 'ID',
            'number' => $batch_size,
            'offset' => $offset,
        );

        if ( $user_start > 0 && $user_end > 0 ) {
            $args['include'] = range( $user_start, $user_end );
        }

        $user_ids = get_users( $args );
        foreach ( $user_ids as $user_id ) {
            $user = new WP_User( $user_id );
            if ( in_array( 'administrator', $user->roles ) ) {
                continue;
            }

            $levels = $this->acl_get_user_levels( $user_id );
            $this->acl_log( "ACL Role Manager: User ID $user_id: Current levels: " . implode( ', ', $levels ) );

            $this->acl_remove_roles( $user_id, $levels );
            $this->acl_log( "ACL Role Manager: User ID $user_id: Roles removed." );

            $this->acl_assign_roles( $user_id, $levels );
            $this->acl_log( "ACL Role Manager: User ID $user_id: Roles assigned." );
        }

        wp_send_json_success( array(
            'offset' => $offset + $batch_size,
            'finished' => count( $user_ids ) < $batch_size
        ));
    }

    private function acl_get_user_levels( $user_id ) {
        $levels = wlmapi_get_member_levels( $user_id );
        return is_array( $levels ) ? array_keys( $levels ) : array();
    }

    private function acl_log( $message ) {
        $logging_enabled = get_option( 'acl_logging_enabled', 'no' );
        if ( $logging_enabled === 'yes' && WP_DEBUG === true ) {
            error_log( $message );
        }
    }
}
