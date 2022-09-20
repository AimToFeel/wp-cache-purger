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

        if (isset($post->media)) {
            $result = $this->fetchMedia($post->media, $platform, $post->id);

            if ($result !== false) {
                $post->media = $result['path'];
            }
        }

        $wpdb->get_var(
            $wpdb->prepare(
                "INSERT INTO {$tableName} (platform, post_id, post_data, post_date, fetched_at) VALUES (%s, %s, %s, %s, %s)",
                $platform,
                $post->id,
                json_encode($post),
                explode('+', $post->createdAt)[0],
                date('Y-m-d H:i:s')
            )
        );
    }

    /**
     * Fetch media.
     *
     * @param string $imageUrl
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function fetchMedia(string $mediaUrl, string $platform, string $postId)
    {
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $tempFile = download_url($mediaUrl);

        if (is_wp_error($tempFile)) {
            return false;
        }

        $file = [
            'name' => "{$platform}-{$postId}." . explode('.', explode('?', basename($mediaUrl))[0])[1],
            'type' => mime_content_type($tempFile),
            'tmp_name' => $tempFile,
            'size' => filesize($tempFile),
        ];

        $sideLoad = wp_handle_sideload(
            $file,
            ['test_form' => false]
        );

        if (!empty($sideLoad['error'])) {
            return false;
        }

        $attachmentId = wp_insert_attachment(
            [
                'guid' => $sideLoad['url'],
                'post_mime_type' => $sideLoad['type'],
                'post_title' => basename($sideLoad['file']),
                'post_content' => '',
                'post_status' => 'inherit',
            ],
            $sideLoad['file']
        );

        if (is_wp_error($attachmentId) || !$attachmentId) {
            return false;
        }

        require_once ABSPATH . 'wp-admin/includes/image.php';

        wp_update_attachment_metadata(
            $attachmentId,
            wp_generate_attachment_metadata($attachmentId, $sideLoad['file'])
        );

        return parse_url(wp_get_attachment_url($attachmentId));
    }
}
