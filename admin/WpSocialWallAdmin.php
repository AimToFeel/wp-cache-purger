<?php

namespace WpSocialWall\admin;

use Exception;
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
    }

    /**
     * On social wall plugin initialize.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function initialize($params): void
    {
        register_setting('wp_social_wall', 'wp_social_wall_api_token');

        $platforms = WP_SOCIAL_WALL_PLATFORMS;

        foreach ($platforms as $platform) {
            $platformLower = strtolower($platform);
            register_setting('wp_social_wall', "wp_social_wall_{$platformLower}_active");
        }

        $this->setupAdminPage();
    }

    /**
     * Set up admin page setting inputs.
     * Prevents API call on every admin screen.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    private function setupAdminPage(): void
    {
        if (!isset($_GET['page']) || $_GET['page'] !== 'wp-social-wall') {
            return;
        }

        $token = get_option('wp_social_wall_api_token');
        $connectedPlatforms = $this->fetchConnectedPlatforms();

        if ($connectedPlatforms !== false) {
            $platforms = WP_SOCIAL_WALL_PLATFORMS;
            foreach ($platforms as $platform) {
                $platformLower = strtolower($platform);

                add_settings_section("wp-social-wall-settings-{$platformLower}", "{$platform} settings", function () use ($platformLower, $connectedPlatforms) {$this->renderSection($platformLower, $connectedPlatforms);}, 'wp-social-wall');
                add_settings_field("wp-social-wall-{$platformLower}-active", "Include {$platform} posts", function () use ($platformLower) {$this->renderPlatformActiveInput($platformLower);}, 'wp-social-wall', "wp-social-wall-settings-{$platformLower}");
            }
        }

        add_settings_section('wp-social-wall-settings-token', 'Token settings', function () use ($connectedPlatforms) {$this->renderTokenSection($connectedPlatforms !== false);}, 'wp-social-wall');
        add_settings_field('wp-social-wall-token', 'API token', function () use ($token) {$this->renderApiTokenInput($token);}, 'wp-social-wall', 'wp-social-wall-settings-token');
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
        $site = urlencode(get_admin_url());

        echo '<hr />';

        if ($connectedPlatforms !== false && in_array($platform, $connectedPlatforms)) {
            echo '<div><p><i style="width: 12px; height: 12px; border-radius: 50%; background: #32a852; display: inline-block;"></i> Platform is connected</p></div>';
        } else {
            echo '<div><p><i style="width: 12px; height: 12px; border-radius: 50%; background: #d63638; display: inline-block;"></i> Platform not connected</p></div>';
        }

        switch ($platform) {
            case 'facebook':
                $url = esc_url("https://wp-social-wall.feelgoodtechnology.nl/register-platform?action=facebook-authentication&authenticationToken={$token}&redirectUrl={$site}");
                echo "<a class=\"button button-primary\" href=\"{$url}\">Connect with Facebook</a>";
                break;
            case 'twitter':
                $url = esc_url("https://wp-social-wall.feelgoodtechnology.nl/register-platform?action=twitter-authentication&authenticationToken={$token}&redirectUrl={$site}");
                echo "<a class=\"button button-primary\" href=\"{$url}\">Connect with Twitter</a>";
                break;
            case 'instagram':
                $url = esc_url("https://wp-social-wall.feelgoodtechnology.nl/register-platform?action=instagram-authentication&authenticationToken={$token}&redirectUrl={$site}");
                echo "<a class=\"button button-primary\" href=\"{$url}\">Connect with Instagram</a>";
                break;
            default:
        }
    }

    /**
     * Render token section.
     *
     * @param bool $connected
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function renderTokenSection($connected): void
    {
        echo '<hr />';

        echo '<p>To get started we need to connect the plugin with our API. Please visit the <a href="https://wp-social-wall.feelgoodtechnology.nl/token" target="_blank">WP Social Wall</a> site to request an API token. Yes, this is free!</p>';
        echo '<p>Please make sure you read the <a href="https://wp-social-wall.feelgoodtechnology.nl/api-terms-and-conditions" target="_blank">Terms and Conditions</a> and <a href="https://wp-social-wall.feelgoodtechnology.nl/api-privacy-policy" target="_blank">Privacy Policy</a> of WP Social Wall API before entering your API token. By entering and saving your API token in this form you agree with the privacy policy, terms and conditions.</p>';

        $token = get_option('wp_social_wall_api_token');

        if ($connected) {
            echo '<p id="api-token" data-token="' . esc_attr($token) . '"><i style="width: 12px; height: 12px; border-radius: 50%; background: #32a852; display: inline-block;"></i> Connection with wp-social-wall API enstablished, access token: "' . esc_html($token) . '".</p>';
        } else {
            echo '<p id="api-token"><i style="width: 12px; height: 12px; border-radius: 50%; background: #d63638; display: inline-block;"></i> Connection with wp-social-wall API not yet enstablished.</p>';
        }
    }

    /**
     * Render platform active input.
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

        echo '<input name="wp_social_wall_' . esc_attr($platform) . '_active" type="checkbox" value="1" ';
        checked(1, $value, true);
        echo ' />';
    }

    /**
     * Render api token input.
     *
     * @param string $token
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function renderApiTokenInput($token): void
    {
        echo '<input name="wp_social_wall_api_token" type="text" value="' . esc_attr($token) . '" />';
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
     * Fetch connected platforms from WP Social Wall API.
     *
     * @return array|boolean
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    private function fetchConnectedPlatforms()
    {
        try {
            $response = (new TokensRequest())->get();

            if (isset($response) && isset($response->data)) {
                return $response->data;
            }

            return false;
        } catch (Exception $e) {
            return false;
        }
    }
}
