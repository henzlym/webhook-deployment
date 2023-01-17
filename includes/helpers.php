<?php
/**
 * Check if a webhook request contains valid authorization information.
 *
 * @since 1.0.0
 *
 * @param array $repo An array containing the repository URL, name, token secret, and file path.
 *
 * @return array|false The merged array of default and provided values if valid, or false if not.
 */
function __webhook_is_authorization($repo)
{
    $auth = [
        'repo_url' => '',
        'repo_name' => '',
        'token_secret' => '',
        'repo_file_path' => ''
    ];

    return array_merge($auth, array_filter($repo)) ?: false;
}
/**
 * Get webhook repositories from the plugin settings.
 *
 * @since 1.0.0
 * 
 * @return array Array of webhook repositories.
 */

function __webhook_get_repos()
{
    $options = get_option(BCA_WEBHOOKS_OPTION_NAME);
    return isset($options['repos']) && !empty($options['repos']) ? $options['repos'] : array();
}
/**
 * Update the repository by webhook ID.
 *
 * @since 1.0.0
 * 
 * @param array $data The webhook data containing the webhook ID.
 * @return array $results Array containing the result of the update process.
 */
function __webhook_update_repo( $webhook_id )
{
    if (!$webhook_id) {
        $results['message'] = 'No webhook_id provided.';
        return $results;
    }

    $repos = __webhook_get_repos();

    if (empty($repos)) {
        $results['message'] = 'No webhooks have been found.';
        return $results;
    }

    if (!array_key_exists($webhook_id, $repos)) {
        $results['repos'] = $repos;
        $results['message'] = "Webhook $webhook_id has not been found.";
        return $results;
    }

    $repo = $repos[$webhook_id];
    if (!__webhook_is_authorization($repo)) {
        $results['message'] = "Webhook $webhook_id is not authorized.";
        return $results;
    }

    $REPO_PATH = $repo['repo_file_path'];
    $REMOTE_REPO = $repo['repo_url'];

    if (is_dir($REPO_PATH)) {
        // If there is already a repo, just run a git pull to grab the latest changes
        shell_exec("cd $REPO_PATH && git reset --hard && git fetch origin && git pull");
        $results['message'] = 'Update existing repo';
    } else {
        wp_mkdir_p($REPO_PATH);
        chmod($REPO_PATH, 0755);
        // If the repo does not exist, then clone it into the parent directory
        shell_exec("cd $REPO_PATH && git clone $REMOTE_REPO .");
        $results['message'] = 'Clone repo';
    }

    return $results;
}