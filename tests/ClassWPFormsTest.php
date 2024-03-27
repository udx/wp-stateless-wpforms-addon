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
  // Adds Mockery expectations to the PHPUnit assertions count.
  use MockeryPHPUnitIntegration;

  public function setUp(): void {
		parent::setUp();
		Monkey\setUp();
  }
	
  public function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

  public function testShouldInitHooks() {
    $wPForms = new WPForms();

    // Actions\expectDone('sm:sync::register_dir')->once();

    $wPForms->module_init([]);

    self::assertNotFalse( has_action('current_screen', [ $wPForms, 'disable_cache_busting' ]) );
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
}
