<?php

namespace WpSocialWall\src\api;

class BaseRequest
{
    private $baseUrl = 'https://api.wp-social-wall.feelgoodtechnology.nl';

    /**
     * Do post request.
     *
     * @param string $location
     * @param object $payload
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    protected function doPost($location, $payload)
    {
        $url = "{$this->baseUrl}/{$location}";
        $apiToken = get_option('wp_social_wall_api_token');

        $result = wp_remote_post(
            $url,
            [
                'method' => 'POST',
                'headers' => [
                    'Content-type' => 'application/json',
                    'nAccept' => 'application/json',
                    'Authorization' => $apiToken,
                ],
                'body' => json_encode($payload),
            ]
        );

        if (is_wp_error($result)) {
            return null;
        }

        return json_decode($result['body']);
    }

    /**
     * Do get request.
     *
     * @param string $location
     * @param object $payload
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    protected function doGet($location)
    {
        $url = "{$this->baseUrl}/{$location}";
        $apiToken = get_option('wp_social_wall_api_token');

        $result = wp_remote_get(
            $url,
            [
                'method' => 'GET',
                'headers' => [
                    'Content-type' => 'application/json',
                    'nAccept' => 'application/json',
                    'Authorization' => $apiToken,
                ],
            ]
        );

        if (is_wp_error($result)) {
            return null;
        }

        return json_decode($result['body']);
    }
}
