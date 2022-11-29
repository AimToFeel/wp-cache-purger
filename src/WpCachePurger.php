<?php

namespace WpCachePurger\src;

use WpCachePurger\src\api\PurgeRequest;
use WpCachePurger\src\hooks\ActivationHook;
use WpCachePurger\src\hooks\DeactivationHook;

class WpCachePurger
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
    }

    /**
     * Purge post cache.
     *
     * @param int $postId
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function purgePostCache($postId): void
    {
        $url = get_permalink($postId);
        $token = get_option('wp_cache_purger_authentication_token');

        (new PurgeRequest())->purge($url, $token);
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
        add_action('save_post', [$this, 'purgePostCache']);

        $activationHook = new ActivationHook();
        register_activation_hook($this->file, [$activationHook, 'run']);
    }
}
