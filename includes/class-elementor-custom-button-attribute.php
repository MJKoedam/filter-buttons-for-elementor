<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Elementor_Custom_Button_Attribute {

    public function __construct() {
        add_action( 'elementor/element/button/section_button/before_section_end', [ $this, 'add_custom_attribute_control' ], 10, 2 );
        add_action( 'elementor/frontend/element/before_render', [ $this, 'render_custom_attribute' ], 10, 1 );
    }

    public function add_custom_attribute_control( $element, $args ) {
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
                'label' => __( 'Activate Filter', 'button-filters-for-elementor' ),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __( 'Yes', 'button-filters-for-elementor' ),
                'label_off' => __( 'No', 'button-filters-for-elementor' ),
                'return_value' => 'yes',
                'default' => 'no',
                'description' => __( 'Activate the custom filter for the button', 'button-filters-for-elementor' ),
            ]
        );

        $element->add_control(
            'custom_post_type',
            [
                'label' => __( 'Custom Post Type', 'button-filters-for-elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_custom_post_types(),
                'description' => __( 'Select a custom post type', 'button-filters-for-elementor' ),
                'condition' => [
                    'custom_filter' => 'yes',
                ],
            ]
        );

        $element->add_control(
            'custom_acf_field',
            [
                'label' => __( 'Custom ACF Field', 'button-filters-for-elementor' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $this->get_all_acf_fields(),
                'description' => __( 'Select a custom ACF field', 'button-filters-for-elementor' ),
                'condition' => [
                    'custom_post_type!' => '',
                    'custom_filter' => 'yes',
                ],
            ]
        );
    }

    private function get_custom_post_types() {
        $post_types = get_post_types( [ 'public' => true ], 'objects' );
        $options = [];
        $extraoptions = [];
    
        foreach ( $post_types as $post_type ) {
            if ( $post_type->name !== 'attachment' ) {
                $options[ $post_type->name ] = $post_type->label;
                $extraoptions[] = [
                    'id' => $post_type->name, // Assuming 'name' is the same as 'id' for post types
                    'label' => $post_type->label // Adding label for completeness
                ];
            }
        }
        
        echo '<script>';
        foreach ($extraoptions as $option) {
            $id = $option['id'];
            $label = $option['label'];
            echo 'console.log("Post Type ID:", "' . $id . '", "Label:", "' . $label . '");';
        }
        echo '</script>';
    
        return $options;
    }

    private function get_all_acf_fields() {
        $fields = [];
    
        if ( function_exists( 'acf_get_field_groups' ) && function_exists( 'acf_get_fields' ) ) {
            $field_groups = acf_get_field_groups();
    

    
            foreach ( $field_groups as $field_group ) {
                $group_fields = acf_get_fields( $field_group['key'] );
    
                if ( ! empty( $group_fields ) ) {
                    foreach ( $group_fields as $field ) {
                        $fields[ $field['key'] ] = $field['label'];
                    }
                }
            }
        }

        echo '<script>';
        foreach ($field_groups as $field_group) {
            $value = $field_group['location'][0][0]['value'];
            echo 'console.log("ACF Field Group Value:", "' . $value . '");';
        }
        echo '</script>';
    
        //return $fields;
        return $field_groups;
    }

    public function render_custom_attribute( $element ) {
        if ( 'button' === $element->get_name() ) {
            $settings = $element->get_settings_for_display();
            
            if ( 'yes' === $settings['custom_filter'] && ! empty( $settings['custom_post_type'] ) && ! empty( $settings['custom_acf_field'] ) ) {
                global $post;
                $field_value = get_field( $settings['custom_acf_field'], $post->ID );
                
                if ( $field_value ) {
                    $element->add_render_attribute( '_wrapper', 'data-custom-attribute', $field_value );
                }
            }
        }
    }
}

new Elementor_Custom_Button_Attribute();
?>
