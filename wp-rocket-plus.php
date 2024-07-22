<?php
/*
Plugin Name: WP Rocket Plus
Description: Handy tools to enhance WP Rocket plugin.
Version: 1.0
Author: Gabor Angyal
Author URI: https://woodevops.com
*/

// Prevent direct access to the file
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

// Hook to add admin menu
add_action( 'admin_menu', 'wp_rocket_plus_add_admin_menu' );

function wp_rocket_plus_add_admin_menu() {
    add_menu_page(
        'WP Rocket Plus Settings', // Page title
        'WP Rocket Plus',          // Menu title
        'manage_options',     // Capability
        'wp-rocket-plus',          // Menu slug
        'wp_rocket_plus_settings_page' // Callback function
    );
}

function wp_rocket_plus_settings_page() {
    ?>
    <div class="wrap">
        <h1>WP Rocket Plus Settings</h1>
        <form method="post" action="options.php">
            <?php
            // Output security fields for the registered setting
            settings_fields( 'wp_rocket_plus_settings_group' );
            // Output setting sections and their fields
            do_settings_sections( 'wp-rocket-plus' );
            // Output save settings button
            submit_button();
            ?>
        </form>
        <form method="post">
            <input type="hidden" name="wp_rocket_plus_button_action" value="1">
            <?php submit_button('Execute Action', 'primary', 'execute_action'); ?>
        </form>
    </div>
    <?php
}

// Hook to register plugin settings
add_action( 'admin_init', 'wp_rocket_plus_settings_init' );

function wp_rocket_plus_settings_init() {
    register_setting( 'wp_rocket_plus_settings_group', 'wp_rocket_plus_settings' );

    add_settings_section(
        'wp_rocket_plus_settings_section',
        'Settings',
        'wp_rocket_plus_settings_section_callback',
        'wp-rocket-plus'
    );

    add_settings_field(
        'wp_rocket_plus_setting_field',
        'Setting Field',
        'wp_rocket_plus_setting_field_callback',
        'wp-rocket-plus',
        'wp_rocket_plus_settings_section'
    );
}

function wp_rocket_plus_settings_section_callback() {
    echo 'Enter your settings below:';
}

function wp_rocket_plus_setting_field_callback() {
    $setting = get_option( 'wp_rocket_plus_settings' );
    ?>
    <input type="text" name="wp_rocket_plus_settings[wp_rocket_plus_setting_field]" value="<?php echo isset( $setting['wp_rocket_plus_setting_field'] ) ? esc_attr( $setting['wp_rocket_plus_setting_field'] ) : ''; ?>">
    <?php
}

// Hook to handle button action
add_action( 'admin_post_wp_rocket_plus_button_action', 'wp_rocket_plus_button_action_callback' );

function wp_rocket_plus_button_action_callback() {
    log_action('wp_rocket_plus_button_action_callback');
    // Check if the user has the required capability
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( 'Unauthorized user' );
    }

    // Perform your custom action here
    // For example, let's just output a message to the admin area
    add_action( 'admin_notices', 'wp_rocket_plus_action_success_notice' );

    // Redirect to the settings page to prevent resubmission
    wp_redirect( $_SERVER['HTTP_REFERER'] );
    exit;
}

function wp_rocket_plus_action_success_notice() {
    log_action('wp_rocket_plus_action_success_notice');
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Action executed successfully!', 'wp-rocket-plus' ); ?></p>
    </div>
    <?php
}

// Handle the button action form submission
add_action( 'admin_init', 'wp_rocket_plus_handle_button_action' );

function wp_rocket_plus_handle_button_action() {
    if ( isset( $_POST['wp_rocket_plus_button_action'] ) ) {
        log_action('wp_rocket_plus_handle_button_action');
        do_action( 'admin_post_wp_rocket_plus_button_action' );
    }
}

function log_action($msg) {
    $upload_dir = wp_upload_dir();
    $log_file = $upload_dir['basedir'] . '/my_plugin_log.txt';
    $current_time = current_time( 'mysql' );

    $log_message = "Action executed at: " . $current_time . " " . $msg . "\n";

    file_put_contents( $log_file, $log_message, FILE_APPEND | LOCK_EX );
}
