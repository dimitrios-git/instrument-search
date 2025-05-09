<?php
defined('ABSPATH') || exit;

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
            $instance = wp_parse_args((array) $instance, $defaults); ?>
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
        <?php }

        public function update($new_instance, $old_instance) {
            return array(
                'show_category_filter' => !empty($new_instance['show_category_filter']),
                'filter_style' => sanitize_text_field($new_instance['filter_style']),
            );
        }
    }
}

if (!function_exists('register_instrument_search_widget')) {
    function register_instrument_search_widget() {
        register_widget('Instrument_Search_Widget');
    }
    add_action('widgets_init', 'register_instrument_search_widget');
}

