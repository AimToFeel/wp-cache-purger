<?php

namespace WpSocialWall\src\api;

class RegisterRequest extends BaseRequest
{
    public function execute(
        $platform,
        $accessToken,
        $pageId,
        $userId
    ) {
        return $this->doPost(
            'register',
            [
                'address' => get_site_url(),
                'platform' => $platform,
                'pageId' => $pageId,
                'accessToken' => $accessToken,
                'userId' => $userId,
            ]
        );
    }
}
