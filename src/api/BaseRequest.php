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

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\nAccept: application/json\r\nAuthorization: {$apiToken}\r\n",
                'method' => 'POST',
                'content' => json_encode($payload),
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            return null;
        }

        return json_decode($result);
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

        $options = [
            'http' => [
                'header' => "Content-type: application/json\r\nAccept: application/json\r\nAuthorization: {$apiToken}\r\n",
                'method' => 'GET',
            ],
        ];

        $context = stream_context_create($options);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) {
            return null;
        }

        return json_decode($result);
    }
}
