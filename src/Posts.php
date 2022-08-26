<?php

namespace WpSocialWall\src;

class Posts
{

    public function get(): array
    {
        global $wpdb;
        $tableName = "{$wpdb->prefix}social_wall_posts";

        $posts = $wpdb->get_results(
            "SELECT * FROM {$tableName} ORDER BY post_date DESC"
        );

        foreach ($posts as $post) {
            $post->post_data = json_decode($post->post_data);
        }

        return $posts;
    }
}
