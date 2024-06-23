<?php

declare(strict_types=1);

/**
 * Settings > BeyondWords > Player
 *
 * @package Beyondwords\Wordpress
 * @author  Stuart McAlpine <stu@beyondwords.io>
 * @since   4.8.0
 */

namespace Beyondwords\Wordpress\Component\Settings\Tabs\Player;

use Beyondwords\Wordpress\Component\Settings\Fields\CallToAction\CallToAction;
use Beyondwords\Wordpress\Component\Settings\Fields\PlaybackFromSegments\PlaybackFromSegments;
use Beyondwords\Wordpress\Component\Settings\Fields\PlaybackControls\PlaybackControls;
use Beyondwords\Wordpress\Component\Settings\Fields\PlayerColors\PlayerColors;
use Beyondwords\Wordpress\Component\Settings\Fields\PlayerUI\PlayerUI;
use Beyondwords\Wordpress\Component\Settings\Fields\PlayerStyle\PlayerStyle;
use Beyondwords\Wordpress\Component\Settings\Fields\WidgetPosition\WidgetPosition;
use Beyondwords\Wordpress\Component\Settings\Fields\WidgetStyle\WidgetStyle;
use Beyondwords\Wordpress\Component\Settings\Fields\TextHighlighting\TextHighlighting;
use Beyondwords\Wordpress\Component\Settings\SettingsUtils;

/**
 * "Player" settings tab
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @since 4.8.0
 */
class Player
{
    /**
     * API client.
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
        (new PlayerUI())->init();
        (new PlayerStyle($this->apiClient))->init();
        (new PlayerColors())->init();
        (new CallToAction())->init();
        (new WidgetStyle())->init();
        (new WidgetPosition())->init();
        (new TextHighlighting())->init();
        (new PlaybackFromSegments())->init();
        (new PlaybackControls())->init();

        add_action('admin_init', array($this, 'addSettingsSection'), 5);
        add_action('admin_enqueue_scripts', array($this, 'syncCheck'));
    }

    /**
     * Add Settings sections.
     *
     * @since  4.8.0
     */
    public function addSettingsSection()
    {
        add_settings_section(
            'player',
            __('Player', 'speechkit'),
            array($this, 'sectionCallback'),
            'beyondwords_player',
        );

        add_settings_section(
            'playback-controls',
            __('Playback controls', 'speechkit'),
            false,
            'beyondwords_player',
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
        $syncUrl = admin_url('options-general.php?page=beyondwords&tab=player&sync_to_api=1');
        ?>
        <p class="description">
            <?php
            esc_html_e(
                'Do we want a description for consistency?', // phpcs:ignore Generic.Files.LineLength.TooLong
                'speechkit'
            );
            ?>
        </p>
        <p>
            <a href="<?php echo esc_url($syncUrl); ?>" class="button button-secondary">
                <?php _e('Sync from Dashboard', 'speechkit'); ?>
            </a>
            <a href="<?php echo esc_url($syncUrl); ?>" class="button button-secondary">
                <?php _e('Sync to Dashboard', 'speechkit'); ?>
            </a>
        </p>
        <?php
    }

    /**
     * Check whether we want to sync to/from the API.
     *
     * We don't automatically sync to the API. We only sync if a
     * "Sync Settings to Dashboard" button is pressed.
     *
     * @return void
     */
    public function syncCheck($hook)
    {
        if ($hook !== 'settings_page_beyondwords') {
            return;
        }

        $syncToApi = isset($_GET['sync_to_api']);

        if ($syncToApi) {
            $this->syncToRestApi();
        }
    }

    /**
     * Sync with BeyondWords REST API.
     *
     * @since 4.8.0
     *
     * @return void
     **/
    public function syncToRestApi()
    {
        $params = [];

        $options = SettingsUtils::getSyncedOptions('player');

        foreach ($options as $name => $args) {
            if (array_key_exists('path', $args)) {
                $transient = get_transient('beyondwords/sync/' . $name);
                if ($transient !== false) {
                    $params[$args['path']] = $transient;
                    // add_settings_error('beyondwords_settings', 'beyondwords_settings', '<span class="dashicons dashicons-rest-api"></span> Syncing ' . $name . ' to /player.' . $args['path'], 'info');
                    delete_transient('beyondwords/sync/' . $name);
                }
            }
        }

        if (count($params)) {
            // Sync WordPress -> REST API
            $result = $this->apiClient->updatePlayerSettings($params);

            if (! $result) {
                // Error notice
                add_settings_error('beyondwords_settings', 'beyondwords_settings', '<span class="dashicons dashicons-rest-api"></span> Error syncing to the BeyondWords dashboard. The settings may not in sync.', 'error');
            }
        }
    }
}
