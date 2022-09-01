<?php

namespace WpSocialWall\src;

class Posts
{
    /**
     * Get posts from database.
     * Validates input for save query execution.
     *
     * @return array
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function get($parameters): array
    {
        global $wpdb;
        $tableName = "{$wpdb->prefix}social_wall_posts";

        $query = "SELECT * FROM {$tableName}";

        if (isset($parameters['platforms'])) {
            $platforms = implode("','", $parameters['platforms']);

            preg_match("/(\'(" . strtolower(implode('|', WP_SOCIAL_WALL_PLATFORMS)) . ")\'(,)?)*/", "'{$platforms}'", $results);

            if (!isset($results[0]) || $results[0] !== "'{$platforms}'") {
                throw new WpSocialWallException("Platform string ill-formed: `'{$platforms}'`.");
            }

            $query .= " WHERE platform IN ({$results[0]})";
        }

        $query .= ' ORDER BY post_date DESC';

        $limit = filter_var($parameters['limit'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $page = filter_var($parameters['page'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($limit !== null) {
            $query .= " LIMIT {$limit}";

            if ($page !== null) {
                $offset = $limit * $page;

                $query .= ", {$offset}";
            }
        }

        $posts = $wpdb->get_results($query);

        foreach ($posts as $post) {
            $post->post_data = json_decode($post->post_data);
        }

        return $posts;
    }
}
