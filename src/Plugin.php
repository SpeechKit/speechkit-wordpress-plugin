<?php

declare(strict_types=1);

namespace Beyondwords\Wordpress;

use Beyondwords\Wordpress\Compatibility\Elementor\Elementor;
use Beyondwords\Wordpress\Compatibility\WPGraphQL\WPGraphQL;
use Beyondwords\Wordpress\Core\ApiClient;
use Beyondwords\Wordpress\Core\Core;
use Beyondwords\Wordpress\Core\Player\LegacyPlayer;
use Beyondwords\Wordpress\Core\Player\Player;
use Beyondwords\Wordpress\Core\Updater;
use Beyondwords\Wordpress\Component\Post\AddPlayer\AddPlayer;
use Beyondwords\Wordpress\Component\Post\BlockAttributes\BlockAttributes;
use Beyondwords\Wordpress\Component\Post\DisplayPlayer\DisplayPlayer;
use Beyondwords\Wordpress\Component\Post\ErrorNotice\ErrorNotice;
use Beyondwords\Wordpress\Component\Post\GenerateAudio\GenerateAudio;
use Beyondwords\Wordpress\Component\Post\Metabox\Metabox;
use Beyondwords\Wordpress\Component\Post\Panel\Inspect\Inspect;
use Beyondwords\Wordpress\Component\Post\PlayerStyle\PlayerStyle;
use Beyondwords\Wordpress\Component\Post\SelectVoice\SelectVoice;
use Beyondwords\Wordpress\Component\Posts\Column\Column;
use Beyondwords\Wordpress\Component\Posts\BulkEdit\BulkEdit;
use Beyondwords\Wordpress\Component\Posts\BulkEdit\Notices as BulkEditNotices;
use Beyondwords\Wordpress\Component\Settings\Settings;
use Beyondwords\Wordpress\Component\Settings\SettingsUtils;
use Beyondwords\Wordpress\Component\SiteHealth\SiteHealth;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Plugin
{
    /**
     * Public property required so that we can run bulk edit actions like this:
     * $beyondwords_wordpress_plugin->core->generateAudioForPost($postId);
     *
     * @see \Beyondwords\Wordpress\Component\Posts\BulkEdit\BulkEdit
     */
    public $core;

    /**
     * Public property required so that we can run bulk edit actions like this:
     * $beyondwords_wordpress_plugin->player->getBody;
     *
     * @see \Beyondwords\Wordpress\Component\Post\PostContentUtils
     */
    public $player;

    /**
     * The API client - this enables various components to access the API.
     *
     * @todo Consider switching from dependency injection to singleton or another
     *       pattern so that components can perform API calls without DI.
     */
    public $apiClient;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->apiClient = new ApiClient();

        /**
         * Register custom post types
         */
        add_action( 'init', array( $this, 'init_rsis_publications_post' ) );
        add_action( 'init', array( $this, 'init_taxonomy_pub_types' ) );
    }

    public function init_rsis_publications_post() {
        $labels = array(
            'name'               => __( 'RSIS Publications' ),
            'singular_name'      => __( 'RSIS Publication' ),
            'menu_name'          => __( 'RSIS Publications' ),
            'name_admin_bar'     => __( 'RSIS Publication' ),
            'add_new'            => _x( 'Add New', 'RSIS Publication' ),
            'add_new_item'       => __( 'Add New RSIS Publication' ),
            'new_item'           => __( 'New RSIS Publication' ),
            'edit_item'          => __( 'Edit RSIS Publication' ),
            'view_item'          => __( 'View RSIS Publication' ),
            'all_items'          => __( 'All RSIS Publications' ),
            'search_items'       => __( 'Search RSIS Publication' ),
            'parent_item_colon'  => __( 'Parent RSIS Publications:' ),
            'not_found'          => __( 'No RSIS Publications found.' ),
            'not_found_in_trash' => __( 'No RSIS Publications found in Trash.' ),
        );

        register_post_type('cpt_active', [
            'graphql_single_name' => 'cptActivePost',
            'graphql_plural_name' => 'cptActivePosts',
            'public'              => true,
            'label'               => 'CPT Active',
            'show_in_graphql'     => true,
            'show_in_rest'        => true,
            'supports'            => ['title', 'editor', 'custom-fields'],
        ]);

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'rsis-publication/%entity%' ),
            // 'capability_type'    => 'rsispub',
            // 'capabilities'       => array(
            //     'publish_posts'      => 'publish_rsis_publications',
            //     'edit_posts'         => 'edit_rsis_publications',
            //     'edit_others_posts'  => 'edit_others_rsis_publications',
            //     'read_private_posts' => 'read_private_rsis_publications',
            //     'edit_post'          => 'edit_rsis_publication',
            //     'delete_post'        => 'delete_rsis_publication',
            //     'read_post'          => 'read_rsis_publication',
            // ),
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'revisions','comments', 'custom-fields' ),
            'taxonomies'         => array('entity', 'pub-types', 'region', 'theme'),
            'menu_icon' => 'dashicons-media-document'
        );

        register_post_type( 'rsispub', $args );
    }

    public function init_taxonomy_pub_types() {
        $labels = array(
            'name'              => _x( 'Publication Types', 'taxonomy general name' ),
            'singular_name'     => _x( 'Publication Type', 'taxonomy singular name' ),
            'search_items'      => __( 'Search Publication Type' ),
            'all_items'         => __( 'All Publication Type' ),
            'parent_item'       => __( 'Parent Publication Type' ),
            'parent_item_colon' => __( 'Parent Publication Type:' ),
            'edit_item'         => __( 'Edit Publication Type' ),
            'update_item'       => __( 'Update Publication Type' ),
            'add_new_item'      => __( 'Add New Publication Type' ),
            'new_item_name'     => __( 'New Publication Type Name' ),
            'menu_name'         => __( 'Publication Types' )
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => false,
            'query_var'         => true,
            'rewrite'           => array( 'slug' => 'pub-types' ),
            'capabilities' => array (
                'manage_terms'  => 'manage_publication-type',
                'edit_terms'    => 'edit_publication-type',
                'delete_terms'  => 'delete_publication-type',
                'assign_terms'  => 'assign_publication-type'
            )
        );

        register_taxonomy('pub-types', null, $args);

        register_taxonomy_for_object_type( 'pub-types', 'rsispub' );
    }

    /**
     * Constructor.
     *
     * @since 3.0.0
     * @since 4.5.1 Disable plugin features if we don't have valid API settings.
     */
    public function init()
    {
        // Run plugin update checks before anything else
        (new Updater())->run();

        // Third-party plugin/theme compatibility
        (new Elementor())->init();
        (new WPGraphQL())->init();

        // Core
        $this->core = new Core($this->apiClient);
        $this->core->init();

        // Site health
        (new SiteHealth())->init();

        // Player
        if (SettingsUtils::useLegacyPlayer()) {
            (new LegacyPlayer())->init();
        } else {
            (new Player())->init();
        }

        // Settings
        (new Settings($this->apiClient))->init();

        /**
         * To prevent browser JS errors we skip adding admin UI components until
         * we have a valid REST API connection.
         */
        if (SettingsUtils::hasApiSettings()) {
            // Posts screen
            (new BulkEdit())->init();
            (new BulkEditNotices())->init();
            (new Column())->init();

            // Post screen
            (new AddPlayer())->init();
            (new BlockAttributes())->init();
            (new ErrorNotice())->init();
            (new Inspect())->init();

            // Post screen metabox
            (new GenerateAudio())->init();
            (new DisplayPlayer())->init();
            (new SelectVoice($this->apiClient))->init();
            (new PlayerStyle())->init();
            (new Metabox($this->apiClient))->init();
        }
    }
}
