<?php
namespace SLCA\WPForms;

use wpCloud\StatelessMedia\Compatibility;

/**
 * Class WPForms
 */
class WPForms extends Compatibility {
  protected $id = 'wpforms';
  protected $title = 'WPForms';
  protected $constant = 'WP_STATELESS_COMPATIBILITY_WPFORMS';
  protected $description = 'Ensures compatibility with WPForms.';
  protected $plugin_file = ['wpforms-lite/wpforms.php', 'wpforms/wpforms.php'];

  /** 
   * @param $sm
   */
  public function module_init($sm) {
    add_action( 'current_screen', [$this, 'disable_cache_busting'], 20);
    add_filter( 'stateless_skip_cache_busting', array( $this, 'skip_cache_busting' ), 10, 2 );
  }

  /**
   * Disable cache busting for WPForms Builder page
   * 
   * @param $screen
   */
  public function disable_cache_busting($screen) {
    if ( $screen->id === 'wpforms_page_wpforms-builder' ) {
      remove_filter('sanitize_file_name', array("wpCloud\StatelessMedia\Utility", 'randomize_filename'), 10);
    }
  }

  /**
   * WPForms uses their own cache-busting approach so we need to skip ours
   * 
   * @param $null
   * @param $filename
   * @return bool | string
   */
  public function skip_cache_busting( $null, $filename ) {
    $backtrace = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );

    foreach( $backtrace as $trace ) {
      if ( isset( $trace['class'] ) && in_array( $trace['class'], ['WPForms_Field_File_Upload', 'WPForms_Process'] ) ) {
        return $filename;
      }
    }

    return $null;
  }
}
