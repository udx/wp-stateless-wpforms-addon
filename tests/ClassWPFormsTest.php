<?php

namespace WPSL\WPForms;

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

  public function testShouldKeepFilter() {
    $wPForms = new WPForms();

    add_filter('sanitize_file_name', [ 'wpCloud\StatelessMedia\Utility', 'randomize_filename' ]);

    $wPForms->module_init([]);

    self::assertNotFalse( has_filter('sanitize_file_name', [ 'wpCloud\StatelessMedia\Utility', 'randomize_filename' ]) );
  }

  public function testShouldRemoveFilter() {
    $wPForms = new WPForms();

    add_filter('sanitize_file_name', [ 'wpCloud\StatelessMedia\Utility', 'randomize_filename' ]);

    $_GET['page'] = 'wpforms-builder';

    $wPForms->module_init([]);

    self::assertFalse( has_filter('sanitize_file_name', [ 'wpCloud\StatelessMedia\Utility', 'randomize_filename' ]) );
  }
}
