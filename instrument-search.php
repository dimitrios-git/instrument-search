<?php
/**
 * Plugin Name: Instrument Search Block & Widget
 * Description: Search instruments with multiple filter styles - works as both block and widget.
 * Version: 1.3
 * Author: Dimitrios Charalampidis
 */

defined( 'ABSPATH' ) || exit;

// =====================
// Shared Rendering Function
// =====================
function instrument_search_render($attributes = array()) {
    $defaults = array(
        'showCategoryFilter' => true,
        'filterStyle' => 'dropdown'
    );
    $attributes = wp_parse_args($attributes, $defaults);

    // Get hierarchical categories
    $all_terms = get_terms(array(
        'taxonomy' => 'instrument-category',
        'hide_empty' => false,
        'orderby' => 'parent name',
    ));

    // Build hierarchical tree
    $build_hierarchy = function($terms, $parent_id = 0) use (&$build_hierarchy) {
        $hierarchy = array();
        foreach ($terms as $term) {
            if ($term->parent == $parent_id) {
                $term->children = $build_hierarchy($terms, $term->term_id);
                $hierarchy[] = $term;
            }
        }
        return $hierarchy;
    };
    $categories = $build_hierarchy($all_terms);

    $instruments = get_posts(array(
        'post_type' => 'instrument',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ));

    ob_start(); ?>
    <div class="instrument-search-block filter-style-<?php echo esc_attr($attributes['filterStyle']); ?>">
        <div class="search-filter-container">
            <input type="text" class="search-input" placeholder="<?php esc_attr_e('Search instruments...', 'instrument-search'); ?>">
            
            <?php if($attributes['showCategoryFilter']) : ?>
            <div class="category-filter-wrapper">
                <?php switch($attributes['filterStyle']) :
                    case 'chips': ?>
                        <div class="category-chips">
                            <button class="category-chip active" data-category="all"><?php esc_html_e('All', 'instrument-search'); ?></button>
                            <?php 
                            $output_chips = function($categories, $depth = 0) use (&$output_chips) {
                                foreach ($categories as $category) {
                                    $indent = str_repeat('· ', $depth);
                                    echo '<button class="category-chip" data-category="' . esc_attr($category->slug) . '" 
                                            style="margin-left: ' . ($depth * 15) . 'px">'
                                        . $indent . esc_html($category->name) 
                                        . '</button>';
                                    if (!empty($category->children)) {
                                        $output_chips($category->children, $depth + 1);
                                    }
                                }
                            };
                            $output_chips($categories);
                            ?>
                        </div>
                        <?php break;
                    
                    case 'checkboxes': ?>
                        <div class="category-filter-checkboxes">
                            <input type="text" class="checkbox-search" placeholder="<?php esc_attr_e('Filter categories...', 'instrument-search'); ?>">
                            <div class="checkbox-container">
                                <?php 
                                $output_checkboxes = function($categories, $depth = 0) use (&$output_checkboxes) {
                                    foreach ($categories as $category) {
                                        echo '<label class="category-checkbox" 
                                                style="margin-left: ' . ($depth * 20) . 'px">';
                                        echo '<input type="checkbox" value="' . esc_attr($category->slug) . '">';
                                        echo '<span>' . esc_html($category->name) . '</span>';
                                        echo '</label>';
                                        if (!empty($category->children)) {
                                            $output_checkboxes($category->children, $depth + 1);
                                        }
                                    }
                                };
                                $output_checkboxes($categories);
                                ?>
                            </div>
                        </div>
                        <?php break;
                    
                    default: ?>
                        <select class="category-filter">
                            <option value="all"><?php esc_html_e('All Categories', 'instrument-search'); ?></option>
                            <?php 
                            $output_options = function($categories, $depth = 0) use (&$output_options) {
                                foreach ($categories as $category) {
                                    $indent = str_repeat('— ', $depth);
                                    echo '<option value="' . esc_attr($category->slug) . '">' 
                                        . $indent . esc_html($category->name) 
                                        . '</option>';
                                    if (!empty($category->children)) {
                                        $output_options($category->children, $depth + 1);
                                    }
                                }
                            };
                            $output_options($categories);
                            ?>
                        </select>
                <?php endswitch; ?>
            </div>
            <?php endif; ?>
        </div>
       
	<div class="instrument-list-container"> 
            <ul class="instrument-list">
                <?php foreach($instruments as $instrument) : 
                    $terms = get_the_terms($instrument->ID, 'instrument-category');
                    $category_slugs = $terms ? wp_list_pluck($terms, 'slug') : array();
                ?>
                <li data-categories="<?php echo esc_attr(implode(' ', $category_slugs)); ?>"
                    data-title="<?php echo esc_attr(get_the_title($instrument)); ?>">
                    <a href="<?php echo esc_url(get_permalink($instrument)); ?>">
                        <?php echo esc_html(get_the_title($instrument)); ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
	</div>
    </div>
    <?php
    
    wp_enqueue_script(
        'instrument-search-frontend',
        plugins_url( 'assets/js/frontend.js', __FILE__ ),
        array(),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/frontend.js'),
        true
    );
    
    wp_enqueue_style(
        'instrument-search-styles',
        plugins_url( 'assets/css/instrument-search.css', __FILE__ )
    );

    return ob_get_clean();
}

