<?php

namespace WpSocialWall\src;

use WpSocialWall\src\api\PostsRequest;
use WpSocialWall\src\hooks\ActivationHook;
use WpSocialWall\src\hooks\DeactivationHook;

class WpSocialWall
{
    /**
     * @var string
     */
    private $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * On social wall plugin initialize.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function initialize(): void
    {
        $deactivationHook = new DeactivationHook();
        register_deactivation_hook($this->file, [$deactivationHook, 'run']);

        var_dump((new PostsRequest())->execute('facebook'));
        die;
    }

    /**
     * Define plugin hooks.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function defineHooks(): void
    {
        add_action('init', [$this, 'initialize']);

        $activationHook = new ActivationHook();
        register_activation_hook($this->file, [$activationHook, 'run']);
    }
}
