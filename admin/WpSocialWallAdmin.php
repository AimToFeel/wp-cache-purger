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

            register_setting('wp_social_wall', "wp_social_wall_{$platformLower}_active");
            add_settings_section("wp-social-wall-settings-{$platformLower}", "{$platform} settings", function () use ($platformLower, $connectedPlatforms) {$this->renderSection($platformLower, $connectedPlatforms);}, 'wp-social-wall');
            add_settings_field("wp-social-wall-{$platformLower}-active", "Include {$platform} posts", function () use ($platformLower) {$this->renderPlatformActiveInput($platformLower);}, 'wp-social-wall', "wp-social-wall-settings-{$platformLower}");
        }
    }

    /**
     * Render platform section.
     *
     * @param string $platform
     * @param array $connectedPlatforms
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function renderSection($platform, $connectedPlatforms): void
    {
        $token = get_option('wp_social_wall_api_token');
        $site = get_admin_url() . 'admin.php?page=wp-social-wall';

        echo '<hr />';

        if (in_array($platform, $connectedPlatforms)) {
            echo '<div><p><i style="width: 12px; height: 12px; border-radius: 50%; background: #32a852; display: inline-block;"></i> Platform is connected</p></div>';
        } else {
            echo '<div><p><i style="width: 12px; height: 12px; border-radius: 50%; background: #d63638; display: inline-block;"></i> Platform not connected</p></div>';
        }

        switch ($platform) {
            case 'facebook':
                echo "<a class=\"button button-primary\" href=\"https://wp-social-wall.feelgoodtechnology.nl/?action=facebook-authentication&authenticationToken={$token}&redirectUrl={$site}\">Connect with Facebook</a>";
                break;
            case 'twitter':
                echo '<button type="button" class="button button-primary" id="twitter-login-button">Connect with Twitter</button>';
                break;
            case 'instagram':
                echo "<a class=\"button button-primary\" href=\"https://wp-social-wall.feelgoodtechnology.nl/?action=instagram-authentication&authenticationToken={$token}&redirectUrl={$site}\">Connect with Instagram</a>";
                break;
            default:
        }
    }

    /**
     * Rebder platform active input.
     *
     * @param string $platform
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function renderPlatformActiveInput($platform): void
    {
        $value = get_option('wp_social_wall_' . $platform . '_active');

        echo '<input name="wp_social_wall_' . $platform . '_active" type="checkbox" value="1" ';
        checked(1, $value, true);
        echo ' />';
    }

    /**
     * Create admin menu item.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function makeAdminMenu(): void
    {
        add_menu_page('WP Social Wall', 'WP Social Wall', 'manage_options', 'wp-social-wall', [$this, 'renderSettingsPage'], plugin_dir_url($this->file) . 'admin/assets/brickwall-small.png');
    }

    /**
     * Render settings page.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function renderSettingsPage(): void
    {
        require_once HOME_DIRETORY_WP_SOCIAL_WALL . '/admin/templates/SetttingsTemplate.php';
    }

    /**
     * Render site token information.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function renderTokenInformation(): void
    {
        $token = get_option('wp_social_wall_api_token');

        if ($token) {
            echo '<p id="api-token" data-token="' . $token . '">Connection with wp-social-wall API enstablished, access token: "' . $token . '".</p>';
        } else {
            echo '<p id="api-token">Connection with wp-social-wall API not yet enstablished.</p>';
        }
    }

    /**
     * Register site to WP Social Wall API.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
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

    /**
     * Catch Twitter callback and store to WP Social Wall API.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    private function registerTwitterCallback(): void
    {
        if (!isset($_GET['oauthToken']) || !isset($_GET['oauthVerifier'])) {
            return;
        }

        (new TokensRequest())->store('twitter', $_GET['oauthToken'], $_GET['oauthVerifier']);
    }

    /**
     * Fetch connected platforms from WP Social Wall API.
     *
     * @return array
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    private function fetchConnectedPlatforms(): array
    {
        return (new TokensRequest())->get()->data;
    }
}
