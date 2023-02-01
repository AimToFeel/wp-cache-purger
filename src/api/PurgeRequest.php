<?php

namespace WpCachePurger\src\api;

class PurgeRequest extends BaseRequest
{
    /**
     * Purge url post request.
     *
     * @param string $url
     *
     * @return object
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function purge(
        $url,
        $authenticationToken
    ) {
        return $this->doPost(
            'purge',
            [
                'url' => $url,
                'authenticationToken' => $authenticationToken,
            ]
        );
    }

    /**
     * Purge all post request.
     *
     * @param string $url
     *
     * @return object
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function purgeAll(
        $authenticationToken
    ) {
        return $this->doPost(
            'purge-all',
            [
                'authenticationToken' => $authenticationToken,
            ]
        );
    }
}
