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

    register_setting( 'reblock_settings_group', 'reblock_hash_slug_option', array(
        'type' => 'boolean',
        'sanitize_callback' => '\\rest_sanitize_boolean',
        'default' => false,
    ) );

    register_setting( 'reblock_settings_group', 'reblock_is_public', array(
        'type' => 'boolean',
        'sanitize_callback' => '\\rest_sanitize_boolean',
        'default' => true,
    ) );

    register_setting( 'reblock_settings_group', 'reblock_is_searchable', array(
        'type' => 'boolean',
        'sanitize_callback' => '\\rest_sanitize_boolean',
        'default' => false,
    ) );

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
        register_setting( 'reblock_settings_group', 'reblock_start_with_excelsior_bootstrap', array(
            'type' => 'boolean',
            'sanitize_callback' => '\\rest_sanitize_boolean',
            'default' => false,
        ) );

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

    register_setting( 'reblock_settings_group', 'reblock_show_wp_admin_bar', array(
        'type' => 'boolean',
        'sanitize_callback' => '\\rest_sanitize_boolean',
        'default' => false,
    ) );

    register_setting( 'reblock_settings_group', 'reblock_allow_global_styles', array(
        'type' => 'boolean',
        'sanitize_callback' => '\\rest_sanitize_boolean',
        'default' => true,
    ) );

    register_setting( 'reblock_settings_group', 'reblock_allowed_styles_scripts', array(
        'type' => 'string',
        'sanitize_callback' => __NAMESPACE__.'\\reblock_sanitize_styles_scripts',
        'default' => '*'
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

    /*** Permission ***/

    register_setting( 'reblock_settings_group', 'reblock_allowed_roles', array(
        'type' => 'string',
        'sanitize_callback' => __NAMESPACE__.'\\reblock_sanitize_roles',
        'default' => ''
    ) );

    add_settings_section(
        'reblock_permission_section',
        __( 'Permission', 'reblock' ),
        __NAMESPACE__.'\\reblock_permission_section',
        'reblock_settings'
    );

    add_settings_field(
        'reblock_allowed_roles',
        __( 'Roles', 'reblock' ),
        __NAMESPACE__.'\\reblock_allowed_roles',
        'reblock_settings',
        'reblock_permission_section'
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
    ?>
    <p><?php esc_html_e( 'Specify which registered styles and JavaScript files should be loaded for a single ReBlock post by entering their handles (unique identifiers) on separate lines.', 'reblock' ); ?></p>

    <ul style="list-style: revert; padding: revert;">
        <li><?php
        // translators: %s is the HTML <strong>*</strong> for the wildcard symbol
        printf( wp_kses_post( __( 'Enter %s (wildcard) to allow all registered styles and scripts.', 'reblock' ) ),
            '<strong>*</strong>'
        ); ?></li>

        <li><?php
        // translators: %s is the texts in <em> HTML tag.
        printf( wp_kses_post( __( 'Enter one or more handles to load only the specified styles and scripts. %s', 'reblock' ) ),
            '<em>' . esc_html__( 'All others will be excluded.', 'reblock' ) . '</em>'
        ); ?></li>

        <li><?php 
        // translators: %s is the word empty in <strong> HTML tag.
        printf( wp_kses_post( __( 'Leave the field %s to remove all registered styles and scripts.', 'reblock' ) ),
            '<strong>' . esc_html__( 'empty', 'reblock' ) . '</strong>'
        ); ?></li>
    </ul>

    <div style="background:#fff; border-left: 4px solid #0073aa; margin:13px 0; padding: 10px;">
        <p style="margin:0;"><?php
            // translators: %1$s is "Note:" in <strong>, %2$s is <code>wp_register_style()</code>, %3$s is <code>wp_register_script()</code>
            printf( wp_kses_post( __( '%1$s This only applies to styles and scripts that are registered with WordPress using %2$s and %3$s. Any styles or scripts loaded by other means (e.g., hardcoded in the theme or loaded via external sources) are not affected by this setting.', 'reblock' ) ),
                '<strong>' . esc_html__( 'Note:', 'reblock' ) . '</strong>',
                '<code>wp_register_style()</code>',
                '<code>wp_register_script()</code>'
            );
        ?></p>
    </div>
    <?php
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

/** Permission **/

function reblock_permission_section() {
    echo '';
}

function reblock_sanitize_roles( $input ) {
    // Make sure the input is an array
    if ( is_array( $input ) ) {
        // Sanitize each value and convert the array into a comma-separated string
        $input = array_map( 'sanitize_text_field', $input );
        return implode( ',', $input );
    }
    return '';
}

function reblock_allowed_roles() {
    
    global $wp_roles;

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new \WP_Roles();
    }

    $saved_roles = get_option( 'reblock_allowed_roles', 'administrator' );
    $selected_roles = $saved_roles ? explode( ',', $saved_roles ) : array('administrator');

    foreach ( $wp_roles->get_names() as $role_key => $role_name ) {
        ?>
        <label>
            <input type="checkbox" name="reblock_allowed_roles[]" value="<?php echo esc_attr( $role_key ); ?>" <?php checked( ( $role_key === 'administrator' || in_array( $role_key, $selected_roles ) ), true ); ?> <?php disabled( ( $role_key === 'administrator' ), true ); ?> />
            <?php echo esc_html( $role_name ); ?>
        </label><br>
        <?php
    }
    
}

/** others */

function reblock_update_role_capabilities( $old_value = '', $new_value = '' ) {
    $post_type_object = get_post_type_object( REBLOCK_POST_TYPE_NAME );
    if ( ! $post_type_object ) return;

    $capabilities = $post_type_object->cap;

    $required_roles = array( 'administrator' );

    $old_roles = is_string( $old_value ) ? array_map( 'trim', explode( ',', $old_value ) ) : array();
    $new_roles = is_string( $new_value ) ? array_map( 'trim', explode( ',', $new_value ) ) : array();

    $removed_roles = array_diff( $old_roles, $new_roles );
    $allowed_roles = array_filter( array_unique( array_merge( $required_roles, $new_roles ) ) );

    // Remove caps from roles that are no longer allowed (and aren't admin)
    foreach ( $removed_roles as $role_name ) {
        if ( $role_name === 'administrator' ) continue;
        $role = get_role( $role_name );
        if ( $role ) {
            foreach ( $capabilities as $cap ) {
                $role->remove_cap( $cap );
            }
        }
    }

    // Add caps to allowed roles (including admin)
    foreach ( $allowed_roles as $role_name ) {
        $role = get_role( $role_name );
        if ( $role ) {
            foreach ( $capabilities as $cap ) {
                $role->add_cap( $cap );
            }
        }
    }
}

function reblock_option_updated( $option, $old_value, $new_value ) {
    if ( 'reblock_hash_slug_option' === $option && $old_value !== $new_value ) {
        flush_rewrite_rules();
    }

    if ( 'reblock_allowed_roles' === $option ) {
        reblock_update_role_capabilities( $old_value, $new_value );
    }
}

add_action( 'updated_option', __NAMESPACE__.'\\reblock_option_updated', 10, 3 );
?>