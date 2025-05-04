/**
 * The admin-specific JavaScript for the plugin.
 *
 * @since      1.0.0
 * @package    OpenAI_Post_Generator
 * @subpackage OpenAI_Post_Generator/admin/js
 */

(function($) {
    'use strict';

    // Initialize the plugin
    $(document).ready(function() {
        // Handle tab switching
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            $('.tab-content').hide();
            $($(this).attr('href')).show();
        });

        // Handle post generation
        $('#generate_post').on('click', function() {
            var topic = $('#post_topic').val();
            var category = $('#post_category').val();
            var status = $('#post_status').val();

            if (!topic) {
                alert('Please enter a topic');
                return;
            }

            $('#generation_status').show();
            $('#generation_result').hide();

            $.ajax({
                url: openai_post_generator_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'generate_post',
                    nonce: openai_post_generator_ajax.nonce,
                    topic: topic,
                    category: category,
                    status: status
                },
                success: function(response) {
                    $('#generation_status').hide();
                    if (response.success) {
                        $('#generation_result')
                            .removeClass('error')
                            .addClass('success')
                            .html('<p>Post generated successfully! <a href="' + response.data.edit_url + '">Edit Post</a></p>')
                            .show();
                    } else {
                        $('#generation_result')
                            .removeClass('success')
                            .addClass('error')
                            .html('<p>Error: ' + response.data + '</p>')
                            .show();
                    }
                },
                error: function() {
                    $('#generation_status').hide();
                    $('#generation_result')
                        .removeClass('success')
                        .addClass('error')
                        .html('<p>Error generating post. Please try again.</p>')
                        .show();
                }
            });
        });
    });
})(jQuery); 