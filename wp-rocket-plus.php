<?php
/*
Plugin Name: WP Rocket Plus
Description: Handy tools to enhance WP Rocket plugin.
Version: 1.0
Author: Gabor Angyal
Author URI: https://woodevops.com
*/

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'admin_menu', 'wp_rocket_plus_add_admin_menu' );

function wp_rocket_plus_add_admin_menu() {
    add_menu_page(
        'WP Rocket Plus Settings',
        'WP Rocket Plus',
        'manage_options',
        'wp-rocket-plus',
        'wp_rocket_plus_settings_page'
    );
}

function wp_rocket_plus_settings_page() {
    ?>
    <div class="wrap">
        <h1>WP Rocket Plus Settings</h1>
        <form method="post">
            <input type="hidden" name="wp_rocket_plus_button_action" value="1">
            <?php submit_button('Exclude all pages from used css feature', 'primary', 'execute_action'); ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_post_wp_rocket_plus_button_action', 'wp_rocket_plus_button_action_callback' );

function wp_rocket_plus_button_action_callback() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized user' );
    }

    set_rocket_exclude_remove_unused_css();

    wp_redirect( $_SERVER['HTTP_REFERER'] );
    exit;
}

add_action( 'admin_notices', 'wp_rocket_plus_action_success_notice' );
function wp_rocket_plus_action_success_notice() {
    $msg = get_transient('wp_rocket_plus_admin_notice');
    if ($msg != null) {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( $msg, 'wp-rocket-plus' ); ?></p>
    </div>
    <?php
    }
}

add_action( 'admin_init', 'wp_rocket_plus_handle_button_action' );

function wp_rocket_plus_handle_button_action() {
    if ( isset( $_POST['wp_rocket_plus_button_action'] ) ) {
        do_action( 'admin_post_wp_rocket_plus_button_action' );
    }
}

function set_rocket_exclude_remove_unused_css() {
    global $wpdb;

    $post_ids = $wpdb->get_col("
        SELECT ID FROM {$wpdb->posts} 
        WHERE (post_type = 'post' OR post_type = 'product' OR post_type = 'page') 
        AND post_status = 'publish'
    ");

    $term_ids = $wpdb->get_col("
        SELECT term_id FROM {$wpdb->term_taxonomy} 
        WHERE taxonomy = 'product_cat'
    ");

    foreach ($post_ids as $post_id) {
        update_post_meta($post_id, '_rocket_exclude_remove_unused_css', 1);
    }

    foreach ($term_ids as $term_id) {
        update_term_meta($term_id, '_rocket_exclude_remove_unused_css', 1);
    }

    set_transient('wp_rocket_plus_admin_notice', count($post_ids) . ' pages, and ' . count($term_ids) . ' category pages excluded from the used CSS function', 3);
}
