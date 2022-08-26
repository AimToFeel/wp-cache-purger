<?php

namespace WpSocialWall\src;

use WpSocialWall\src\api\PostsRequest;

class Fetcher
{
    /**
     * Perform fetch action for given platform.
     *
     * @param string $platform
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function fetchPlatform(string $platform)
    {
        $result = (new PostsRequest())->execute($platform);

        foreach ($result->posts as $post) {
            $this->storePost($platform, $post);
        }
    }

    /**
     * Store post content to database.
     *
     * @param string $platform
     * @param object $post
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    private function storePost(string $platform, $post): void
    {
        global $wpdb;
        $tableName = "{$wpdb->prefix}social_wall_posts";

        $results = $wpdb->get_var($wpdb->prepare("SELECT COUNT(id) FROM {$tableName} WHERE post_id = %s AND platform = %s;", $post->id, $platform));

        if ($results > 0) {
            return;
        }

        $wpdb->get_var(
            $wpdb->prepare(
                "INSERT INTO {$tableName} (platform, post_id, post_data, post_date, fetched_at) VALUES (%s, %s, %s, %s, %s)",
                $platform,
                $post->id,
                json_encode($post),
                date($post->createdAt),
                date('Y-m-d H:i:s')
            )
        );
    }
}
