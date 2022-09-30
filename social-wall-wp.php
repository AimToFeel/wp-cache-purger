<?php
/**
 * Plugin name: Social Wall WP
 * Description: Collects social media activity and stores them in a shared table.
 * Version: 1.5.1
 * Author: AimToFeel
 * Author URI: https://aimtofeel.com
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text domain: social-wall-wp
 */

use WpSocialWall\admin\WpSocialWallAdmin;
use WpSocialWall\src\WpSocialWall;
use WpSocialWall\src\WpSocialWallApi;

if (!function_exists('add_action')) {
    die('Not allowed to call WP Social Wall directly.');
}

define('HOME_DIRETORY_WP_SOCIAL_WALL', plugin_dir_path(__FILE__));
require_once HOME_DIRETORY_WP_SOCIAL_WALL . '/autoloader.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

$wpSocialWall = new WpSocialWall(__FILE__);
$wpSocialWall->defineHooks();

$wpSocialWallAdmin = new WpSocialWallAdmin(__FILE__);
$wpSocialWallAdmin->defineHooks();

// Global API Definition
function get_wp_social_wall_api()
{
    return new WpSocialWallApi();
}
