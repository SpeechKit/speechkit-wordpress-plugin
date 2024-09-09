<?php

declare(strict_types=1);

/**
 * Setting: Player colors
 *
 * @package Beyondwords\Wordpress
 * @author  Stuart McAlpine <stu@beyondwords.io>
 * @since   5.0.0
 */

namespace Beyondwords\Wordpress\Component\Settings\Fields\PlayerColors;

use Beyondwords\Wordpress\Component\Settings\SettingsUtils;

/**
 * PlayerColors setup
 *
 * @since 5.0.0
 */
class PlayerColors
{
    /**
     * Option name.
     */
    public const OPTION_NAME_THEME = 'beyondwords_player_theme';

    /**
     * Option name.
     */
    public const OPTION_NAME_LIGHT_THEME = 'beyondwords_player_light_theme';

    /**
     * Option name.
     */
    public const OPTION_NAME_DARK_THEME = 'beyondwords_player_dark_theme';

    /**
     * Option name.
     */
    public const OPTION_NAME_VIDEO_THEME = 'beyondwords_player_video_theme';

    /**
     * Init.
     *
     * @since 5.0.0
     */
    public function init()
    {
        add_action('admin_init', array($this, 'addPlayerThemeSetting'));
        add_action('admin_init', array($this, 'addPlayerColorsSetting'));
        add_action('update_option_' . self::OPTION_NAME_THEME, function () {
            add_filter('beyondwords_sync_to_dashboard', function ($fields) {
                $fields[] = self::OPTION_NAME_THEME;
                return $fields;
            });
        });
        add_action('update_option_' . self::OPTION_NAME_LIGHT_THEME, function () {
            add_filter('beyondwords_sync_to_dashboard', function ($fields) {
                $fields[] = self::OPTION_NAME_LIGHT_THEME;
                return $fields;
            });
        });
        add_action('update_option_' . self::OPTION_NAME_DARK_THEME, function () {
            add_filter('beyondwords_sync_to_dashboard', function ($fields) {
                $fields[] = self::OPTION_NAME_DARK_THEME;
                return $fields;
            });
        });
        add_action('update_option_' . self::OPTION_NAME_VIDEO_THEME, function () {
            add_filter('beyondwords_sync_to_dashboard', function ($fields) {
                $fields[] = self::OPTION_NAME_VIDEO_THEME;
                return $fields;
            });
        });
    }

    /**
     * Init "Player color" setting.
     *
     * @since 5.0.0
     *
     * @return void
     */
    public function addPlayerThemeSetting()
    {
        register_setting(
            'beyondwords_player_settings',
            'beyondwords_player_theme',
            [
                'default' => '',
            ]
        );

        add_settings_field(
            'beyondwords-player-theme',
            __('Player theme', 'speechkit'),
            array($this, 'renderPlayerThemeSetting'),
            'beyondwords_player',
            'styling'
        );
    }

    /**
     * Init "Player colors" setting.
     *
     * @since 5.0.0
     *
     * @return void
     */
    public function addPlayerColorsSetting()
    {
        register_setting(
            'beyondwords_player_settings',
            'beyondwords_player_light_theme',
            [
                'default' => [
                    'background_color' => '#F5F5F5',
                    'icon_color'       => '#000',
                    'text_color'       => '#111',
                    'highlight_color'  => '#EEE',
                ],
                'sanitize_callback' => array($this, 'sanitizeColorsArray'),
            ]
        );

        register_setting(
            'beyondwords_player_settings',
            'beyondwords_player_dark_theme',
            [
                'default' => [
                    'background_color' => '#F5F5F5',
                    'icon_color'       => '#000',
                    'text_color'       => '#111',
                    'highlight_color'  => '#EEE',
                ],
                'sanitize_callback' => array($this, 'sanitizeColorsArray'),
            ]
        );

        register_setting(
            'beyondwords_player_settings',
            'beyondwords_player_video_theme',
            [
                'default' => [
                    'background_color' => '#000',
                    'icon_color'       => '#FFF',
                    'text_color'       => '#FFF',
                ],
                'sanitize_callback' => array($this, 'sanitizeColorsArray'),
            ]
        );

        add_settings_field(
            'beyondwords-player-colors',
            __('Player colors', 'speechkit'),
            array($this, 'renderPlayerColorsSetting'),
            'beyondwords_player',
            'styling'
        );
    }

