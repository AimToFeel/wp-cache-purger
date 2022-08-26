<?php

namespace WpSocialWall\src\api;

class SitesRequest extends BaseRequest
{
    public function store(
        $address
    ) {
        return $this->doPost(
            'sites',
            [
                'address' => $address,
            ]
        );
    }
}
