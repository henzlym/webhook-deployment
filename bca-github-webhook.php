<?php

/**
 *
 * @link https://blk-canvas.com
 * @since 1.0.0
 * @package Blk Canvas
 * @link https://gist.github.com/marcelosomers/8305065
 *
 * @wordpress-plugin
 * Plugin Name: Blk Canvas - Github Webhook
 * Plugin URI: https://blk-canvas.com/
 * Description: Add github webhooks to your site
 * Version: 1.0.0
 * Author: Blk Canvas
 * Author URI: https://blk-canvas.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: bca
 * Domain Path: /languages/
 */

 // Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin path/uri constant
define('BCA_WEBHOOKS_DIR', plugin_dir_path( __FILE__ ) );
define('BCA_WEBHOOKS_URL', plugin_dir_url( __FILE__ ) );
define('BCA_WEBHOOKS_CACHE', '1.0.0' );
define('BCA_WEBHOOKS_OPTION_GROUP', 'bca_webhook' );
define('BCA_WEBHOOKS_OPTION_NAME', 'bca_webhook_options' );
define('BCA_WEBHOOKS_NAMESPACE', 'bca/webhooks/v1' );
define('BCA_WEBHOOKS_PAYLOAD_ROUT', '/pull/' );
// Include Settings
require_once BCA_WEBHOOKS_DIR . 'includes/helpers.php';
require_once BCA_WEBHOOKS_DIR . 'includes/settings.php';

function bca_git_webhook_init()
{
    register_rest_route( BCA_WEBHOOKS_NAMESPACE, '/pull/', array(
        'methods' => 'POST',
        'callback' => 'bca_git_webhook_pull',
        'permission_callback' => '__return_true'
    ));
    register_rest_route( BCA_WEBHOOKS_NAMESPACE, '/pull/(?P<webhook_id>[\da-zA-Z]+)', array(
        'methods' => 'POST',
        'callback' => 'bca_git_webhook_pull_id',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'bca_git_webhook_init');

function bca_git_webhook_pull_id( WP_REST_Request $request )
{
    if (!isset( $request['webhook_id'] )) {
        $results['message'] = 'No webhook_id provided.';
        return $results;
    }
    return update_repo( $request['webhook_id'] );
}
function bca_git_webhook_pull( WP_REST_Request $request )
{
    $results = array(
        'error' => false
    );

    if ( ( isset($_POST['payload']) && $_POST['payload'] ) ) {
        // Only respond to POST requests from Github
        $repos = __webhook_get_repos();
        
        if (empty($repos)) {
            $results['message'] = 'No webhooks have been found.';
            return $results;
        }

        foreach ($repos as $key => $repo) {
            
            $results[$key] = update_repo( $repo['token_secret'] );
            
        }
        

    }

    return $results;
    
}
