<?php

namespace WpSocialWall\src\api;

class TokensRequest extends BaseRequest
{
    /**
     * Store token post request.
     *
     * @param string $platform
     * @param string $accessToken
     * @param string $verifyToken
     *
     * @return object
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function store(
        $platform,
        $accessToken,
        $verifyToken = null
    ) {
        return $this->doPost(
            'tokens',
            [
                'platform' => $platform,
                'accessToken' => $accessToken,
                'verifyToken' => $verifyToken,
            ]
        );
    }

    /**
     * Get platforms connected to authentication.
     *
     * @return object
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function get()
    {
        return $this->doGet('tokens');
    }
}
