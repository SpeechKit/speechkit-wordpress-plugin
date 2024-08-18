<?php

declare(strict_types=1);

/**
 * Setting: Default language
 *
 * @package Beyondwords\Wordpress
 * @author  Stuart McAlpine <stu@beyondwords.io>
 * @since   5.0.0
 */

namespace Beyondwords\Wordpress\Component\Settings\Fields\SpeakingRate;

use Beyondwords\Wordpress\Component\Settings\Sync;

/**
 * TitleVoiceSpeakingRate setup
 *
 * @since 5.0.0
 */
class TitleVoiceSpeakingRate
{
    /**
     * Option name.
     *
     * @since 5.0.0
     */
    public const OPTION_NAME = 'beyondwords_title_voice_speaking_rate';

    /**
     * Constructor
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
     * Add setting.
     *
     * @since 5.0.0
     *
     * @return void
     */
    public function addSetting()
    {
        register_setting(
            'beyondwords_voices_settings',
            'beyondwords_title_voice_speaking_rate',
            [
                'type'    => 'integer',
                'default' => 100,
            ]
        );

        add_settings_field(
            'beyondwords-title-speaking-rate',
            __('Title voice speaking rate', 'speechkit'),
            array($this, 'render'),
            'beyondwords_voices',
            'voices'
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
        $current = get_option('beyondwords_title_voice_speaking_rate');
        ?>
        <div class="beyondwords-setting__title-speaking-rate">
            <input
                type="range"
                name="beyondwords_title_voice_speaking_rate"
                class="beyondwords_speaking_rate"
                min="50"
                max="200"
                step="1"
                value="<?php echo esc_attr($current); ?>"
                oninput="this.nextElementSibling.value = `${this.value}%`"
                onload="this.nextElementSibling.value = `${this.value}%`"
            />
            <output><?php echo esc_html($current); ?>%</output>
        </div>
        <p class="description">
            <?php
            esc_html_e(
                'Choose the default speaking rate for your title voice.',
                'speechkit'
            );
            ?>
        </p>
        <?php
    }
}
