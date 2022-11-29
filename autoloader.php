<?php

spl_autoload_register('wpCachePurgerAutoloader');

function wpCachePurgerAutoloader($className)
{
    if (strpos($className, 'WpCachePurger\\') === false) {
        return;
    }

    require_once HOME_DIRECTORY_WP_CACHE_PURGER . '/' . str_replace('\\', '/', substr($className, 13)) . '.php';
}
