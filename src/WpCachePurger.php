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
    private $isAdminPanel = false;

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
        $customFields = get_post_custom($postId);
        $selectedPostId = isset($customFields['embedded_in']) ? $customFields['embedded_in'][0] : $postId;
        $url = get_permalink($selectedPostId);
        $token = get_option('wp_cache_purger_authentication_token');

        (new PurgeRequest())->purge($url, $token);
    }

    public function defineAdminClearCacheButton($wp_admin_bar): void
    {
        echo '
        <style type="text/css">
            .wp-cache-purger-admin-bar-button {
                display: block !important;
            }

            .wp-cache-purger-admin-bar-button div:hover {
                cursor: pointer !important;
                color: #d63638 !important;
            }

            .wp-cache-purger-admin-bar-button .ab-icon::before {
                content: "\f17c";
                top: 3px;
            }

            .wp-cache-purger-admin-bar-button div:hover .ab-icon::before {
                color: #d63638 !important;
            }

            .wp-cache-purger-site-bar-button {
                display: block !important;
            }

            .wp-cache-purger-site-bar-button div:hover {
                cursor: pointer !important;
                color: #d63638 !important;
            }

            .wp-cache-purger-site-bar-button .ab-icon::before {
                content: "\f17c";
                top: 3px;
            }

            .wp-cache-purger-site-bar-button div:hover .ab-icon::before {
                color: #d63638 !important;
            }

            @media screen and (max-width: 782px) {
                .wp-cache-purger-site-bar-button, .wp-cache-purger-admin-bar-button {
                    width: 52px !important;
                    overflow: hidden !important;
                }
            }

            .wp-cache-purger-modal {
                top: 0;
                left: 0;
                position: fixed;
                height: 100%;
                width: 100%;
                z-index: 100;
                display: none;
            }

            .wp-cache-purger-modal--open {
                display: block;
            }

            .wp-cache-purger-modal__inner {
                position: absolute;
                top: 20%;
                left: 50%;
                width: calc(100vw - 48px);
                max-width: 400px;
                transform: translateX(-50%);
                background: #fff;
                z-index: 100;
                padding: 24px;
            }

            .wp-cache-purger-modal__backdrop {
                position: absolute;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                z-index: 99;
                background: #000;
                opacity: 0.6;
            }

            .wp-cache-purger-modal__button-container {
                display: flex;
                flex-direction: row;
                gap: 24px;
            }

            .wp-cache-purger-modal__button {
                background: #fff;
                border-radius: 3px;
                border: 1px solid #2271b1;
                flex: 1;
                text-align: center;
                color: #2271b1;
                line-height: 40px;
                font-weight: bold;
            }

            .wp-cache-purger-modal__button--confirm {
                border: 1px solid #d63638;
                color: #d63638;
            }

            .wp-cache-purger-modal__button--confirm:hover {
                cursor: pointer;
                background: #f6d6d7;
            }

            .wp-cache-purger-modal__button--cancel {
                border: 1px solid #2271b1;
                color: #2271b1;
            }

            .wp-cache-purger-modal__button--cancel:hover {
                cursor: pointer;
                background: #cce3f5;
            }

            .wp-cache-purger-modal__title {
                margin: 0 0 8px 0;
            }

            .wp-cache-purger-modal__text {
                margin: 0 0 24px 0;
            }
        </style>
        ';

        $args = [];

        if (!$this->isAdminPanel) {
            echo '
                <script>
                    async function wpCachePurgerPurgePage() {
                        await fetch("/cache-purger/purge", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({
                                url: "' . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . '",
                                authenticationToken: "' . get_option('wp_cache_purger_authentication_token') . '"
                            })
                        });
                    }
                </script>
            ';

            $args = [
                'id' => 'wp-cache-purger',
                'parent' => null,
                'group' => null,
                'title' => '<span class="ab-icon"></span> Clear cache',
                'meta' => [
                    'class' => 'wp-cache-purger-site-bar-button',
                    'title' => 'Clear current page cache',
                    'onclick' => 'wpCachePurgerPurgePage();',
                ],
            ];
        } else {
            $args = [
                'id' => 'wp-cache-purger',
                'parent' => null,
                'group' => null,
                'title' => '<span class="ab-icon"></span> Clear cache',
                'meta' => [
                    'class' => 'wp-cache-purger-admin-bar-button',
                    'title' => 'Clear the whole site cache',
                    'onclick' => 'wpCachePurgerPurgerOpenModal();',
                ],
            ];
        }

        $wp_admin_bar->add_node($args);
    }

    public function addAdminAreaCss(): void
    {
        $this->isAdminPanel = true;

        echo '
            <script>
                function wpCachePurgerPurgerOpenModal() {
                    const modal = document.getElementById("wp-cache-purger-modal");
                    modal.classList.add("wp-cache-purger-modal--open");
                }

                function wpCachePurgerPurgerCloseModal() {
                    const modal = document.getElementById("wp-cache-purger-modal");
                    modal.classList.remove("wp-cache-purger-modal--open");
                }

                async function wpCachePurgerPurgerPurgeAll() {
                    await fetch("/cache-purger/purge-all", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json"
                        },
                        body: JSON.stringify({ authenticationToken: "' . get_option('wp_cache_purger_authentication_token') . '" })
                    });
                }
            </script>
        ';
    }

    public function addAdminAreaHtml(): void
    {
        echo '
            <div class="wp-cache-purger-modal" id="wp-cache-purger-modal">
                <div class="wp-cache-purger-modal__backdrop"></div>
                <div class="wp-cache-purger-modal__inner">
                    <h2 class="wp-cache-purger-modal__title">Are you sure you want to clear the website cache?</h2>
                    <p class="wp-cache-purger-modal__text">Clearing the cache can result in slower loading times for the end user, clearing the cache is only necessary when a new update has been released or global settings have been changed.</p>

                    <div class="wp-cache-purger-modal__button-container">
                        <button class="wp-cache-purger-modal__button wp-cache-purger-modal__button--confirm" type="button" onClick="wpCachePurgerPurgerPurgeAll(); wpCachePurgerPurgerCloseModal();">Yes, clear cache</button>
                        <button class="wp-cache-purger-modal__button wp-cache-purger-modal__button--cancel" type="button" onClick="wpCachePurgerPurgerCloseModal()">No, cancel</button>
                    </div>
                </div>
            </div>
        ';
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
        add_action('admin_bar_menu', [$this, 'defineAdminClearCacheButton'], 75);
        add_action('admin_head', [$this, 'addAdminAreaCss']);
        add_action('admin_footer', [$this, 'addAdminAreaHtml']);

        $activationHook = new ActivationHook();
        register_activation_hook($this->file, [$activationHook, 'run']);
    }
}
