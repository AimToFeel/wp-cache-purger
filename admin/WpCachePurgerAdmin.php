<?php

namespace WpCachePurger\admin;

class WpCachePurgerAdmin
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
        register_setting('wp_cache_purger', 'wp_cache_purger_authentication_token');

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
        if (!isset($_GET['page']) || $_GET['page'] !== 'wp-cache-purger') {
            return;
        }

        $token = get_option('wp_cache_purger_authentication_token');
        add_settings_section('wp-cache-purger-settings-token', 'Authentication token settings', function () {$this->renderTokenSection();}, 'wp-cache-purger');
        add_settings_field('wp-cache-purger-token', 'Authentication token', function () use ($token) {$this->renderApiTokenInput($token);}, 'wp-cache-purger', 'wp-cache-purger-settings-token');
    }

    /**
     * Render token section.
     *
     * @return void
     *
     * @author Niek van der Velde <niek@aimtofeel.com>
     * @version 1.0.0
     */
    public function renderTokenSection(): void
    {}

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
        echo '<input name="wp_cache_purger_authentication_token" type="text" value="' . esc_attr($token) . '" />';
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
        add_menu_page('WP Cache Purger', 'WP Cache Purger', 'manage_options', 'wp-cache-purger', [$this, 'renderSettingsPage'], 'dashicons-database-remove');
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
        require_once HOME_DIRECTORY_WP_CACHE_PURGER . '/admin/templates/SettingsTemplate.php';
    }
}
