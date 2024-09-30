<?php
namespace SLCA\WPForms;

use wpCloud\StatelessMedia\Compatibility;
use wpCloud\StatelessMedia\Utility;
use wpCloud\StatelessMedia\Helper;

/**
 * Class WPForms
 */
class WPForms extends Compatibility {
  const STORAGE_PATH = 'wpforms/';
  const TMP_PATH = self::STORAGE_PATH . 'tmp/';
  const UPLOAD_FIELD_TYPE = 'file-upload';
  const MESSAGE_KEY = 'stateless-wpforms-modern';
  const DISMISSED_MESSAGE_KEY = 'dismissed_notice_' . self::MESSAGE_KEY;

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
    add_action( 'wp_ajax_wpforms_upload_chunk_init', [$this, 'remove_cache_busting'], 5);
    add_action( 'wp_ajax_nopriv_wpforms_upload_chunk_init', [$this, 'remove_cache_busting'], 5);
    add_action( 'wp_ajax_wpforms_submit', [$this, 'remove_cache_busting'], 5);
    add_action( 'wp_ajax_nopriv_wpforms_submit', [$this, 'remove_cache_busting'], 5);

    add_filter( 'sm:sync::syncArgs', [$this, 'sync_args'], 10, 4);
  }

  /**
   * Get the position of 'so-css/' dir in the filename.
   * 
   * @param $name
   * @return bool
   */
  protected function storage_position($name) {
    return strpos($name, self::STORAGE_PATH);
  }

  /**
   * Skip cache busting for WPForms files.
   */
  public function remove_cache_busting() {
    remove_filter('sanitize_file_name', ['wpCloud\StatelessMedia\Utility', 'randomize_filename'], 10);
  }

  /**
   * Disable cache busting for WPForms Builder page
   * 
   * @param $screen
   */
  public function disable_cache_busting($screen) {
    if ( $screen->id === 'wpforms_page_wpforms-builder' ) {
      $this->remove_cache_busting();
    }
  }

  /**
   * Update args when uploading/syncing file to GCS.
   * 
   * @param array $args
   * @param string $name
   * @param string $file
   * @param bool $force
   * 
   * @return array
   */
  public function sync_args($args, $name, $file, $force) {
    if ( $this->storage_position($name) !== 0 ) {
      return $args;
    }

    if ( ud_get_stateless_media()->is_mode('stateless') ) {
      $args['name_with_root'] = false;
    }

    $args['source'] = 'WPForms';
    $args['source_version'] = defined('WPFORMS_VERSION') ? WPFORMS_VERSION : '';

    return $args;
  }
}
