<?php

spl_autoload_register('wpSocialWallAutoload');

function wpSocialWallAutoload($className)
{
    if (strpos($className, 'WpSocialWall\\') === false) {
        return;
    }

    require_once HOME_DIRETORY_WP_SOCIAL_WALL . '/' . str_replace('\\', '/', substr($className, 13)) . '.php';
}
