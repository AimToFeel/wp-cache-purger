<?php

namespace WpCachePurger\src\api;

class BaseRequest
{
    private $baseUrl = '/cache-purger';

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
        $url = str_replace('/wp', '', get_site_url()) . "{$this->baseUrl}/{$location}";

        $result = wp_remote_post(
            $url,
            [
                'method' => 'POST',
                'headers' => [
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json',
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

        $result = wp_remote_get(
            $url,
            [
                'method' => 'GET',
                'headers' => [
                    'Content-type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ]
        );

        if (is_wp_error($result)) {
            return null;
        }

        return json_decode($result['body']);
    }
}
