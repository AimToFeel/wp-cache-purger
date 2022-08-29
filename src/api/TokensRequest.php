<?php

namespace WpSocialWall\src\api;

class TokensRequest extends BaseRequest
{
    public function store(
        $platform,
        $accessToken,
        $verifyToken = null,
        $pageId = null,
        $userId = null
    ) {
        return $this->doPost(
            'tokens',
            [
                'platform' => $platform,
                'pageId' => $pageId,
                'accessToken' => $accessToken,
                'verifyToken' => $verifyToken,
                'userId' => $userId,
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
