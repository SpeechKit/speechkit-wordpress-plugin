<?php

declare(strict_types=1);

/**
 * Setting: Text highlighting
 *
 * @package Beyondwords\Wordpress
 * @author  Stuart McAlpine <stu@beyondwords.io>
 * @since   4.8.0
 */

namespace Beyondwords\Wordpress\Component\Settings\Fields\PlaybackControls;

/**
 * PlaybackControls setup
 *
 * @since 4.8.0
 */
class PlaybackControls
{
    /**
     * Player Settings docs URL.
     *
     * @var string
     */
    const playerSettingsDocsUrl = 'https://github.com/beyondwords-io/player/blob/main/doc/player-settings.md';

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
            'beyondwords_player_skip_button_style',
            [
                'default' => '',
            ]
        );

        add_settings_field(
            'beyondwords-player-skip-button-style',
            __('Skipping', 'speechkit'),
            array($this, 'render'),
            'beyondwords_player',
            'playback-controls'
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
        $current = get_option('beyondwords_player_skip_button_style');
        $options = $this->getOptions();
        ?>
        <div class="beyondwords-setting--player-skip-button-style">
            <!--
            <select name="beyondwords_player_skip_button_style">
                <?php
                foreach ($options as $option) {
                    printf(
                        '<option value="%s" %s>%s</option>',
                        esc_attr($option['value']),
                        selected($option['value'], $current),
                        esc_html($option['label'])
                    );
                }
                ?>
            </select>
            -->
            <input
                type="text"
                name="beyondwords_player_skip_button_style"
                placeholder="auto"
                value="<?php echo esc_attr($current); ?>"
                size="20"
            />
            <p class="description" style="max-width: 740px;">
                <?php
                echo wp_kses_post(__('The style of skip buttons to show in the player.', 'speechkit')) . " ";
                echo wp_kses_post(__('Possible values are <code>auto</code>, <code>segments</code>, <code>seconds</code> or <code>audios</code>.', 'speechkit')) . " ";
                echo wp_kses_post(__('You can specify the number of seconds to skip, e.g. <code>seconds-15</code> or <code>seconds-15-30</code>.', 'speechkit')) . " ";
                // echo wp_kses_post(__('The <code>auto</code> style uses <code>audios</code> if there is a playlist and <code>segments</code> otherwise.', 'speechkit')) . " ";
                printf(
                    /* translators: %s is replaced with the link to the Player Settings docs */
                    esc_html__('Refer to the %s for more details.', 'speechkit'),
                    sprintf(
                        '<a href="%s" target="_blank" rel="nofollow">%s</a>',
                        esc_url(PlaybackControls::playerSettingsDocsUrl),
                        __('Player Settings docs', 'speechkit')
                    )
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Get all options for the current component.
     *
     * @since 4.8.0
     *
     * @return string[] Associative array of options.
     **/
    public function getOptions()
    {
        $options = [
            [
                'value' => 'auto',
                'label' => __('Auto (default)', 'speechkit'),
            ],
            [
                'value' => 'segments',
                'label' => __('Segments', 'speechkit'),
            ],
            [
                'value' => 'seconds',
                'label' => __('Seconds', 'speechkit'),
            ],
        ];

        return $options;
    }
}
