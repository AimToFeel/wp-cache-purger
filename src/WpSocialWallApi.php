<?php

namespace WpSocialWall\src;

class WpSocialWallApi
{
    /**
     * Get posts.
     *
     * @param array $parameters
     * @param int|null $parameters.limit
     * @param int|null $parameters.page
     * @param array|null $parameters.platforms
     *
     * @return array
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function getPosts($parameters = []): array
    {
        return (new Posts())->get($parameters);
    }
}
