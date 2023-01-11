<?php
// function __webhook_get_authorization_dep()
// {
//     $is_auth = true;
//     $auth = array(
//         'repo_url' => false,
//         'repo_name' => false,
//         'token_secret' => false,
//         'repo_file_path' => false
//     );
//     $option = get_option(BCA_WEBHOOKS_OPTION_NAME);
//     $auth['repo_url'] = (isset($option['repo_url']) && !empty($option['repo_url'])) ? $option['repo_url'] : $auth['repo_url'];
//     $auth['repo_name'] = (isset($option['repo_name']) && !empty($option['repo_name'])) ? $option['repo_name'] : $auth['repo_name'];
//     $auth['token_secret'] = (isset($option['token_secret']) && !empty($option['token_secret'])) ? $option['token_secret'] : $auth['token_secret'];
//     $auth['repo_file_path'] = (isset($option['repo_file_path']) && !empty($option['repo_file_path'])) ? $option['repo_file_path'] : $auth['repo_file_path'];
//     foreach ($auth as $key => $value) {
//         if ($value == false) {
//             $is_auth = false;
//         }
//     }
//     if (!$is_auth) {
//         return false;
//     }
//     return $auth;
// }
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
 * @param array $data The webhook data containing the webhook ID.
 * @return array $results Array containing the result of the update process.
 */
function update_repo( $webhook_id )
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
        shell_exec("cd $REPO_PATH && git reset --hard && git fetch origin && git pull origin master");
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