    /**
     * Render setting field.
     *
     * @since 5.0.0
     *
     * @return string
     **/
    public function renderPlayerThemeSetting()
    {
        $current = get_option('beyondwords_player_theme');
        $themeOptions = $this->getPlayerThemeOptions();
        ?>
        <div class="beyondwords-setting__player beyondwords-setting__player--player-colors">
            <select name="beyondwords_player_theme">
                <?php
                foreach ($themeOptions as $option) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($option['value']),
                        selected($option['value'], $current),
                        esc_html($option['label'])
                    );
                }
                ?>
            </select>
        </div>
        <?php
    }

    /**
     * Sanitise the colors array setting value.
     *
     * @since 5.0.0
     *
     * @param array $value The submitted value.
     *
     * @return array The sanitized value.
     **/
    public function sanitizeColorsArray($value)
    {
        if (!is_array($value)) {
            return [];
        }

        $value['background_color'] = $this->sanitizeColor($value['background_color'] ?: '');
        $value['text_color']       = $this->sanitizeColor($value['text_color']       ?: '');
        $value['icon_color']       = $this->sanitizeColor($value['icon_color']       ?: '');

        // Highlight doesn't exist for video player
        if (!empty($value['highlight_color'])) {
            $value['highlight_color'] = $this->sanitizeColor($value['highlight_color']);
        }

        return $value;
    }

    /**
     * Sanitize an individual color value.
     *
     * @since 5.0.0
     *
     * @param string $value The submitted individual color value.
     *
     * @return array The sanitized value.
     **/
    public function sanitizeColor($value)
    {
        $value = trim((string)$value);

        // Prepend hash to hexidecimal values if missing
        if (preg_match("/^[0-9a-fA-F]+$/", $value)) {
            $value = '#' . $value;
        }

        return $value;
    }

    /**
     * Get all options for the current component.
     *
     * @since 5.0.0
     *
     * @return string[] Associative array of player theme options.
     **/
    public function getPlayerThemeOptions()
    {
        $themeOptions = [
            [
                'value' => 'light',
                'label' => 'Light (default)',
            ],
            [
                'value' => 'dark',
                'label' => 'Dark',
            ],
            [
                'value' => 'auto',
                'label' => 'Auto',
            ],
        ];

        return $themeOptions;
    }

    /**
     * Render setting field.
     *
     * @since 5.0.0
     *
     * @return string
     **/
    public function renderPlayerColorsSetting()
    {
        $lightTheme = get_option('beyondwords_player_light_theme');
        $darkTheme  = get_option('beyondwords_player_dark_theme');
        $videoTheme = get_option('beyondwords_player_video_theme');

        $this->playerColorsTable(
            __('Light theme settings'),
            'beyondwords_player_light_theme',
            $lightTheme,
        );

        $this->playerColorsTable(
            __('Dark theme settings'),
            'beyondwords_player_dark_theme',
            $darkTheme,
        );

        $this->playerColorsTable(
            __('Video theme settings'),
            'beyondwords_player_video_theme',
            $videoTheme,
        );
    }

    /**
     * A player colors table.
     *
     * @since 5.0.0
     *
     * @return string
     **/
    public function playerColorsTable($title, $name, $value)
    {
        ?>
        <h3 class="subheading">
            <?php echo esc_html($title); ?>
        </h3>
        <div class="color-pickers">
            <div class="row">
                <?php
                SettingsUtils::colorInput(
                    __('Background'),
                    sprintf('%s[background_color]', $name),
                    $value['background_color'] ?: ''
                );
                ?>
            </div>
            <div class="row">
                <?php
                SettingsUtils::colorInput(
                    __('Icons'),
                    sprintf('%s[icon_color]', $name),
                    $value['icon_color'] ?: ''
                );
                ?>
            </div>
            <div class="row">
                <?php
                SettingsUtils::colorInput(
                    __('Text color'),
                    sprintf('%s[text_color]', $name),
                    $value['text_color'] ?: ''
                );
                ?>
            </div>
        </div>
        <?php
    }
}