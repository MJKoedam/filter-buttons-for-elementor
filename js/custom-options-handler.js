/**
 * Custom Options Handler for Elementor Button Widget
 *
 * This script dynamically populates ACF (Advanced Custom Fields) options based on the selected
 * custom post type within Elementor's button widget settings(in the editor). It listens for changes in the
 * custom post type select field, triggers an AJAX request to fetch ACF field groups and their
 * respective fields, and updates the custom ACF field select options accordingly.
 **/
jQuery(document).ready(function($) {
    // Event listener for change in Custom Post Type select
    $(document).on('change', '.elementor-control-custom_post_type select', function() {
        // Get the selected custom post type value
        var selectedPostType = $(this).val();
        console.log('Selected Custom Post Type:', selectedPostType);

        // Trigger AJAX to fetch ACF fields based on selected post type
        if (selectedPostType) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_acf_fields',
                    post_type: selectedPostType,
                },
                success: function(response) {
                    // Check if the AJAX request was successful and contains groups data
                    if (response.success && response.data.groups) {
                        console.log('ACF Field Groups:', response.data.groups);

                        // Extract ACF fields from response and prepare options array
                        var acfFields = response.data.groups.flatMap(group =>
                            group.fields.map(field => ({ id: field.key, text: field.label }))
                        );

                        // Update custom_acf_field select options
                        var selectElement = $('.elementor-control-custom_acf_field select');
                        selectElement.empty(); // Clear existing options

                        // Append new options to custom_acf_field select
                        acfFields.forEach(option => {
                            selectElement.append(new Option(option.text, option.id));
                        });

                        // Trigger change event to reflect changes in Elementor editor
                        selectElement.trigger('change');
                    } else {
                        console.error('Error fetching ACF fields:', response);
                    }
                },
                error: function(error) {
                    console.error('AJAX Error:', error);
                }
            });
        }
    });
});
