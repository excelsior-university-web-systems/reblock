<?php
namespace eslin87\ReBlock;

if ( !defined( 'ABSPATH' ) ) { exit; }

function reblock_register_settings_page() {
    add_submenu_page(
        'edit.php?post_type='.REBLOCK_POST_TYPE_NAME,
        'ReBlock Settings',
        'Settings',
        'manage_options',
        'reblock-settings',
        __NAMESPACE__.'\\reblock_settings_page'
    );
}

add_action( 'admin_menu', __NAMESPACE__.'\\reblock_register_settings_page' );

function reblock_settings_page() {
    
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'ReBlock Settings', 'reblock' ); ?></h1>
        <form action="options.php" method="post">
            <?php
                settings_fields( 'reblock_settings_group' );
                do_settings_sections( 'reblock_settings' );
                submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}

function reblock_register_settings() {

    if ( EXCELSIOR_BOOTSTRAP_EDITOR_SUPPORT ) {
        /*** EXCELSIOR BOOTSTRAP EDITOR ***/

        // Register a new setting for "reblock_settings_group".
        register_setting( 'reblock_settings_group', 'reblock_start_with_excelsior_bootstrap' );

        // Add Excelsior Bootstrap Editor Section
        add_settings_section(
            'reblock_excelsior_bootstrap_editor', // Section ID
            __( 'Excelsior Bootstrap Editor', 'reblock' ), // Title for the section
            __NAMESPACE__.'\\reblock_excelsior_bootstrap_editor_section', // Callback function for section description
            'reblock_settings' // Page on which to add this section
        );

        // Add Excelsior Bootstrap Editor Checkbox
        add_settings_field(
            'reblock_start_with_excelsior_bootstrap', // Field ID
            __( 'Block Editor', 'reblock' ), // Field title/label
            __NAMESPACE__.'\\reblock_start_with_excelsior_bootstrap', // Callback function to render the field
            'reblock_settings', // Page on which to add this field
            'reblock_excelsior_bootstrap_editor' // Section in which to add the field
        );

        /*** Styles and JavaScript ***/

        register_setting( 'reblock_settings_group', 'reblock_show_wp_admin_bar' );

        register_setting( 'reblock_settings_group', 'reblock_allowed_styles', array(
			'sanitize_callback' => __NAMESPACE__.'\\reblock_sanitize_styles_scripts'
		) );

        register_setting( 'reblock_settings_group', 'reblock_allowed_scripts', array(
			'sanitize_callback' => __NAMESPACE__.'\\reblock_sanitize_styles_scripts'
		) );

        add_settings_section(
            'reblock_styles_scripts',
            __( 'Styles and Scripts', 'reblock' ),
            __NAMESPACE__.'\\reblock_styles_scripts_section',
            'reblock_settings'
        );

        add_settings_field(
            'reblock_show_wp_admin_bar',
            __( 'Admin Bar', 'reblock' ),
            __NAMESPACE__.'\\reblock_show_wp_admin_bar',
            'reblock_settings',
            'reblock_styles_scripts'
        );

        add_settings_field(
            'reblock_allowed_styles',
            __( 'Styles', 'reblock' ),
            __NAMESPACE__.'\\reblock_allowed_styles',
            'reblock_settings',
            'reblock_styles_scripts'
        );

        add_settings_field(
            'reblock_allowed_scripts',
            __( 'Scripts', 'reblock' ),
            __NAMESPACE__.'\\reblock_allowed_scripts',
            'reblock_settings',
            'reblock_styles_scripts'
        );

    }
    
}

add_action( 'admin_init', __NAMESPACE__.'\\reblock_register_settings' );

// Excelsior Bootstrap Editor Section
function reblock_excelsior_bootstrap_editor_section() {
    echo '';
}

// Excelsior Bootstrap Editor Checkbox field
function reblock_start_with_excelsior_bootstrap() {
    $option = get_option( 'reblock_start_with_excelsior_bootstrap', false );
    ?>
    <input type="checkbox" name="reblock_start_with_excelsior_bootstrap" value="1" <?php checked( 1, $option, true ); ?> />
    <label for="reblock_start_with_excelsior_bootstrap"><?php esc_html_e( 'Start new ReBlock with Excelsior Bootstrap', 'reblock' ); ?></label>
    <?php
}

function reblock_styles_scripts_section() {
    echo '<p>' . esc_html__( 'Specify which registered styles and JavaScript files should be loaded for a single ReBlock post by entering their handles on separate lines. Use * (wildcard) to allow all. To remove all, leave them empty.', 'reblock' ) . '</p>';
}

function reblock_show_wp_admin_bar() {
    $option = get_option( 'reblock_show_wp_admin_bar', true );
    ?>
    <input type="checkbox" name="reblock_show_wp_admin_bar" value="1" <?php checked( 1, $option, true ); ?> />
    <label for="reblock_show_wp_admin_bar"><?php esc_html_e( 'Show Admin Bar', 'reblock' ); ?></label>
    <?php
}

function reblock_sanitize_styles_scripts( $input ) {
    $lines = preg_split( '/[\r\n]+/', $input );
    $lines = array_filter( array_map( 'trim', $lines ) );
    if ( in_array( '*', $lines ) ) {
        return '*';
    }
    if ( empty( $lines ) ) {
        return '';
    }
    return implode( ',', $lines );
}

function reblock_allowed_styles() {
    $saved_styles = get_option( 'reblock_allowed_styles', '*' );
    $selected_styles = str_replace( ',', "\n", $saved_styles );
    ?>
    <textarea name="reblock_allowed_styles" rows="5" cols="50"><?php echo esc_textarea( $selected_styles ); ?></textarea>
    <?php
}

function reblock_allowed_scripts() {
    $saved_scripts = get_option( 'reblock_allowed_scripts', '*' );
    $selected_scripts = str_replace( ',', "\n", $saved_scripts );
    ?>
    <textarea name="reblock_allowed_scripts" rows="5" cols="50"><?php echo esc_textarea( $selected_scripts ); ?></textarea>
    <?php
}

?>