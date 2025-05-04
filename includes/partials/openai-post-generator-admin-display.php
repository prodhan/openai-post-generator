<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 * @package    OpenAI_Post_Generator
 * @subpackage OpenAI_Post_Generator/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="nav-tab-wrapper">
        <a href="#settings" class="nav-tab nav-tab-active">Settings</a>
        <a href="#generate" class="nav-tab">Generate Post</a>
    </div>

    <div id="settings" class="tab-content">
        <form method="post" action="options.php">
            <?php
            settings_fields('openai_post_generator_options');
            do_settings_sections('openai-post-generator');
            submit_button();
            ?>
        </form>
    </div>

    <div id="generate" class="tab-content" style="display: none;">
        <h2>Generate New Post</h2>
        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="post_topic">Post Topic</label>
                </th>
                <td>
                    <input type="text" id="post_topic" name="post_topic" class="regular-text" />
                    <p class="description">Enter the topic for your post</p>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="post_category">Category</label>
                </th>
                <td>
                    <?php
                    wp_dropdown_categories(array(
                        'show_option_none' => 'Select Category',
                        'hide_empty' => 0,
                        'name' => 'post_category',
                        'id' => 'post_category',
                        'class' => 'regular-text'
                    ));
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="post_status">Post Status</label>
                </th>
                <td>
                    <select id="post_status" name="post_status" class="regular-text">
                        <option value="draft">Draft</option>
                        <option value="publish">Publish</option>
                    </select>
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="button" id="generate_post" class="button button-primary">Generate Post</button>
        </p>
        <div id="generation_status" style="display: none;">
            <div class="spinner is-active" style="float: none;"></div>
            <p>Generating post...</p>
        </div>
        <div id="generation_result" style="display: none;"></div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.tab-content').hide();
        $($(this).attr('href')).show();
    });

    // Post generation
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
                $('#generation_result').html(response).show();
            },
            error: function() {
                $('#generation_status').hide();
                $('#generation_result').html('Error generating post. Please try again.').show();
            }
        });
    });
});
</script> 