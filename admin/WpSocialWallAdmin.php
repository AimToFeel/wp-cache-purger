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
        $platforms = [
            'Facebook', 'Twitter', 'Instagram', 'TikTok', 'Linkedin',
        ];

        $this->registerSite();

        $this->registerTwitterCallback();

        foreach ($platforms as $platform) {
            $platformLower = strtolower($platform);

            $this->registerPlatform($platformLower);

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
            add_settings_section("wp-social-wall-settings-{$platformLower}", "{$platform} settings", function () use ($platformLower) {$this->renderSection($platformLower);}, 'social-wall');
            add_settings_field("wp-social-wall-{$platformLower}-active", "Include {$platform} posts", function () use ($platformLower) {$this->renderPlatformActiveInput($platformLower);}, 'social-wall', "wp-social-wall-settings-{$platformLower}");
            add_settings_field("wp-social-wall-{$platformLower}-id", "{$platform} ID", function () use ($platformLower) {$this->renderPlatformIdInput($platformLower);}, 'social-wall', "wp-social-wall-settings-{$platformLower}");

            if ($platformLower === 'facebook') {
                add_settings_field("wp-social-wall-{$platformLower}-user-id", "{$platform} User ID", function () use ($platformLower) {$this->renderPlatformUserIdInput($platformLower);}, 'social-wall', "wp-social-wall-settings-{$platformLower}");
            }

            add_settings_field("wp-social-wall-{$platformLower}-token", "{$platform} Token", function () use ($platformLower) {$this->renderPlatformTokenInput($platformLower);}, 'social-wall', "wp-social-wall-settings-{$platformLower}");
        }

    }

    public function renderSection($platform): void
    {
        echo '<hr />';

        switch ($platform) {
            case 'facebook':
                echo '
                    <div
                        class="fb-login-button"
                        data-size="medium"
                        data-button-type="login_with"
                        data-auto-logout-link="false"
                        data-use-continue-as="false"
                        data-scope="pages_read_engagement"
                    ></div>
                ';
                break;
            case 'twitter':
                echo '<button type="button" class="button button-primary" id="twitter-login-button">Login with Twitter</button>';
                break;
            default:
        }
    }

    public function renderPlatformIdInput($platform): void
    {
        $value = get_option('wp_social_wall_' . $platform . '_id');

        echo '
            <input name="wp_social_wall_' . $platform . '_id" type="text" value="' . $value . '" />
        ';

        switch ($platform) {
            case 'facebook':
                echo '
                    <p>Facebook page ID of which to fetch posts from.</p>
                ';
                break;
            default:
        }
    }

    public function renderPlatformUserIdInput($platform): void
    {
        $value = get_option('wp_social_wall_' . $platform . '_user_id');

        echo '
            <input name="wp_social_wall_' . $platform . '_user_id" type="text" value="' . $value . '" />
        ';
    }

    public function renderPlatformTokenInput($platform): void
    {
        $value = get_option('wp_social_wall_' . $platform . '_token');

        echo '<input name="wp_social_wall_' . $platform . '_token" type="text" value="' . $value . '" />';

        switch ($platform) {
            case 'facebook':
                echo '
                    <button class="button button-secondary" onclick="setFacebookAuthentication(event)">Set Facebook access token</button>
                    <p>Use the sign-in with facebook button. Afterwards click this button to set the access token & user ID. This is a single use token, this field will be reset after saving.</p>
                ';
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

    private function registerPlatform($platform): void
    {
        $active = get_option('wp_social_wall_' . $platform . '_active');
        $token = get_option('wp_social_wall_' . $platform . '_token');
        $pageId = get_option('wp_social_wall_' . $platform . '_id');
        $userId = get_option('wp_social_wall_' . $platform . '_user_id');

        if (!$active || !$token || !$pageId || !$userId) {
            return;
        }

        $result = (new TokensRequest())->store($platform, $token, null, $pageId, $userId);

        if ($result) {
            update_option('wp_social_wall_' . $platform . '_token', null);
        }
    }

    private function registerTwitterCallback(): void
    {
        if (!isset($_GET['oauthToken']) || !isset($_GET['oauthVerifier'])) {
            return;
        }

        (new TokensRequest())->store('twitter', $_GET['oauthToken'], $_GET['oauthVerifier']);
    }
}
