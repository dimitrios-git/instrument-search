<?php
defined('ABSPATH') || exit;

function instrument_search_block_init() {
    wp_register_script(
        'instrument-search-block-editor',
        INSTRUMENT_SEARCH_PLUGIN_URL . 'assets/js/block.js',
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-i18n'),
        filemtime(INSTRUMENT_SEARCH_PLUGIN_DIR . 'assets/js/block.js')
    );

   register_block_type('instrument/search-block', array(
        'editor_script' => 'instrument-search-block-editor',
        'render_callback' => 'instrument_search_render',
        'attributes' => array(
            'showCategoryFilter' => array(
                'type' => 'boolean',
                'default' => true
            ),
            'filterStyle' => array(
                'type' => 'string',
                'default' => 'dropdown'
            )
        )
    ));
}
add_action('init', 'instrument_search_block_init');

