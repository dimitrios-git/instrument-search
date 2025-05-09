<?php
defined('ABSPATH') || exit;

function instrument_search_render($attributes = array()) {
    $defaults = array(
        'showCategoryFilter' => true,
        'filterStyle' => 'dropdown'
    );
    $attributes = wp_parse_args($attributes, $defaults);

    $all_terms = get_terms(array(
        'taxonomy' => 'instrument-category',
        'hide_empty' => false,
        'orderby' => 'parent name',
    ));

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
        INSTRUMENT_SEARCH_PLUGIN_URL . 'assets/js/frontend.js',
        array(),
        filemtime(INSTRUMENT_SEARCH_PLUGIN_DIR . 'assets/js/frontend.js'),
        true
    );

    wp_enqueue_style(
        'instrument-search-styles',
        INSTRUMENT_SEARCH_PLUGIN_URL . 'assets/css/instrument-search.css'
    );

    return ob_get_clean();
}

