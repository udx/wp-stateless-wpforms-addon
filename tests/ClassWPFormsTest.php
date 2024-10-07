<?php

namespace SLCA\WPForms;

use PHPUnit\Framework\TestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Brain\Monkey;
use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use wpCloud\StatelessMedia\WPStatelessStub;

/**
 * Class ClassWPFormsTest
 */

class ClassWPFormsTest extends TestCase {
  use MockeryPHPUnitIntegration;

  const TEST_URL = 'https://test.test';
  const TEST_FILE = 'wpforms/image.png';

  public function setUp(): void {
		parent::setUp();
		Monkey\setUp();

    // WP_Stateless mocks
    Functions\when('wp_get_upload_dir')->justReturn( [] );
    Functions\when('ud_get_stateless_media')->justReturn( WPStatelessStub::instance() );
  }
	
  public function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

  public function testShouldInitHooks() {
    $wPForms = new WPForms();

    $wPForms->module_init([]);

    self::assertNotFalse( has_action('current_screen', [ $wPForms, 'disable_cache_busting' ]) );
    self::assertNotFalse( has_action('wp_ajax_wpforms_upload_chunk_init', [ $wPForms, 'remove_cache_busting' ]) );
    self::assertNotFalse( has_action('wp_ajax_nopriv_wpforms_upload_chunk_init', [ $wPForms, 'remove_cache_busting' ]) );
    self::assertNotFalse( has_action('wp_ajax_wpforms_submit', [ $wPForms, 'remove_cache_busting' ]) );
    self::assertNotFalse( has_action('wp_ajax_nopriv_wpforms_submit', [ $wPForms, 'remove_cache_busting' ]) );

    self::assertNotFalse( has_filter('sm:sync::syncArgs', [ $wPForms, 'sync_args' ]) );
  }

  public function testShouldCountHooks() {
    $wPForms = new WPForms();

    Functions\expect('add_action')->times(5);
    Functions\expect('add_filter')->times(1);

    $wPForms->module_init([]);
  }

  public function testShouldRemoveFilter() {
    $wPForms = new WPForms();

    add_filter('sanitize_file_name', [ 'wpCloud\StatelessMedia\Utility', 'randomize_filename' ]);

    $wPForms->disable_cache_busting( (object) ['id' => 'wpforms_page_wpforms-builder'] );

    self::assertFalse( has_filter('sanitize_file_name', [ 'wpCloud\StatelessMedia\Utility', 'randomize_filename' ]) );
  }

  public function testShouldKeepFilter() {
    $wPForms = new WPForms();

    add_filter('sanitize_file_name', [ 'wpCloud\StatelessMedia\Utility', 'randomize_filename' ]);

    $_GET['page'] = 'wpforms-builder';

    $wPForms->disable_cache_busting( (object) ['id' => 'another_admin_screen'] );

    self::assertNotFalse( has_filter('sanitize_file_name', [ 'wpCloud\StatelessMedia\Utility', 'randomize_filename' ]) );
  }

  public function testShouldUpdateArgs() {
    $wPForms = new WPForms();

    $args = $wPForms->sync_args([], self::TEST_FILE, '', false);

    self::assertTrue( isset( $args['source'] ) );
    self::assertTrue( isset( $args['source_version'] ) );
    self::assertEquals( 'WPForms', $args['source'] );
    self::assertFalse( isset( $args['name_with_root'] ) );
  }

  public function testShouldUpdateArgsStateless() {
    $wPForms = new WPForms();

    ud_get_stateless_media()->set('sm.mode', 'stateless');

    $args = $wPForms->sync_args([], self::TEST_FILE, '', false);

    self::assertTrue( isset( $args['source'] ) );
    self::assertTrue( isset( $args['source_version'] ) );
    self::assertEquals( 'WPForms', $args['source'] );
    self::assertTrue( isset( $args['name_with_root'] ) );
  }

  public function testShouldNotUpdateArgs() {
    $wPForms = new WPForms();

    self::assertEquals(
      0,
      count( $wPForms->sync_args([], self::TEST_URL, '', false) )
    );
  }
}
