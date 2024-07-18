<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function acl_settings_page() {
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
