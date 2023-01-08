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
function __webhook_get_authorization()
{
    $auth = [
        'repo_url' => '',
        'repo_name' => '',
        'token_secret' => '',
        'repo_file_path' => ''
    ];

    $options = get_option(BCA_WEBHOOKS_OPTION_NAME);

    return array_merge($auth, array_filter($options)) ?: false;
}