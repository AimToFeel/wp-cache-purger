<?php

namespace WpSocialWall\src\api;

class SitesRequest extends BaseRequest
{
    /**
     * Store site by post request.
     *
     * @param string $address
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
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
