<?php
/**
 * Plugin Name: Instrument Search Block & Widget
 * Description: Search instruments with multiple filter styles - works as both block and widget.
 * Version: 1.3
 * Author: Dimitrios Charalampidis
 */

defined( 'ABSPATH' ) || exit;

define('INSTRUMENT_SEARCH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('INSTRUMENT_SEARCH_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once plugin_dir_path(__FILE__) . 'includes/render.php';
require_once plugin_dir_path(__FILE__) . 'includes/block.php';
require_once plugin_dir_path(__FILE__) . 'includes/widget.php';

function instrument_search_load_textdomain() {
    load_plugin_textdomain(
        'instrument-search',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}
add_action('init', 'instrument_search_load_textdomain');

