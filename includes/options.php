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
        
    /*** General ***/

    register_setting( 'reblock_settings_group', 'reblock_hash_slug_option' );
    register_setting( 'reblock_settings_group', 'reblock_is_public' );
    register_setting( 'reblock_settings_group', 'reblock_is_searchable' );

    add_settings_section(
        'reblock_general_section',
        __( 'General', 'reblock' ),
        __NAMESPACE__.'\\reblock_general_section',
        'reblock_settings'
    );

    add_settings_field(
        'reblock_is_public',
        __( 'Visibility', 'reblock' ),
        __NAMESPACE__.'\\reblock_is_public',
        'reblock_settings',
        'reblock_general_section'
    );

    add_settings_field(
        'reblock_hash_slug_option',
        __( 'Unlisted', 'reblock' ),
        __NAMESPACE__.'\\reblock_hash_slug_option',
        'reblock_settings',
        'reblock_general_section'
    );

    add_settings_field(
        'reblock_is_searchable',
        __( 'Searchable', 'reblock' ),
        __NAMESPACE__.'\\reblock_is_searchable',
        'reblock_settings',
        'reblock_general_section'
    );

    /*** EXCELSIOR BOOTSTRAP EDITOR ***/
    if ( EXCELSIOR_BOOTSTRAP_EDITOR_SUPPORT ) {
        // Register a new setting for "reblock_settings_group".
        register_setting( 'reblock_settings_group', 'reblock_start_with_excelsior_bootstrap' );

        // Add Excelsior Bootstrap Editor Section
        add_settings_section(
            'reblock_excelsior_bootstrap_editor', // Section ID
            __( 'Excelsior Bootstrap Editor', 'reblock' ), // Title for the section
            __NAMESPACE__.'\\reblock_excelsior_bootstrap_editor_section', // Callback function for section description
            'reblock_settings' // Page on which to add this section
        );

    }
    
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
    register_setting( 'reblock_settings_group', 'reblock_allow_global_styles' );
    register_setting( 'reblock_settings_group', 'reblock_allowed_styles_scripts', array(
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
        'reblock_allow_global_styles',
        __( 'Global Styles', 'reblock' ),
        __NAMESPACE__.'\\reblock_allow_global_styles',
        'reblock_settings',
        'reblock_styles_scripts'
    );

    add_settings_field(
        'reblock_allowed_styles_scripts',
        __( 'Handles', 'reblock' ),
        __NAMESPACE__.'\\reblock_allowed_styles_scripts',
        'reblock_settings',
        'reblock_styles_scripts'
    );
    
}

add_action( 'admin_init', __NAMESPACE__.'\\reblock_register_settings' );

/*** General ***/

function reblock_general_section() {
    echo '';
}

function reblock_hash_slug_option() {
    $option = get_option( 'reblock_hash_slug_option', false );
    ?>
    <input type="checkbox" name="reblock_hash_slug_option" value="1" <?php checked( 1, $option, true ); ?> />
    <label for="reblock_hash_slug_option"><?php esc_html_e( 'Hash the last part of the URL (for Public only)', 'reblock' ); ?></label>
    <p class="description"><?php esc_html_e( 'Ensure only users with the exact URLs can access posts.', 'reblock' ); ?></p>
    <?php
}

function reblock_is_public() {
    $option = get_option( 'reblock_is_public', true );
    ?>
    <input type="checkbox" name="reblock_is_public" value="1" <?php checked( 1, $option, true ); ?> />
    <label for="reblock_is_public"><?php esc_html_e( 'Public', 'reblock' ); ?></label>
    <p class="description"><?php esc_html_e( 'Display ReBlock posts on the front end with accessible URLs.', 'reblock' ); ?></p>
    <?php
}

function reblock_is_searchable() {
    $option = get_option( 'reblock_is_searchable', false );
    ?>
    <input type="checkbox" name="reblock_is_searchable" value="1" <?php checked( 1, $option, true ); ?> />
    <label for="reblock_is_searchable"><?php esc_html_e( 'Include ReBlock posts in the front end search results (for Public only)', 'reblock' ); ?></label>
    <?php
}

