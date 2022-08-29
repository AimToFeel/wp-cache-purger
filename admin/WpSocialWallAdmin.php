<?php

namespace WpSocialWall\admin;

use WpSocialWall\src\api\SitesRequest;
use WpSocialWall\src\api\TokensRequest;

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
        $platforms = WP_SOCIAL_WALL_PLATFORMS;

        $this->registerSite();

        $this->registerTwitterCallback();

        $connectedPlatforms = $this->fetchConnectedPlatforms();

        foreach ($platforms as $platform) {
            $platformLower = strtolower($platform);

            register_setting('wp_social_wall', "wp_social_wall_{$platformLower}_id", [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => null,
            ]);
            register_setting('wp_social_wall', "wp_social_wall_{$platformLower}_token", [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => null,
            ]);
            register_setting('wp_social_wall', "wp_social_wall_{$platformLower}_user_id", [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => null,
            ]);
            register_setting('wp_social_wall', "wp_social_wall_{$platformLower}_active");
            add_settings_section("wp-social-wall-settings-{$platformLower}", "{$platform} settings", function () use ($platformLower, $connectedPlatforms) {$this->renderSection($platformLower, $connectedPlatforms);}, 'social-wall');
            add_settings_field("wp-social-wall-{$platformLower}-active", "Include {$platform} posts", function () use ($platformLower) {$this->renderPlatformActiveInput($platformLower);}, 'social-wall', "wp-social-wall-settings-{$platformLower}");
        }

    }

    public function renderSection($platform, $connectedPlatforms): void
    {
        $token = get_option('wp_social_wall_api_token');
        $site = explode('wp-admin/', $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'])[0] . 'wp-admin/admin.php?page=social-wall';

        echo '<hr />';

        if (in_array($platform, $connectedPlatforms)) {
            echo '<div><p>Platform is connected: <i style="width: 10px; height: 10px; border-radius: 50%; background: #32a852;"></i></p></div>';
        } else {
            echo '<div><p>Platform not connected: <i style="width: 10px; height: 10px; border-radius: 50%; background: #d63638;"></i></p></div>';
        }

        switch ($platform) {
            case 'facebook':
                echo "<a class=\"button button-primary\" href=\"https://wp-social-wall.feelgoodtechnology.nl/?action=facebook-authentication&authenticationToken={$token}&redirectUrl={$site}\">Connect with Facebook</a>";
                break;
            case 'twitter':
                echo '<button type="button" class="button button-primary" id="twitter-login-button">Connect with Twitter</button>';
                break;
            default:
        }
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
        add_action('wp_social_wall_render_token_information', [$this, 'renderTokenInformation']);
    }

    public function renderTokenInformation(): void
    {
        $token = get_option('wp_social_wall_api_token');

        if ($token) {
            echo '<p id="api-token" data-token="' . $token . '">Connection with wp-social-wall API enstablished, access token: "' . $token . '".</p>';
        } else {
            echo '<p id="api-token">Connection with wp-social-wall API not yet enstablished.</p>';
        }
    }

    private function registerSite(): void
    {
        $siteToken = get_option('wp_social_wall_api_token');

        if ($siteToken) {
            return;
        }

        $result = (new SitesRequest())->store($_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);

        if ($result) {
            update_option('wp_social_wall_api_token', $result->site->access_token);
        }
    }

    private function registerTwitterCallback(): void
    {
        if (!isset($_GET['oauthToken']) || !isset($_GET['oauthVerifier'])) {
            return;
        }

        (new TokensRequest())->store('twitter', $_GET['oauthToken'], $_GET['oauthVerifier']);
    }

    private function fetchConnectedPlatforms(): array
    {
        return (new TokensRequest())->get()->data;
    }
}
