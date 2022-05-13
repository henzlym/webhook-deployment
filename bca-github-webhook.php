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

// Include Settings
require_once BCA_WEBHOOKS_DIR . 'includes/settings.php';

function bca_git_webhook_init()
{
    register_rest_route('bca/webhooks/v1', '/pull/', array(
        'methods' => 'POST',
        'callback' => 'bca_git_webhook_pull',
        'permission_callback' => '__return_true'
    ));
}
add_action('rest_api_init', 'bca_git_webhook_init');

function bca_git_webhook_pull( WP_REST_Request $request )
{
    $results = array(
        'error' => false
    );
    
    if (isset($_POST['payload']) && $_POST['payload']) {
        // Only respond to POST requests from Github
        $option = get_option( BCA_WEBHOOKS_OPTION_NAME );
        // $secret = 'RDGIYHDPW9ICNL959XCIIP7L3M4T9LNL';
        $token_secret = ( isset( $option['token_secret'] ) && $option['token_secret'] ) ? $option['token_secret'] : false;
        if (!$token_secret) {
            $results['message'] = 'Missing Api Token Secret';
            return $results;
        }
        $LOCAL_ROOT         = WP_CONTENT_DIR . "/themes";
        $LOCAL_REPO_NAME    = "blkcanvas-theme";
        $LOCAL_REPO         = "{$LOCAL_ROOT}/{$LOCAL_REPO_NAME}";
        $REMOTE_REPO        = "https://github.com/henzlym/blkcanvas-theme.git";
        $BRANCH             = "master";
        /**
         * @link https://gist.github.com/jplitza/88d64ce351d38c2f4198
         */

        $post_data = file_get_contents('php://input');
        $signature = 'sha256=' . hash_hmac('sha256', $post_data, $token_secret);
        $results['raw_payload'] = $post_data;
        $results['payload'] = urldecode($_POST['payload']);
        $payload = str_replace( 'payload=', '', $results['payload'] );

        $payload = json_decode( $payload, true );

        if ($signature === $request->get_header('X-Hub-Signature-256') ) {
            $results['error'] = false;
            $results['is_valid'] = true;
        } else {
            $results['error'] = true;
            $results['is_valid'] = false;
        }
        
        if (file_exists($LOCAL_REPO)) {
            // If there is already a repo, just run a git pull to grab the latest changes
            shell_exec("cd {$LOCAL_REPO} && git pull");

        } else {
            // If the repo does not exist, then clone it into the parent directory
            shell_exec("cd {$LOCAL_ROOT} && git clone {$REMOTE_REPO}");
        }

    }

    return $results;
    
}
