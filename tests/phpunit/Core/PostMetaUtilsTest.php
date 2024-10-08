<?php

declare(strict_types=1);

use Beyondwords\Wordpress\Component\Post\PostMetaUtils;

class PostMetaUtilsTest extends WP_UnitTestCase
{
    /**
     * @var \WpunitTester
     */
    protected $tester;

    /**
     * Sample data from the custom field `speechkit_info`.
     *
     * This was exported from a test site running plugin v2.7.10.
     *
     * @var string
     */
    private $sampleSpeechkitInfo = 'a:16:{s:2:"id";s:2:"49";s:10:"podcast_id";i:9969567;s:3:"url";s:53:"https://speechkit.pressingspace.com/post-from-2-7-10/";s:5:"title";s:16:"Post from 2.7.10";s:6:"author";s:13:"pressingspace";s:7:"summary";s:0:"";s:5:"image";s:1:"f";s:12:"published_at";s:24:"2021-11-17T17:44:58.000Z";s:5:"state";s:9:"processed";s:9:"share_url";s:25:"https://spkt.io/a/9969567";s:13:"share_version";s:2:"v2";s:5:"media";a:2:{i:0;a:10:{s:2:"id";i:11542939;s:4:"role";s:4:"body";s:12:"content_type";s:21:"application/x-mpegURL";s:3:"url";s:118:"https://abcdefghabcdef.cloudfront.net/audio/projects/9969/contents/9969567/media/abcdefghabcdefghabcdefghabcdefgh.m3u8";s:12:"download_url";N;s:10:"created_at";s:24:"2021-11-17T17:45:03.211Z";s:10:"updated_at";s:24:"2021-11-17T17:45:03.211Z";s:5:"state";s:9:"processed";s:8:"duration";i:4;s:5:"voice";N;}i:1;a:10:{s:2:"id";i:11542938;s:4:"role";s:4:"body";s:12:"content_type";s:10:"audio/mpeg";s:3:"url";s:126:"https://abcdefghabcdef.cloudfront.net/audio/projects/9969/contents/9969567/media/abcdefghabcdefghabcdefghabcdefgh_compiled.mp3";s:12:"download_url";N;s:10:"created_at";s:24:"2021-11-17T17:45:02.078Z";s:10:"updated_at";s:24:"2021-11-17T17:45:02.078Z";s:5:"state";s:9:"processed";s:8:"duration";i:4;s:5:"voice";N;}}s:11:"player_type";s:14:"EmbeddedPlayer";s:24:"next_content_external_id";N;s:11:"ad_disabled";b:0;s:10:"project_id";i:9969;}';

    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    /**
     * Test if we can get a Project ID from the plugin settings.
     *
     * @test
     * @dataProvider getProjectIdWithPluginSettingProvider
     *
     * @param boolean $expected Expected Project ID
     * @param int     $postId   WordPress Post ID
     */
    public function getProjectIdWithPluginSetting($expected, $postArgs)
    {
        $postId = self::factory()->post->create($postArgs);

        update_option('beyondwords_project_id', BEYONDWORDS_TESTS_PROJECT_ID);

        $this->assertEquals($expected, PostMetaUtils::getProjectId($postId));

        delete_option('beyondwords_project_id');

        wp_delete_post($postId, true);
    }

