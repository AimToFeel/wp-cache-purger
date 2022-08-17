<?php

namespace WpSocialWall\admin;

class WpSocialWallAdmin
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
        $platforms = [
            'Facebook', 'Twitter', 'TikTok', 'Linkedin',
        ];

        foreach ($platforms as $platform) {
            $platformLower = strtolower($platform);

            register_setting('wp_social_wall', "wp_social_wall_{$platformLower}_id", [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => null,
            ]);
            register_setting('wp_social_wall', "wp_social_wall_{$platformLower}_active");
            add_settings_section("wp-social-wall-settings-{$platformLower}", "{$platform} settings", [$this, 'renderFacebookSection'], 'social-wall');
            add_settings_field("wp-social-wall-{$platformLower}-active", "Include {$platform} posts", function () use ($platformLower) {$this->renderPlatformActiveInput($platformLower);}, 'social-wall', "wp-social-wall-settings-{$platformLower}");
            add_settings_field("wp-social-wall-{$platformLower}-id", "{$platform} ID", function () use ($platformLower) {$this->renderPlatformIdInput($platformLower);}, 'social-wall', "wp-social-wall-settings-{$platformLower}");
        }

    }

    public function renderFacebookSection(): void
    {}

    public function renderPlatformIdInput($platform): void
    {
        $value = get_option('wp_social_wall_' . $platform . '_id');

        echo '<input name="wp_social_wall_' . $platform . '_id" type="text" value="' . $value . '" />';
    }

    public function renderPlatformActiveInput($platform): void
    {
        $value = get_option('wp_social_wall_' . $platform . '_active');

        echo '<input name="wp_social_wall_' . $platform . '_active" type="checkbox" value="1" ';
        checked(1, $value, true);
        echo ' />';
    }

    public function makeAdminMenu(): void
    {
        add_menu_page('Social Wall settings', 'Social Wall', 'manage_options', 'social-wall', [$this, 'renderSettingsPage'], plugin_dir_url($this->file) . 'admin/assets/brickwall-small.png');

    }

    public function renderSettingsPage(): void
    {
        require_once HOME_DIRETORY_WP_SOCIAL_WALL . '/admin/templates/SetttingsTemplate.php';
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
        add_action('admin_init', [$this, 'initialize']);
        add_action('admin_menu', [$this, 'makeAdminMenu']);
    }
}
