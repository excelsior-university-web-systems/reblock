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

        register_setting( 'reblock_settings_group', 'reblock_allowed_styles', $args = array(
			'sanitize_callback' => __NAMESPACE__.'\\reblock_sanitize_styles'
		) );

        // Add Excelsior Bootstrap Editor Section
        add_settings_section(
            'reblock_styles_scripts', // Section ID
            __( 'Styles and Javascript Files', 'reblock' ), // Title for the section
            __NAMESPACE__.'\\reblock_styles_scripts_section', // Callback function for section description
            'reblock_settings' // Page on which to add this section
        );

        // Add Excelsior Bootstrap Editor Checkbox
        add_settings_field(
            'reblock_allowed_styles', // Field ID
            __( 'Styles', 'reblock' ), // Field title/label
            __NAMESPACE__.'\\reblock_allowed_styles', // Callback function to render the field
            'reblock_settings', // Page on which to add this field
            'reblock_styles_scripts' // Section in which to add the field
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
    <label for="reblock_checkbox_field"><?php esc_html_e( 'Start new ReBlock with Excelsior Bootstrap', 'reblock' ); ?></label>
    <?php
}

function reblock_styles_scripts_section() {
    echo '<p>' . esc_html__( 'Select registered styles and JavaScript files to load on the ReBlock single post.', 'reblock' ) . '</p>';
}

function reblock_sanitize_styles( $input ) {
    // Make sure the input is an array
    if ( is_array( $input ) ) {
        // Sanitize each value and convert the array into a comma-separated string
        $input = array_map( 'sanitize_text_field', $input );
        return implode( ',', $input );
    }
    return '';
}

function reblock_allowed_styles() {
    global $wp_styles;
    $saved_styles = get_option( 'reblock_allowed_styles', '' );
    $selected_styles = $saved_styles ? explode( ',', $saved_styles ) : array();
    echo '<pre>' . print_r( $wp_styles->queue, true ) . '</pre>';
    // foreach ( $wp_styles->queue as $handle => $style ) {
    //     echo esc_html($handle) . '<br />';
    //     echo esc_url($style->src) . '<br /><br />';
    // }
}


?>