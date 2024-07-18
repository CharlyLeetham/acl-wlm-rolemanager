<?php
/*
Plugin Name: ACL Wishlist Member Role Manager
Plugin URI: https://askcharlyleetham.com/acl-wishlist-member-role-manager
Description: This plugin assigns WordPress roles to users based on their Wishlist Member levels. When a user is added to a level, they are assigned the corresponding role. When a user is removed from a level, the plugin ensures any roles not associated with other levels are also removed. If a user is moved to a different level, all levels are checked and roles are assigned accordingly.
Version: 1.5
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

// Include the main class
require_once plugin_dir_path( __FILE__ ) . 'includes/class-acl-wishlist-member-role-manager.php';

// Include the admin class
if ( is_admin() ) {
    //require_once plugin_dir_path( __FILE__ ) . 'admin/class-acl-admin.php';
}

// Initialize the plugin
new acl_WishlistMemberRoleManager();
if ( is_admin() ) {
    //new acl_Admin();
}
