<?php

namespace WpSocialWall\src\api;

class PostsRequest extends BaseRequest
{
    /**
     * Get posts by platform type.
     *
     * @param string $platform
     *
     * @return object
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function execute(string $platform)
    {
        return $this->doGet("posts?platform={$platform}");
    }
}
