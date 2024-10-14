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

    self::assertNotFalse( has_action('wp', [ $wPForms, 'check_processing_form_submit' ]) );
    self::assertNotFalse( has_action('wp_ajax_wpforms_upload_chunk_init', [ $wPForms, 'remove_cache_busting' ]) );
    self::assertNotFalse( has_action('wp_ajax_nopriv_wpforms_upload_chunk_init', [ $wPForms, 'remove_cache_busting' ]) );
    self::assertNotFalse( has_action('wp_ajax_wpforms_submit', [ $wPForms, 'remove_cache_busting' ]) );
    self::assertNotFalse( has_action('wp_ajax_nopriv_wpforms_submit', [ $wPForms, 'remove_cache_busting' ]) );
    self::assertNotFalse( has_action('wpforms_process_entry_saved', [ $wPForms, 'entry_saved' ]) );
    self::assertNotFalse( has_action('wpforms_pre_delete_entries', [ $wPForms, 'pre_delete_entries' ]) );
    self::assertNotFalse( has_action('wpforms_pro_admin_entries_page_empty_trash_before', [ $wPForms, 'before_empty_trash' ]) );
    self::assertNotFalse( has_action('wpforms_pre_delete_entry_fields', [ $wPForms, 'pre_delete_entry_fields' ]) );
    self::assertNotFalse( has_action('wpforms_builder_save_form', [ $wPForms, 'builder_save_form' ]) );
    self::assertNotFalse( has_action('admin_init', [ $wPForms, 'show_message' ]) );

    self::assertNotFalse( has_filter('wpforms_process_after_filter', [ $wPForms, 'upload_complete' ]) );
    self::assertNotFalse( has_filter('wpforms_entry_email_data', [ $wPForms, 'entry_email_data' ]) );
    self::assertNotFalse( has_filter('sm:sync::syncArgs', [ $wPForms, 'sync_args' ]) );
    self::assertNotFalse( has_filter('sm:sync::nonMediaFiles', [ $wPForms, 'sync_non_media_files' ]) );
    }

  public function testShouldCountHooks() {
    $wPForms = new WPForms();

    Functions\expect('add_action')->times(11);
    Functions\expect('add_filter')->times(4);

    $wPForms->module_init([]);
  }

  public function testShouldRemoveFilter() {
    $wPForms = new WPForms();

    add_filter('sanitize_file_name', [ 'wpCloud\StatelessMedia\Utility', 'randomize_filename' ]);

    $_GET['page'] = 'wpforms-builder';

    $wPForms->module_init([]);

    self::assertFalse( has_filter('sanitize_file_name', [ 'wpCloud\StatelessMedia\Utility', 'randomize_filename' ]) );
  }

  public function testShouldKeepFilter() {
    $wPForms = new WPForms();

    add_filter('sanitize_file_name', [ 'wpCloud\StatelessMedia\Utility', 'randomize_filename' ]);

    unset( $_GET['page'] );

    $wPForms->module_init([]);

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

  public function testShouldNotMoveUploadedFile() {
    $wPForms = new WPForms();

    self::assertEquals(
      0,
      count( $wPForms->upload_complete([], [], []) )
    );
  }

  public function testShouldNotUpdateEmailData() {
    $wPForms = new WPForms();

    self::assertEquals(
      0,
      count( $wPForms->entry_email_data([], [], []) )
    );
  }
}

function sanitize_text_field($value) {
  return $value;
}