<?php

declare(strict_types=1);

namespace Beyondwords\Wordpress\Component\Settings;

use Beyondwords\Wordpress\Compatibility\Elementor\Elementor;

/**
 * BeyondWords Settings Utilities.
 *
 * @package    Beyondwords
 * @subpackage Beyondwords/includes
 * @author     Stuart McAlpine <stu@beyondwords.io>
 * @since      3.5.0
 */
class SettingsUtils
{
    /**
     * Get the post types BeyondWords will consider for compatibility.
     *
     * We don't consider many of the core built-in post types for compatibity
     * because they don't support the features we need such as titles, body,
     * custom fields, etc.
     *
     * @since 3.7.0
     * @since 4.5.0 Renamed from getAllowedPostTypes to getConsideredPostTypes.
     * @since 4.6.2 Exclude wp_font_face and wp_font_family from considered post types.
     *
     * @static
     *
     * @return string[] Array of post type names.
     **/
    public static function getConsideredPostTypes()
    {
        $postTypes = get_post_types();

        $skip = [
            'attachment',
            'custom_css',
            'customize_changeset',
            'nav_menu_item',
            'oembed_cache',
            'revision',
            'user_request',
            'wp_block',
            'wp_font_face',
            'wp_font_family',
            'wp_template',
            'wp_template_part',
            'wp_global_styles',
            'wp_navigation',
        ];

        // Remove the skipped post types
        $postTypes = array_diff($postTypes, $skip);

        return array_values($postTypes);
    }

    /**
     * Get the post types that are compatible with BeyondWords.
     *
     * - Start with the considered post types
     * - Allow publishers to filter the list
     * - Filter again, removing any that are incompatible
     *
     * @since 3.0.0
     * @since 3.2.0 Removed $output parameter to always output names, never objects.
     * @since 3.2.0 Added `beyondwords_post_types` filter.
     * @since 3.5.0 Moved from Core\Utils to Component\Settings\SettingsUtils.
     * @since 3.7.0 Refactored forbidden/allowed/supported post type methods to improve site health debugging info.
     * @since 4.5.0 Renamed from getSupportedPostTypes to getCompatiblePostTypes.
     *
     * @static
     *
     * @return string[] Array of post type names.
     **/
    public static function getCompatiblePostTypes()
    {
        $postTypes = SettingsUtils::getConsideredPostTypes();

        /**
         * Filters the post types supported by BeyondWords.
         *
         * This defaults to all registered post types with 'custom-fields' in their 'supports' array.
         *
         * If any of the supplied post types do not support custom fields then they will be removed
         * from the array.
         *
         * Scheduled for removal in plugin version 5.0.0.
         *
         * @since 3.3.3
         *
         * @deprecated 4.3.0 Replaced with beyondwords_settings_post_types.
         *
         * @param string[] The post types supported by BeyondWords.
         */
        $postTypes = apply_filters('beyondwords_post_types', $postTypes);

        /**
         * Filters the post types supported by BeyondWords.
         *
         * This defaults to all registered post types with 'custom-fields' in their 'supports' array.
         *
         * If any of the supplied post types do not support custom fields then they will be removed
         * from the array.
         *
         * @since 3.3.3 Introduced as beyondwords_post_types
         * @since 4.3.0 Renamed from beyondwords_post_types to beyondwords_settings_post_types.
         *
         * @param string[] The post types supported by BeyondWords.
         */
        $postTypes = apply_filters('beyondwords_settings_post_types', $postTypes);

        // Remove incompatible post types
        $postTypes = array_diff($postTypes, SettingsUtils::getIncompatiblePostTypes());

        return array_values($postTypes);
    }

    /**
     * Get the post types that are incompatible with BeyondWords.
     *
     * The requirements are:
     * - Must support Custom Fields.
     *
     * @since 4.5.0
     *
     * @static
     *
     * @return string[] Array of post type names.
     **/
    public static function getIncompatiblePostTypes()
    {
        $postTypes = SettingsUtils::getConsideredPostTypes();

        // Filter the array, removing unsupported post types
        $postTypes = array_filter($postTypes, function ($postType) {
            if (post_type_supports($postType, 'custom-fields')) {
                return false;
            }

            return true;
        });

        return array_values($postTypes);
    }

