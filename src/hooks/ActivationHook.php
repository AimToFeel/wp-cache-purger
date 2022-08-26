<?php

namespace WpSocialWall\src\hooks;

class ActivationHook
{
    public function run(): void
    {
        global $wpdb;
        $charsetCollate = $wpdb->get_charset_collate();

        $tableName = "{$wpdb->prefix}social_wall_posts";
        $sql = "CREATE TABLE IF NOT EXISTS $tableName (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            platform varchar(255) NOT NULL,
            post_id varchar(255) NOT NULL,
            post_data JSON NOT NULL,
            post_date datetime NOT NULL,
            fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id)
        ) $charsetCollate;";

        dbDelta($sql);
    }
}