// =====================
// Gutenberg Block
// =====================
function instrument_search_block_init() {
    wp_register_script(
        'instrument-search-block-editor',
        plugins_url( 'assets/js/block.js', __FILE__ ),
        array('wp-blocks', 'wp-element', 'wp-components', 'wp-editor', 'wp-i18n'),
        filemtime(plugin_dir_path(__FILE__) . 'assets/js/block.js')
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
add_action( 'init', 'instrument_search_block_init' );

// =====================
// Widget Implementation
// =====================
if (!class_exists('Instrument_Search_Widget')) {
    class Instrument_Search_Widget extends WP_Widget {
        
        public function __construct() {
            parent::__construct(
                'instrument_search_widget',
                __('Instrument Search', 'instrument-search'),
                array('description' => __('Search instruments with category filtering', 'instrument-search'))
            );
        }

        public function widget($args, $instance) {
            echo $args['before_widget'];
            $attributes = array(
                'showCategoryFilter' => !empty($instance['show_category_filter']),
                'filterStyle' => isset($instance['filter_style']) ? $instance['filter_style'] : 'dropdown'
            );
            echo instrument_search_render($attributes);
            echo $args['after_widget'];
        }

        public function form($instance) {
            $defaults = array(
                'show_category_filter' => true,
                'filter_style' => 'dropdown'
            );
            $instance = wp_parse_args((array) $instance, $defaults);
            ?>
            <p>
                <input type="checkbox" 
                    id="<?php echo esc_attr($this->get_field_id('show_category_filter')); ?>" 
                    name="<?php echo esc_attr($this->get_field_name('show_category_filter')); ?>" 
                    <?php checked($instance['show_category_filter'], true); ?>>
                <label for="<?php echo esc_attr($this->get_field_id('show_category_filter')); ?>">
                    <?php esc_html_e('Show Category Filter', 'instrument-search'); ?>
                </label>
            </p>
            
            <p>
                <label for="<?php echo esc_attr($this->get_field_id('filter_style')); ?>">
                    <?php esc_html_e('Filter Style:', 'instrument-search'); ?>
                </label>
                <select class="widefat" 
                        id="<?php echo esc_attr($this->get_field_id('filter_style')); ?>" 
                        name="<?php echo esc_attr($this->get_field_name('filter_style')); ?>">
                    <option value="dropdown" <?php selected($instance['filter_style'], 'dropdown'); ?>>
                        <?php esc_html_e('Dropdown', 'instrument-search'); ?>
                    </option>
                    <option value="chips" <?php selected($instance['filter_style'], 'chips'); ?>>
                        <?php esc_html_e('Chips', 'instrument-search'); ?>
                    </option>
                    <option value="checkboxes" <?php selected($instance['filter_style'], 'checkboxes'); ?>>
                        <?php esc_html_e('Checkboxes', 'instrument-search'); ?>
                    </option>
                </select>
            </p>
            <?php
        }

        public function update($new_instance, $old_instance) {
            $instance = array();
            $instance['show_category_filter'] = !empty($new_instance['show_category_filter']);
            $instance['filter_style'] = sanitize_text_field($new_instance['filter_style']);
            return $instance;
        }
    }
}

if (!function_exists('register_instrument_search_widget')) {
    function register_instrument_search_widget() {
        register_widget('Instrument_Search_Widget');
    }
    add_action('widgets_init', 'register_instrument_search_widget');
}

// =====================
// Translations
// =====================
function instrument_search_load_textdomain() {
    load_plugin_textdomain(
        'instrument-search',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}
add_action('init', 'instrument_search_load_textdomain');

