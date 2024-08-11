<?php

declare(strict_types=1);

/**
 * Settings > BeyondWords > Content
 *
 * @package Beyondwords\Wordpress
 * @author  Stuart McAlpine <stu@beyondwords.io>
 * @since   4.8.0
 */

namespace Beyondwords\Wordpress\Component\Settings\Tabs\Content;

use Beyondwords\Wordpress\Component\Settings\Fields\IncludeExcerpt\IncludeExcerpt;
use Beyondwords\Wordpress\Component\Settings\Fields\IncludeTitle\IncludeTitle;
use Beyondwords\Wordpress\Component\Settings\Fields\PreselectGenerateAudio\PreselectGenerateAudio;
use Beyondwords\Wordpress\Component\Settings\SettingsUtils;

/**
 * "Content" settings tab
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @since 4.8.0
 */
class Content
{
    /**
     * @var \Beyondwords\Wordpress\Core\ApiClient
     */
    private $apiClient;

    /**
     * Constructor.
     *
     * @since 4.8.0
     */
    public function __construct($apiClient)
    {
        $this->apiClient = $apiClient;
    }

    /**
     * Init
     */
    public function init()
    {
        (new IncludeTitle($this->apiClient))->init();
        (new IncludeExcerpt())->init();
        (new PreselectGenerateAudio())->init();

        add_action('admin_init', array($this, 'addSettingsSection'), 5);
        add_action('admin_init', array($this, 'maybeSync'), 10);
    }

    /**
     * Add Settings sections.
     *
     * @since  4.8.0
     */
    public function addSettingsSection()
    {
        add_settings_section(
            'content',
            __('Content', 'speechkit'),
            array($this, 'sectionCallback'),
            'beyondwords_content',
        );
    }

    /**
     * Section callback
     *
     * @since 4.8.0
     *
     * @return void
     **/
    public function sectionCallback()
    {
        ?>
        <p class="description">
            <?php
            esc_html_e(
                'Only future content will be affected. To apply changes to existing content, please regenerate each post.', // phpcs:ignore Generic.Files.LineLength.TooLong
                'speechkit'
            );
            ?>
        </p>
        <?php
    }

    /**
     * Maybe sync to REST API.
     *
     * @since 4.8.0
     *
     * @return void
     **/
    public function maybeSync()
    {
        $submitted = isset($_POST['submit-content' ]); // phpcs:ignore WordPress.Security.NonceVerification

        if (! $submitted) {
            return;
        }

        // Sync WordPress -> REST API
        $projectResult    = $this->apiClient->updateProject($this->getProjectParams());

        if (! $projectResult) {
            // Error notice
            add_settings_error(
                'beyondwords_settings',
                'beyondwords_settings',
                '<span class="dashicons dashicons-rest-api"></span> Error syncing to the BeyondWords dashboard. The settings may not in sync.', // phpcs:ignore Generic.Files.LineLength.TooLong
                'error'
            );
        }
    }

    /**
     * Get the project body params, ready for REST API call.
     *
     * @since 4.8.0
     *
     * @return array REST API body params.
     */
    public function getProjectParams()
    {
        $params['title']['enabled'] = (bool) get_option('beyondwords_include_title', true);

        return array_filter($params);
    }
}
