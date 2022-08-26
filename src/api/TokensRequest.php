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
}