    /**
     *
     */
    public function getProjectIdWithPluginSettingProvider()
    {
        return [
            'No BeyondWords metadata' => [BEYONDWORDS_TESTS_PROJECT_ID, ['post_title' => 'UtilsTest:getProjectIdWithPluginSettingProvider']],
            // post_meta.beyondwords_project_id
            'beyondwords_project_id is empty'   => [BEYONDWORDS_TESTS_PROJECT_ID, ['post_title' => 'UtilsTest:getProjectIdWithPluginSettingProvider', 'meta_input' => ['beyondwords_project_id' => '']]],
            'beyondwords_project_id is invalid' => [BEYONDWORDS_TESTS_PROJECT_ID, ['post_title' => 'UtilsTest:getProjectIdWithPluginSettingProvider', 'meta_input' => ['beyondwords_project_id' => 'foo']]],
            // post_meta.speechkit_project_id
            'speechkit_project_id is empty'   => [BEYONDWORDS_TESTS_PROJECT_ID, ['post_title' => 'UtilsTest:getProjectIdWithPluginSettingProvider', 'meta_input' => ['speechkit_project_id' => '']]],
            'speechkit_project_id is invalid' => [BEYONDWORDS_TESTS_PROJECT_ID, ['post_title' => 'UtilsTest:getProjectIdWithPluginSettingProvider', 'meta_input' => ['speechkit_project_id' => 'foo']]],
        ];
    }

    /**
     * Test if we can get a Project ID from the post.
     *
     * @test
     * @dataProvider getProjectIdWithoutPluginSettingProvider
     *
     * @param boolean $expected Expected Project ID
     * @param int     $postId   WordPress Post ID
     */
    public function getProjectIdWithoutPluginSetting($expected, $postArgs)
    {
        $postId = self::factory()->post->create($postArgs);

        $this->assertEquals($expected, PostMetaUtils::getProjectId($postId));

        wp_delete_post($postId, true);
    }

    /**
     *
     */
    public function getProjectIdWithoutPluginSettingProvider()
    {
        return [
            'No BeyondWords metadata' => [false, ['post_title' => 'UtilsTest:getProjectIdWithoutPluginSettingProvider']],
            // post_meta.beyondwords_project_id
            'beyondwords_project_id is empty'   => [false, ['post_title' => 'UtilsTest:getProjectIdWithoutPluginSettingProvider', 'meta_input' => ['beyondwords_project_id' => '']]],
            'beyondwords_project_id is invalid' => [false, ['post_title' => 'UtilsTest:getProjectIdWithoutPluginSettingProvider', 'meta_input' => ['beyondwords_project_id' => 'foo']]],
            'beyondwords_project_id = ' . BEYONDWORDS_TESTS_PROJECT_ID      => [BEYONDWORDS_TESTS_PROJECT_ID,  ['post_title' => 'UtilsTest:getProjectIdWithoutPluginSettingProvider', 'meta_input' => ['beyondwords_project_id' => BEYONDWORDS_TESTS_PROJECT_ID]]],
            // post_meta.speechkit_project_id
            'speechkit_project_id is empty'   => [false, ['post_title' => 'UtilsTest:getProjectIdWithoutPluginSettingProvider', 'meta_input' => ['speechkit_project_id' => '']]],
            'speechkit_project_id is invalid' => [false, ['post_title' => 'UtilsTest:getProjectIdWithoutPluginSettingProvider', 'meta_input' => ['speechkit_project_id' => 'foo']]],
            'speechkit_project_id = ' . BEYONDWORDS_TESTS_PROJECT_ID      => [BEYONDWORDS_TESTS_PROJECT_ID,  ['post_title' => 'UtilsTest:getProjectIdWithoutPluginSettingProvider', 'meta_input' => ['speechkit_project_id' => BEYONDWORDS_TESTS_PROJECT_ID]]],
        ];
    }

    /**
     * Test a Post's Project ID remains fixed after the plugin
     * Project ID setting changes.
     *
     * @test
     */
    public function getProjectIdWhenSettingChanges()
    {
        $this->markTestSkipped('Update for real REST API calls');

        $firstPostId = self::factory()->post->create([
            'post_title' => 'UtilsTest:getProjectIdWhenSettingChanges::1',
            'meta_input' => [
                'beyondwords_project_id' => 1234,
            ],
        ]);

        $this->assertEquals(1234, PostMetaUtils::getProjectId($firstPostId));

        update_option('beyondwords_project_id', 5678);

        $secondPostId = self::factory()->post->create([
            'post_title' => 'UtilsTest:getProjectIdWhenSettingChanges::2',
        ]);

        // The first Post should still have the original Project ID
        $this->assertEquals(1234, PostMetaUtils::getProjectId($firstPostId));

        // The second Post should be using the updated plugin setting
        $this->assertEquals(5678, PostMetaUtils::getProjectId($secondPostId));

        delete_option('beyondwords_project_id');

        wp_delete_post($firstPostId, true);
        wp_delete_post($secondPostId, true);
    }

