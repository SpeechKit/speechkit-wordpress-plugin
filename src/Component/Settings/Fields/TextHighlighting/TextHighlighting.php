<?php

declare(strict_types=1);

/**
 * Setting: Text highlighting
 *
 * @package Beyondwords\Wordpress
 * @author  Stuart McAlpine <stu@beyondwords.io>
 * @since   5.0.0
 */

namespace Beyondwords\Wordpress\Component\Settings\Fields\TextHighlighting;

use Beyondwords\Wordpress\Component\Settings\SettingsUtils;

/**
 * TextHighlighting setup
 *
 * @since 5.0.0
 */
class TextHighlighting
{
    /**
     * Option name.
     *
     * @since 5.0.0
     */
    public const OPTION_NAME = 'beyondwords_player_highlight_sections';

    /**
     * Init.
     *
     * @since 5.0.0
     */
    public function init()
    {
        add_action('admin_init', array($this, 'addSetting'));
        add_action('update_option_' . self::OPTION_NAME, function () {
            add_filter('beyondwords_sync_to_dashboard', function ($fields) {
                $fields[] = self::OPTION_NAME;
                return $fields;
            });
        });
    }

    /**
     * Init setting.
     *
     * @since 5.0.0
     *
     * @return void
     */
    public function addSetting()
    {
        register_setting(
            'beyondwords_player_settings',
            'beyondwords_player_highlight_sections',
            [
                'type'              => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default'           => false,
            ]
        );

        add_settings_field(
            'beyondwords-text-highlighting',
            __('Text highlighting', 'speechkit'),
            array($this, 'render'),
            'beyondwords_player',
            'styling'
        );
    }

    /**
     * Render setting field.
     *
     * @since 5.0.0
     *
     * @return void
     **/
    public function render()
    {
        $enabled    = get_option('beyondwords_player_highlight_sections', '');
        $lightTheme = get_option('beyondwords_player_light_theme');
        $darkTheme  = get_option('beyondwords_player_dark_theme');
        ?>
        <div class="beyondwords-setting__player beyondwords-setting__player--text-highlighting">
            <label>
                <input type="hidden" name="beyondwords_player_highlight_sections" value="" />
                <input
                    type="checkbox"
                    id="beyondwords_player_highlight_sections"
                    name="beyondwords_player_highlight_sections"
                    value="body"
                    <?php checked($enabled, 'body'); ?>
                />
                <?php esc_html_e('Highlight the current paragraph during audio playback', 'speechkit'); ?>
            </label>
        </div>
        <div>
            <h3 class="subheading">Light theme settings</h3>
            <?php
            SettingsUtils::colorInput(
                __('Highlight color'),
                'beyondwords_player_light_theme[highlight_color]',
                $lightTheme['highlight_color'] ?? '',
            );
            ?>
        </div>
        <div>
            <h3 class="subheading">Dark theme settings</h3>
            <?php
            SettingsUtils::colorInput(
                __('Highlight color'),
                'beyondwords_player_dark_theme[highlight_color]',
                $darkTheme['highlight_color'] ?? '',
            );
            ?>
        </div>
        <?php
    }
}