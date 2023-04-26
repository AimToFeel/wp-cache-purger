<?php
/**
 * Plugin name: Cache purger
 * Description: Purges server side cache by calling a purger api
 * Version: 1.7.1
 * Author: AimToFeel
 * Author URI: https://aimtofeel.com
 * License: GPLv2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text domain: social-wall-wp
 */

use WpCachePurger\admin\WpCachePurgerAdmin;
use WpCachePurger\src\WpCachePurger;

if (!function_exists('add_action')) {
    die('Not allowed to call Cache Purger directly.');
}

define('HOME_DIRECTORY_WP_CACHE_PURGER', plugin_dir_path(__FILE__));
require_once HOME_DIRECTORY_WP_CACHE_PURGER . '/autoloader.php';
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

$wpCachePurger = new WpCachePurger(__FILE__);
$wpCachePurger->defineHooks();

$wpCachePurgerAdmin = new WpCachePurgerAdmin(__FILE__);
$wpCachePurgerAdmin->defineHooks();