/*** Excelsior Bootstrap Editor ***/

function reblock_excelsior_bootstrap_editor_section() {
    echo '';
}

function reblock_start_with_excelsior_bootstrap() {
    $option = get_option( 'reblock_start_with_excelsior_bootstrap', false );
    ?>
    <input type="checkbox" name="reblock_start_with_excelsior_bootstrap" value="1" <?php checked( 1, $option, true ); ?> />
    <label for="reblock_start_with_excelsior_bootstrap"><?php esc_html_e( 'Start new ReBlock with Excelsior Bootstrap', 'reblock' ); ?></label>
    <p class="description"><?php esc_html_e( 'Automatically add the Excelsior Bootstrap block to the editor.', 'reblock' ); ?></p>
    <?php
}

/*** Styles and JavaScript ***/

function reblock_styles_scripts_section() {
    echo '<p>' . __( 'Specify which registered styles and JavaScript files should be loaded for a single ReBlock post by entering their handles (unique identifiers) on separate lines.', 'reblock' ) . '</p>';
    echo '<ul style="list-style: revert; padding: revert;">';
    echo '<li>' . wp_kses_post( __( 'Enter <strong>*</strong> (wildcard) to allow all registered styles and scripts.', 'reblock' ) ) . '</li>';
    echo '<li>' . wp_kses_post( __( 'Enter one or more handles to load only the specified styles and scripts. <em>All others will be excluded.</em>', 'reblock' ) ) . '</li>';
    echo '<li>' . wp_kses_post( __( 'Leave the field <strong>empty to remove all</strong> registered styles and scripts.', 'reblock' ) ) . '</li>';
    echo '</ul>';
    echo '<div style="background:#fff; border-left: 4px solid #0073aa; margin:13px 0; padding: 10px;">' . wp_kses_post( __( '<p style="margin:0;"><strong>Note:</strong> This only applies to styles and scripts that are registered with WordPress using <code>wp_register_style()</code> and <code>wp_register_script()</code>. Any styles or scripts loaded by other means (e.g., hardcoded in the theme or loaded via external sources) are not affected by this setting.</p>', 'reblock' ) ) . '</div>';
}

function reblock_show_wp_admin_bar() {
    $option = get_option( 'reblock_show_wp_admin_bar', true );
    ?>
    <input type="checkbox" name="reblock_show_wp_admin_bar" value="1" <?php checked( 1, $option, true ); ?> />
    <label for="reblock_show_wp_admin_bar"><?php esc_html_e( 'Show Admin Bar', 'reblock' ); ?></label>
    <?php
}

function reblock_allow_global_styles() {
    $option = get_option( 'reblock_allow_global_styles', true );
    ?>
    <input type="checkbox" name="reblock_allow_global_styles" value="1" <?php checked( 1, $option, true ); ?> />
    <label for="reblock_allow_global_styles"><?php esc_html_e( 'Allow WordPress global styles', 'reblock' ); ?></label>
    <p class="description"><?php esc_html_e( 'Ensure block styling in the editor matches the front-end.', 'reblock' ); ?></p>
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

function reblock_allowed_styles_scripts() {
    $saved_styles = get_option( 'reblock_allowed_styles_scripts', '*' );
    $selected_styles = str_replace( ',', "\n", $saved_styles );
    ?>
    <textarea name="reblock_allowed_styles_scripts" rows="5" cols="50"><?php echo esc_textarea( $selected_styles ); ?></textarea>
    <?php
}

/** others */

function reblock_option_updated( $option, $old_value, $new_value ) {
    if ( 'reblock_hash_slug_option' === $option ) {
        flush_rewrite_rules();
    }
}

add_action( 'updated_option', __NAMESPACE__.'\\reblock_option_updated', 10, 3 );

?>