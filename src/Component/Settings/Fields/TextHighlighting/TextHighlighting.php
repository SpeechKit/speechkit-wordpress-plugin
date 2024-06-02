<?php

declare(strict_types=1);

/**
 * Setting: Text highlighting
 *
 * @package Beyondwords\Wordpress
 * @author  Stuart McAlpine <stu@beyondwords.io>
 * @since   4.8.0
 */

namespace Beyondwords\Wordpress\Component\Settings\Fields\TextHighlighting;

use Beyondwords\Wordpress\Component\Settings\SettingsUtils;

/**
 * TextHighlighting setup
 *
 * @since 4.8.0
 */
class TextHighlighting
{
    /**
     * Init.
     *
     * @since 4.0.0
     */
    public function init()
    {
        add_action('admin_init', array($this, 'addSetting'));
    }

    /**
     * Init setting.
     *
     * @since  4.8.0
     *
     * @return void
     */
    public function addSetting()
    {
        register_setting(
            'beyondwords_player_settings',
            'beyondwords_text_highlighting',
            [
                'default' => '',
            ]
        );

        add_settings_field(
            'beyondwords-text-highlighting',
            __('Text highlighting', 'speechkit'),
            array($this, 'render'),
            'beyondwords_player',
            'player'
        );
    }

    /**
     * Render setting field.
     *
     * @since 4.8.0
     *
     * @return void
     **/
    public function render()
    {
        $enabled    = get_option('beyondwords_text_highlighting', '');
        $lightTheme = get_option('beyondwords_player_light_theme');
        $darkTheme  = get_option('beyondwords_player_dark_theme');
        ?>
        <div>
            <label>
                <input
                    type="checkbox"
                    name="beyondwords_text_highlighting"
                    value="1"
                    <?php checked($enabled, '1'); ?>
                />
                <?php esc_html_e('Highlight the current paragraph during audio playback', 'speechkit'); ?>
            </label>
        </div>
        <div>
            <h3 class="subheading">Light theme settings</h3>
            <?php
            echo SettingsUtils::colorInput(
                __('Highlight color'),
                'beyondwords_player_light_theme[highlight_color]',
                $lightTheme['highlight_color'] ?? '',
            );
            ?>
        </div>
        <div>
            <h3 class="subheading">Dark theme settings</h3>
            <?php
            echo SettingsUtils::colorInput(
                __('Highlight color'),
                'beyondwords_player_dark_theme[highlight_color]',
                $darkTheme['highlight_color'] ?? '',
            );
            ?>
        </div>
        <?php
    }
}