    /**
     * Do we have both a BeyondWords API key and Project ID?
     * We need both to call the BeyondWords API.
     *
     * @since  3.0.0
     * @since  4.0.0 Moved from Settings to SettingsUtils
     * @static
     *
     * @return boolean
     */
    public static function hasApiSettings()
    {
        return boolval(get_option('beyondwords_valid_api_connection'));
    }

    /**
     * A color input.
     *
     * @since  4.8.0
     * @static
     *
     * @param string $label Content for the `<label>`
     * @param string $name  `name` attribute for the `<input />`
     * @param string $value `value` attribute for the `<input />`
     *
     * @return string
     */
    public static function colorInput($label, $name, $value)
    {
        ob_start();
        ?>
        <div class="color-input">
            <label>
                    <?php echo esc_attr($label); ?>
            </label>
            <input name="<?php echo esc_attr($name); ?>" type="text" value="<?php echo esc_attr($value); ?>" class="small-text" />
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get the WordPress options we sync to the REST API.
     *
     * @param string $entity Optionally filter the options by entity (auth|project|player)
     *
     * @return array Associative array of option name and sync args (auth,entity,path)
     */
    public static function getSyncedOptions($entity = '')
    {
        // The options we want to sync with our API
        $options = [
            /**
             * Re-authenticate if either API Key or Project ID change, but don't
             * attempt to set the API Key or Project ID.
             **/
            'beyondwords_api_key'    => ['entity' => 'auth'],
            'beyondwords_project_id' => ['entity' => 'auth'],

            /**
             * Project settings synced with REST API.
             * projects/{project_id}
             **/
            'beyondwords_project_language'    => ['entity' => 'project', 'path' => 'language'],
            'beyondwords_project_title_voice' => ['entity' => 'project', 'path' => 'title.voice.id'],
            'beyondwords_project_body_voice'  => ['entity' => 'project', 'path' => 'body.voice.id'],

            /**
             * @todo
             * Project settings NOT synced with REST API.
             * projects/{project_id}
             **/
            // 'beyondwords_title_speaking_rate', { playbackRate }
            // 'beyondwords_body_speaking_rate', ??? { playbackRate }

            /**
             * Player settings synced with REST API
             * projects/{project_id}/player_settings
             **/
            'beyondwords_player_style'             => ['entity' => 'player', 'path' => 'player_style'],
            'beyondwords_player_theme'             => ['entity' => 'player', 'path' => 'theme'],
            'beyondwords_player_dark_theme'        => ['entity' => 'player', 'path' => 'dark_theme'], // [text_color|background_color|icon_color|highlight_color]
            'beyondwords_player_light_theme'       => ['entity' => 'player', 'path' => 'light_theme'], // [text_color|background_color|icon_color|highlight_color]
            'beyondwords_player_video_theme'       => ['entity' => 'player', 'path' => 'video_theme'], // [text_color|background_color|icon_color]
            'beyondwords_player_call_to_action'    => ['entity' => 'player', 'path' => 'call_to_action'],
            'beyondwords_player_widget_style'      => ['entity' => 'player', 'path' => 'widget_style'],
            'beyondwords_player_widget_position'   => ['entity' => 'player', 'path' => 'widget_position'],
            'beyondwords_player_skip_button_style' => ['entity' => 'player', 'path' => 'skip_button_style'],

            /**
             * @todo
             * Player params NOT synced with REST API
             */
            // 'beyondwords_player_ui',               // TOGGLE <script>
            // 'beyondwords_text_highlighting',      // { highlightSections: 'body-none' }
            // 'beyondwords_playback_from_segments', // { clickableSections: body }
        ];

        if (!empty($entity)) {
            $options = array_filter($options, function($option) use ($entity) {
                return $option['entity'] === $entity;
            });
        }

        return $options;
    }
}
