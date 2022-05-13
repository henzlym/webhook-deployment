<?php
namespace Bca\Webhooks;

class Settings
{
    public function __construct()
    {
        $this->option_group = BCA_WEBHOOKS_OPTION_GROUP;
        $this->option_name = BCA_WEBHOOKS_OPTION_NAME;

        /**
         * Register our bca_settings_init to the admin_init action hook.
         */
        add_action( 'admin_init', array( $this, 'register_settings' ) );

         
        /**
         * Register our bca_webhook_options_page to the admin_menu action hook.
         */
        add_action( 'admin_menu',  array( $this, 'add_admin_menu' ));
    }

    /**
     * Top level menu callback function
     */
    public function render_options_page() {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
    
        // add error/update messages
    
        // check if the user have submitted the settings
        // WordPress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // add settings saved message with the class of "updated"
            add_settings_error( 'bca_messages', 'bca_message', __( 'Settings Saved', 'bca' ), 'updated' );
        }
    
        // show error/update messages
        settings_errors( 'bca_messages' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting "bca"
                settings_fields( $this->option_group );
                // output setting sections and their fields
                // (sections are registered for "bca_webhook", each field is registered to a specific section)
                do_settings_sections( $this->option_group );
                // output save settings button
                submit_button( 'Save Settings' );
                ?>
            </form>
        </div>
        <?php
    }
    /**
     * Add the top level menu page.
     */
    public function add_admin_menu() {
        add_submenu_page(
            'options-general.php',
            'Settings',
            'Webhooks',
            'manage_options',
            $this->option_group,
            array( $this, 'render_options_page' )
        );
    }
    public function field_input($args)
    {

        $option = get_option( $this->option_name );
        $value = (isset($option[$args['label_for']]) && !empty($option[$args['label_for']])) ? $option[$args['label_for']] : "";
        $name = $this->option_name . '[' . $args['label_for'] . ']';
        $type = ( isset( $args['type'] ) ) ? $args['type'] : 'text';
        $class = ( isset( $args['class'] ) ) ? $args['class'] : 'regular-text';
    ?>
        <input 
            type="<?php echo esc_attr( $type ); ?>" 
            id="<?php echo esc_attr($args['label_for']); ?>" 
            name="<?php echo esc_attr( $name ); ?>" 
            value="<?php echo esc_attr( $value ); ?>"
            class="<?php echo esc_attr( $class ); ?>"
        >
        <p class="description">
            <?php esc_html_e(esc_attr($args['description'])); ?>
        </p>
    <?php
    }
    /**
     * Developers section callback function.
     *
     * @param array $args  The settings array, defining title, id, callback.
     */
    public function section_callback( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Follow the white rabbit.', 'bca' ); ?></p>
        <?php
    }

    public function register_settings()
    {
        // Register a new setting for "bca" page.
        register_setting( $this->option_group, $this->option_name );
    
        // Register a new section in the "bca" page.
        add_settings_section(
            'general',
            __( 'General', 'bca' ),
            array( $this, 'section_callback'),
            $this->option_group
        );
    
        // Register a new field in the "bca_section_developers" section, inside the "bca" page.
        add_settings_field(
            'token_name', // As of WP 4.6 this value is used only internally.
            // Use $args' label_for to populate the id inside the callback.
            __( 'API Token Name', 'bca' ),
            array( $this, 'field_input'),
            $this->option_group,
            'general',
            array(
                'label_for' => 'token_name',
                'class' => 'regular-text',
                'description' => '',
            )
        );
        add_settings_field(
            'token_secret', // As of WP 4.6 this value is used only internally.
            // Use $args' label_for to populate the id inside the callback.
            __( 'API Token Secret', 'bca' ),
            array( $this, 'field_input'),
            $this->option_group,
            'general',
            array(
                'label_for' => 'token_secret',
                'class' => 'regular-text',
                'description' => 'Please enter a unique password to be used for the token secret',
            )
        );
    }
}

new \Bca\Webhooks\Settings();