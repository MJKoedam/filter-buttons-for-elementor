<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/** 
 * Elementor Custom Button Attribute
 *
 * This class integrates custom attributes and dynamically populates ACF (Advanced Custom Fields)
 * options based on selected custom post types within Elementor's button widget settings.
 **/
class Elementor_Custom_Button_Attribute {

    /**
     * Constructor. Initializes actions.
     */
    public function __construct() {
        add_action('wp_ajax_get_acf_fields', [$this, 'get_acf_fields_callback']);
        add_action('wp_ajax_nopriv_get_acf_fields', [$this, 'get_acf_fields_callback']);
        add_action('elementor/element/button/section_button/before_section_end', [$this, 'add_custom_attribute_control'], 10, 2);
        add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    /**
     * AJAX callback to fetch ACF fields based on selected post type.
     */
    public function get_acf_fields_callback() {
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : '';

        if (empty($post_type)) {
            wp_send_json_error('No post type provided.');
        }

        $field_groups = acf_get_field_groups();
        $groups = [];

        foreach ($field_groups as $field_group) {
            foreach ($field_group['location'] as $location_rule) {
                foreach ($location_rule as $rule) {
                    if (isset($rule['value']) && $rule['value'] === $post_type) {
                        $group_fields = acf_get_fields($field_group['key']);
                        $fields = [];

                        foreach ($group_fields as $field) {
                            $fields[] = [
                                'key' => $field['key'],
                                'label' => $field['label'],
                                // Add more fields as needed
                            ];
                        }

                        $groups[] = [
                            'title' => $field_group['title'],
                            'fields' => $fields,
                        ];

                        wp_send_json_success(['groups' => $groups]);
                    }
                }
            }
        }

        wp_send_json_error('No matching ACF field groups found.');
    }

    /**
     * Adds custom attribute controls to Elementor button widget.
     *
     * @param Elementor\Widget_Base $element The Elementor button widget instance.
     * @param array $args Additional arguments.
     */
    public function add_custom_attribute_control($element, $args) {
        $element->add_control(
            'custom_attribute_divider',
            [
                'type' => \Elementor\Controls_Manager::DIVIDER,
                'style' => 'thick',
            ]
        );

        $element->add_control(
            'custom_filter',
            [
                'label' => __('Activate Filter', 'button-filters-for-elementor'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'button-filters-for-elementor'),
                'label_off' => __('No', 'button-filters-for-elementor'),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => __('Activate the custom filter for the button', 'button-filters-for-elementor'),
            ]
        );

        $element->add_control(
            'custom_post_type',
            [
                'label' => __('Custom Post Type', 'button-filters-for-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_custom_post_types(),
                'description' => __('Select a custom post type', 'button-filters-for-elementor'),
                'condition' => [
                    'custom_filter' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'custom_acf_field',
            [
                'label' => __('Custom ACF Field', 'button-filters-for-elementor'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_all_acf_fields(), // Populated dynamically via JavaScript
                'description' => __('Select a custom ACF field', 'button-filters-for-elementor'),
                'condition' => [
                    'custom_post_type!' => '',
                    'custom_filter' => 'yes',
                ],
            ]
        );
    }

    /**
     * Enqueues scripts and localizes data for custom-options-handler.js.
     */
    public function enqueue_scripts() {
        wp_enqueue_script('custom-options-handler', plugins_url('../js/custom-options-handler.js', __FILE__), ['jquery'], null, true);
        wp_localize_script('custom-options-handler', 'customOptionsData', [
            'ajaxurl' => admin_url('admin-ajax.php'),
        ]);
    }

    /**
     * Retrieves all public custom post types.
     *
     * @return array List of custom post types with names as keys and labels as values.
     */
    private function get_custom_post_types() {
        $post_types = get_post_types(['public' => true], 'objects');
        $options = [];

        foreach ($post_types as $post_type) {
            if ($post_type->name !== 'attachment') {
                $options[$post_type->name] = $post_type->label;
            }
        }

        return $options;
    }

    /**
     * Retrieves all ACF fields.
     *
     * @return array Empty array, as fields are populated dynamically via JavaScript.
     */
    private function get_all_acf_fields() {
        return [];
    }
}

new Elementor_Custom_Button_Attribute();
