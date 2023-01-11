<?php
namespace Bca\Webhooks;

class Settings
{
    public $option_group;
    public $option_name;
    
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
        $default = ( isset( $args['default'] ) ) ? $args['default'] : "";
        $value = (isset($option[$args['label_for']]) && !empty($option[$args['label_for']])) ? $option[$args['label_for']] : $default;
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
            <?php echo $args['description']; ?>
        </p>
    <?php
    }
    function repeater_field($args) {
        ?>
        <div id="repeater-container">
            <?php
            $option = get_option( $this->option_name );
            $default = ( isset( $args['default'] ) ) ? $args['default'] : "";
            $repeater_values = (isset($option[$args['label_for']]) && !empty($option[$args['label_for']])) ? $option[$args['label_for']] : $default;
            $name = $this->option_name . '[' . $args['label_for'] . ']';
            $type = ( isset( $args['type'] ) ) ? $args['type'] : 'text';
            $class = ( isset( $args['class'] ) ) ? $args['class'] : 'regular-text';
            $class = ( isset( $args['class'] ) ) ? $args['class'] : 'regular-text';
            $nested_fields = ( isset( $args['fields'] ) ) ? $args['fields'] : array();
            $repeater_id = uniqid();

            if ( ! empty( $repeater_values ) ) {
                
                foreach ( $repeater_values as $repeater_name => $repeater_fields ) {
                    ?>
                    <div class="repeater-item" id="webhook-<?php echo $repeater_name;?>">
                    <?php
                    foreach ( $repeater_fields as $field_name => $field_value ) {
                        $field_label = ( isset( $nested_fields[$field_name] ) ) ?  $nested_fields[$field_name]['label'] : '';
                        $field_description = ( isset( $nested_fields[$field_name] ) && isset( $nested_fields[$field_name]['description'] ) ) ?  $nested_fields[$field_name]['description'] : '';
                        ?>
                        <div class="repeater-nested-item">
                        <label for="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $repeater_name ); ?>][<?php echo esc_attr( $field_name ); ?>]">
                            <?php echo esc_attr( $field_label ); ?>
                        </label>
                        <input 
                            type="<?php echo esc_attr( $type ); ?>" 
                            id="<?php echo esc_attr($args['label_for']); ?>" 
                            name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $repeater_name ); ?>][<?php echo esc_attr( $field_name ); ?>]" 
                            value="<?php echo esc_attr( $field_value ); ?>"
                            class="<?php echo esc_attr( $class ); ?>"
                        >
                        <p class="description">
                            <?php echo $field_description; ?>
                        </p>
                        </div>
                        <?php
                    }
                    ?>
                    <button type="button" class="pull-button" data-webhook="<?php echo $repeater_name;?>">Pull</button>
                    <button type="button" class="remove-button">Remove</button>
                    </div>
                    <?php
                }
            } else{
                if (!empty( $nested_fields )) {
                    ?>
                    <div class="repeater-item">
                    <?php
                    foreach ($nested_fields as $key => $field) {
                        $field_value = ( isset( $field['default'] ) ) ? $field['default'] : ""; 
                        $field_description = ( isset( $field['description'] ) && isset( $field['description'] ) ) ?  $field['description'] : '';

                        ?>
                        <div class="repeater-nested-item">
                        <label for="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $repeater_id ); ?>][<?php echo esc_attr( $field['name'] ); ?>]">
                            <?php echo esc_attr( $field['label'] ); ?>
                        </label>
                        <input 
                            type="<?php echo esc_attr( $type ); ?>" 
                            id="<?php echo esc_attr($args['label_for']); ?>" 
                            name="<?php echo esc_attr( $name ); ?>[<?php echo esc_attr( $repeater_id ); ?>][<?php echo esc_attr( $field['name'] ); ?>]" 
                            value="<?php echo esc_attr( $field_value ); ?>"
                            class="<?php echo esc_attr( $class ); ?>"
                        >
                        <p class="description">
                            <?php echo $field_description; ?>
                        </p>
                        </div>
                        <?php
                    }
                    ?>
                    </div>
                    <?php
                }
            }

            ?>
            <button type="button" id="add-button">Add</button>
        </div>
        <script>
            var addButton = document.getElementById("add-button");
            var pullButtons = document.querySelectorAll(".pull-button");
            var repeaterContainer = document.getElementById("repeater-container");
            var nestedFields = <?php echo json_encode( $nested_fields ); ?>;
            function uniqid() {
                return (new Date().getTime() + Math.random().toString(36).substr(2, 9));
            }
            addButton.addEventListener("click", function () {
                var newItem = document.createElement("div");
                var fieldId = uniqid();
                newItem.classList.add("repeater-item");
                for (const [key, field] of Object.entries(nestedFields)) {
                    newItem.innerHTML += `
                    <div class="repeater-nested-item">
                        <label for="<?php echo esc_attr( $name ); ?>[${fieldId}][${field.name}]">
                            ${field.label}
                        </label>
                        <input 
                            type="<?php echo esc_attr( $type ); ?>" 
                            id="<?php echo esc_attr($args['label_for']); ?>" 
                            name="<?php echo esc_attr( $name ); ?>[${fieldId}][${field.name}]" 
                            value="${field.default}"
                            class="<?php echo esc_attr( $class ); ?>"
                        >
                        <p class="description">
                            ${ field.description || "" }
                        </p>
                    </div>
                    `;
                };
                newItem.innerHTML += `
                    <button type="button" class="remove-button">Remove</button>
                `;
                repeaterContainer.appendChild(newItem);
            });
    
            repeaterContainer.addEventListener("click", function (event) {
                if (event.target.classList.contains("remove-button")) {
                    event.target.parentElement.remove();
                }
            });
            pullButtons.forEach( pullButton => {
                var webhook = pullButton.getAttribute('data-webhook');
                pullButton.addEventListener('click', () => {
                    fetch(
                        '/wp-json/bca/webhooks/v1/pull/' + webhook,
                        { 
                            body:JSON.stringify({
                                webhookid:webhook
                            }), 
                            method:"POST" 
                        }
                    )
                    .then( (res) => res.json() )
                    .then( (data) => { console.log(data); })
                })

            });
        </script>
        <?php
    }
    /**
     * Developers section callback function.
     *
     * @param array $args  The settings array, defining title, id, callback.
     */
    public function section_callback( $args ) {
        ?>
        <p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Webhook URL: ' . get_rest_url( null, BCA_WEBHOOKS_NAMESPACE . '/pull/' ), 'bca' ); ?></p>
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
    
        add_settings_field(
            'repos', // As of WP 4.6 this value is used only internally.
            // Use $args' label_for to populate the id inside the callback.
            __( 'Repos', 'bca' ),
            array( $this, 'repeater_field'),
            $this->option_group,
            'general',
            array(
                'label_for' => 'repos',
                'class' => 'repeater-field',
                'description' => '',
                'fields' => array(
                    'repo_url' => array(
                        'name' => 'repo_url',
                        'label' => 'Clone URL',
                        'type' => 'text',
                        'default' => "",
                        'description' => 'Enter the clone URL for the remote repository. All clone URLs must begin with the http:// or https://',
                    ),
                    'repo_name' => array(
                        'name' => 'repo_name',
                        'label' => 'Repository name',
                        'type' => 'text',
                        'default' => "",
                        
                    ),
                    'token_secret' => array(
                        'name' => 'token_secret',
                        'label' => 'Secret Key',
                        'type' => 'text',
                        'default' => bin2hex(random_bytes(16)),
                        'description' => 'Please enter a unique password to be used for the token secret',
                    ),
                    'repo_file_path' => array(
                        'name' => 'repo_file_path',
                        'label' => 'Server Path',
                        'type' => 'text',
                        'default' => "",
                        'description' => " Directory on the server where files will be deployed." 
                            . "<br/>". WP_PLUGIN_DIR . "<br/>" . WP_CONTENT_DIR . "/themes"
                        ,
                    )
                )
            )
        );
    }
}

new \Bca\Webhooks\Settings();