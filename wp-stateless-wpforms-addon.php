<?php

/**
 * Plugin Name: WP-Stateless - WPForms Addon
 * Plugin URI: https://stateless.udx.io/addons/wpforms/
 * Description: Provides compatibility between the WPForms and the WP-Stateless plugins.
 * Author: UDX
 * Version: 0.0.2
 * Text Domain: wp-stateless-wpforms-addon
 * Author URI: https://udx.io
 * License: GPLv2 or later
 * 
 * Copyright 2024 UDX (email: info@udx.io)
 */

namespace SLCA\WPForms;

add_action('plugins_loaded', function () {
  if (class_exists('wpCloud\StatelessMedia\Compatibility')) {
    require_once ( dirname( __FILE__ ) . '/vendor/autoload.php' );
    // Load 
    return new WPForms();
  }

  add_filter('plugin_row_meta', function ($plugin_meta, $plugin_file, $_, $__) {
    if ($plugin_file !== join(DIRECTORY_SEPARATOR, [basename(__DIR__), basename(__FILE__)])) return $plugin_meta;
    $plugin_meta[] = sprintf('<span style="color:red;">%s</span>', __('This plugin requires WP-Stateless plugin version 3.4.0 or greater to be installed and active.', 'wp-stateless-wpforms-addon'));
    return $plugin_meta;
  }, 10, 4);
});
