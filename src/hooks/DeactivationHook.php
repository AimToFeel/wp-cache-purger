<?php

namespace WpSocialWall\src\hooks;

class DeactivationHook
{
    public function run(): void
    {
        global $wpdb;

        $tableName = "{$wpdb->prefix}social_wall_posts";
        $sql = "DROP TABLE $tableName;";
        dbDelta($sql);
    }
}