    /**
     * Test if we can get a content ID from all the various places it can be.
     *
     * @test
     * @dataProvider getContentIdProvider
     *
     * @param boolean $expected Expected Content ID
     * @param int     $postArgs WordPress Post args
     */
    public function getContentId($expected, $postArgs)
    {
        $postId = self::factory()->post->create($postArgs);

        $this->assertEquals($expected, PostMetaUtils::getContentId($postId));

        wp_delete_post($postId, true);
    }

    /**
     *
     */
    public function getContentIdProvider()
    {
        return [
            'No BeyondWords metadata' => [false, ['post_title' => 'UtilsTest:getContentIdProvider']],
            // post_meta.beyondwords_content_id
            'beyondwords_content_id is empty'  => ['',         ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['beyondwords_content_id' => '']]],
            'beyondwords_content_id = foo'     => ['foo',      ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['beyondwords_content_id' => 'foo']]],
            'beyondwords_content_id = ' . BEYONDWORDS_TESTS_CONTENT_ID => [BEYONDWORDS_TESTS_CONTENT_ID, ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['beyondwords_content_id' => BEYONDWORDS_TESTS_CONTENT_ID]]],
            // post_meta.beyondwords_podcast_id
            'beyondwords_podcast_id is empty'  => ['',         ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['beyondwords_podcast_id' => '']]],
            'beyondwords_podcast_id = foo'     => ['foo',      ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['beyondwords_podcast_id' => 'foo']]],
            'beyondwords_podcast_id = ' . BEYONDWORDS_TESTS_CONTENT_ID => [BEYONDWORDS_TESTS_CONTENT_ID, ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['beyondwords_podcast_id' => BEYONDWORDS_TESTS_CONTENT_ID]]],
            // post_meta.speechkit_podcast_id
            'speechkit_podcast_id is empty'    => ['',      ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['speechkit_podcast_id' => '']]],
            'speechkit_podcast_id = foo'       => ['foo',   ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['speechkit_podcast_id' => 'foo']]],
            'speechkit_podcast_id = ' . BEYONDWORDS_TESTS_CONTENT_ID => [BEYONDWORDS_TESTS_CONTENT_ID, ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['speechkit_podcast_id' => BEYONDWORDS_TESTS_CONTENT_ID]]],
            // post_meta.speechkit_link
            'speechkit_link is empty'                          => [false,   ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['_speechkit_link' => '']]],
            'speechkit_link = https://spkt.io/a/1234567'       => [1234567, ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['_speechkit_link' => 'https://spkt.io/a/1234567']]],
            'speechkit_link = https://spkt.io/a/1234567/'      => [1234567, ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['_speechkit_link' => 'https://spkt.io/a/1234567/']]],
            'speechkit_link = https://spkt.io/a/1234567?x=456' => [1234567, ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['_speechkit_link' => 'https://spkt.io/a/1234567?x=456']]],
            'speechkit_link = https://example.com/a/1234567'   => [1234567, ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['_speechkit_link' => 'https://example.com/a/1234567']]],
            'speechkit_link = https://spkt.io/e/1234567'       => [1234567, ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['_speechkit_link' => 'https://spkt.io/e/1234567']]],
            'speechkit_link = https://spkt.io/m/1234567'       => [1234567, ['post_title' => 'UtilsTest:getContentIdProvider', 'meta_input' => ['_speechkit_link' => 'https://spkt.io/m/1234567']]],
        ];
    }

    /**
     * Get API response body from post meta field.
     *
     * @test
     * @dataProvider getHttpResponseBodyFromPostMetaProvider
     *
     * @param boolean $expected Expected speechkit_response
     * @param int     $postArgs WordPress Post args
     */
    public function getHttpResponseBodyFromPostMeta($expected, $postArgs)
    {
        update_option('beyondwords_project_id', BEYONDWORDS_TESTS_PROJECT_ID);

        $postId = self::factory()->post->create($postArgs);

        $this->assertSame($expected, PostMetaUtils::getHttpResponseBodyFromPostMeta($postId, 'speechkit_response'));

        wp_delete_post($postId, true);

        delete_option('beyondwords_project_id');
    }

    /**
     *
     */
    public function getHttpResponseBodyFromPostMetaProvider()
    {
        $json = '{"foo":"bar","baz":42}';

        return [
            'Missing'             => ['',    ['post_title' => 'UtilsTest:getHttpResponseBodyFromPostMetaProvider']],
            'Empty string'        => ['',    ['post_title' => 'UtilsTest:getHttpResponseBodyFromPostMetaProvider', 'meta_input' => ['speechkit_response' => '']]],
            'String'              => [$json, ['post_title' => 'UtilsTest:getHttpResponseBodyFromPostMetaProvider', 'meta_input' => ['speechkit_response' => $json]]],
        ];
    }

    /**
     *
     */
    public function exportedDataHelper($path)
    {
        $handle = fopen($path, 'r');

        $output = [];

        // Ignore first line of CSV
        fgetcsv($handle, 0, ',', '"', "\0");

        // Process remaining lines
        while (($data = fgetcsv($handle, 0, ',', '"', "\0")) !== false) {
            // Only test Posts with a state of "Processed"
            if (strtolower($data[11]) == 'processed') {
                $output['spktdotblog ID ' . $data[0]] = $data;
            }
        }

        return $output;
    }

    /**
     * Test if we can get a content ID from all the various places it can be.
     *
     * @test
     * @dataProvider hasGenerateAudioProvider
     *
     * @param boolean $expected Expected method return value.
     * @param array   $postArgs WordPress post args.
     */
    public function hasGenerateAudio($expected, $postArgs)
    {
        $postId = self::factory()->post->create($postArgs);

        $this->assertEquals($expected, PostMetaUtils::hasGenerateAudio($postId));

        wp_delete_post($postId, true);
    }

    /**
     *
     */
    public function hasGenerateAudioProvider()
    {
        return [
            'No BeyondWords metadata'             => [false, []],
            'beyondwords_generate_audio is ""'    => [false, ['post_title' => 'UtilsTest:hasGenerateAudio', 'meta_input' => ['beyondwords_generate_audio' => '']]],
            'beyondwords_generate_audio is "0"'   => [false, ['post_title' => 'UtilsTest:hasGenerateAudio', 'meta_input' => ['beyondwords_generate_audio' => '0']]],
            'beyondwords_generate_audio is "-1"'  => [false, ['post_title' => 'UtilsTest:hasGenerateAudio', 'meta_input' => ['beyondwords_generate_audio' => '-1']]],
            'beyondwords_generate_audio is "1"'   => [true,  ['post_title' => 'UtilsTest:hasGenerateAudio', 'meta_input' => ['beyondwords_generate_audio' => '1']]],
            'speechkit_generate_audio is ""'      => [false, ['post_title' => 'UtilsTest:hasGenerateAudio', 'meta_input' => ['speechkit_generate_audio' => '']]],
            'speechkit_generate_audio is "0"'     => [false, ['post_title' => 'UtilsTest:hasGenerateAudio', 'meta_input' => ['speechkit_generate_audio' => '0']]],
            'speechkit_generate_audio is "-1"'    => [false, ['post_title' => 'UtilsTest:hasGenerateAudio', 'meta_input' => ['speechkit_generate_audio' => '-1']]],
            'speechkit_generate_audio is "1"'     => [true,  ['post_title' => 'UtilsTest:hasGenerateAudio', 'meta_input' => ['speechkit_generate_audio' => '1']]],
        ];
    }
}
