<?php

namespace WpSocialWall\src;

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
        define('WP_SOCIAL_WALL_PLATFORMS', [
            'Facebook', 'Twitter', 'Instagram',
        ]);

        $deactivationHook = new DeactivationHook();
        register_deactivation_hook($this->file, [$deactivationHook, 'run']);

        if (!wp_next_scheduled('wp_social_wall_fetch_posts')) {
            wp_schedule_event(time(), 'every_two_hours', 'wp_social_wall_fetch_posts');
        }
    }

    /**
     * Fetch posts of all enabled services
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function fetchPosts(): void
    {
        foreach (WP_SOCIAL_WALL_PLATFORMS as $platform) {
            $platformLower = strtolower($platform);

            $active = get_option("wp_social_wall_{$platformLower}_active");

            if (!$active) {
                continue;
            }

            (new Fetcher())->fetchPlatform($platformLower);
        }
    }

    /**
     * Define cron schedule.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function defineCronSchedule(array $schedules): array
    {
        $schedules['every_two_hours'] = [
            'interval' => 7200,
            'display' => __('Every 2 hours'),
        ];

        return $schedules;
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
        add_filter('cron_schedules', [$this, 'defineCronSchedule']);
        add_action('wp_social_wall_fetch_posts', [$this, 'fetchPosts']);

        $activationHook = new ActivationHook();
        register_activation_hook($this->file, [$activationHook, 'run']);
    }
}
