<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    OpenAI_Post_Generator
 * @subpackage OpenAI_Post_Generator/includes
 */
class OpenAI_Post_Generator_Settings {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Add AJAX handler for post generation
        add_action('wp_ajax_generate_post', array($this, 'generate_post_ajax_handler'));
        
        // Add custom cron schedules
        add_filter('cron_schedules', array($this, 'add_custom_cron_intervals'));
    }
    
    /**
     * Register custom cron intervals
     *
     * @since    1.0.0
     */
    public function add_custom_cron_intervals($schedules) {
        $schedules['every_5_minutes'] = array(
            'interval' => 5 * 60,
            'display'  => __('Every 5 Minutes', 'openai-post-generator')
        );
        $schedules['every_10_minutes'] = array(
            'interval' => 10 * 60,
            'display'  => __('Every 10 Minutes', 'openai-post-generator')
        );
        $schedules['every_30_minutes'] = array(
            'interval' => 30 * 60,
            'display'  => __('Every 30 Minutes', 'openai-post-generator')
        );
        $schedules['every_60_minutes'] = array(
            'interval' => 60 * 60,
            'display'  => __('Every 60 Minutes', 'openai-post-generator')
        );
        return $schedules;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . '../admin/css/openai-post-generator-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . '../admin/js/openai-post-generator-admin.js', array('jquery'), $this->version, false);
        wp_localize_script($this->plugin_name, 'openai_post_generator_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('openai_post_generator_nonce')
        ));
    }

    /**
     * Add menu items to the admin menu.
     *
     * @since    1.0.0
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            'Post Generator',
            'Post Generator',
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page'),
            'dashicons-welcome-write-blog',
            26
        );
        
        // Add a submenu for testing the scheduled post generation
        add_submenu_page(
            $this->plugin_name,
            'Debug',
            'Debug',
            'manage_options',
            $this->plugin_name . '-debug',
            array($this, 'display_debug_page')
        );
    }

    /**
     * Register the settings for the plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        register_setting(
            'openai_post_generator_options',
            'openai_post_generator_options',
            array($this, 'validate')
        );

        add_settings_section(
            'openai_post_generator_main',
            'OpenAI API Settings',
            array($this, 'print_section_info'),
            'openai-post-generator'
        );

        add_settings_field(
            'api_key',
            'API Key',
            array($this, 'api_key_callback'),
            'openai-post-generator',
            'openai_post_generator_main'
        );

        add_settings_field(
            'model',
            'Model',
            array($this, 'model_callback'),
            'openai-post-generator',
            'openai_post_generator_main'
        );

        add_settings_field(
            'max_tokens',
            'Max Tokens',
            array($this, 'max_tokens_callback'),
            'openai-post-generator',
            'openai_post_generator_main'
        );

        add_settings_field(
            'prompt',
            'Default Prompt',
            array($this, 'prompt_callback'),
            'openai-post-generator',
            'openai_post_generator_main'
        );

        // Scheduled post title template
        add_settings_field(
            'scheduled_title_template',
            'Scheduled Post Title Template',
            array($this, 'scheduled_title_template_callback'),
            'openai-post-generator',
            'openai_post_generator_main'
        );

        // Add to register_settings() method, after the scheduled_title_template field
        add_settings_field(
            'scheduled_default_category',
            'Default Category for Scheduled Posts',
            array($this, 'scheduled_default_category_callback'),
            'openai-post-generator',
            'openai_post_generator_schedule'
        );
        
        add_settings_field(
            'scheduled_default_author',
            'Default Author for Scheduled Posts',
            array($this, 'scheduled_default_author_callback'),
            'openai-post-generator',
            'openai_post_generator_schedule'
        );

        // Add to register_settings() method, after the other scheduled post settings
        add_settings_field(
            'scheduled_default_status',
            'Default Status for Scheduled Posts',
            array($this, 'scheduled_default_status_callback'),
            'openai-post-generator',
            'openai_post_generator_schedule'
        );

        // Scheduling fields
        add_settings_section(
            'openai_post_generator_schedule',
            'Scheduling',
            function() { echo 'Configure automatic post generation schedule.'; },
            'openai-post-generator'
        );
        add_settings_field(
            'schedule_frequency',
            'Schedule Frequency',
            array($this, 'schedule_frequency_callback'),
            'openai-post-generator',
            'openai_post_generator_schedule'
        );
        add_settings_field(
            'schedule_time',
            'Schedule Time',
            array($this, 'schedule_time_callback'),
            'openai-post-generator',
            'openai_post_generator_schedule'
        );
    }

    /**
     * Print the Section text
     */
    public function print_section_info() {
        print 'Enter your OpenAI API settings below:';
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function api_key_callback() {
        $options = get_option('openai_post_generator_options');
        printf(
            '<input type="password" id="api_key" name="openai_post_generator_options[api_key]" value="%s" class="regular-text" />',
            isset($options['api_key']) ? esc_attr($options['api_key']) : ''
        );
    }

    public function model_callback() {
        $options = get_option('openai_post_generator_options');
        $model = isset($options['model']) ? $options['model'] : 'gpt-4';
        ?>
        <select id="model" name="openai_post_generator_options[model]">
            <option value="gpt-4" <?php selected($model, 'gpt-4'); ?>>GPT-4</option>
            <option value="gpt-3.5-turbo" <?php selected($model, 'gpt-3.5-turbo'); ?>>GPT-3.5 Turbo</option>
        </select>
        <?php
    }

    public function max_tokens_callback() {
        $options = get_option('openai_post_generator_options');
        printf(
            '<input type="number" id="max_tokens" name="openai_post_generator_options[max_tokens]" value="%s" class="small-text" min="1" max="4096" />',
            isset($options['max_tokens']) ? esc_attr($options['max_tokens']) : '1000'
        );
    }

    public function prompt_callback() {
        $options = get_option('openai_post_generator_options');
        printf(
            '<textarea id="prompt" name="openai_post_generator_options[prompt]" rows="5" cols="50" class="large-text">%s</textarea>',
            isset($options['prompt']) ? esc_textarea($options['prompt']) : 'Write a blog post about:'
        );
        echo '<p class="description">You can use the same placeholders as in the title template:</p>';
        echo '<ul style="list-style-type: disc; margin-left: 20px;">';
        echo '<li><code>{date}</code> - Current date and time</li>';
        echo '<li><code>{date_only}</code> - Current date only</li>';
        echo '<li><code>{time_only}</code> - Current time only</li>';
        echo '<li><code>{year}</code> - Current year</li>';
        echo '<li><code>{month}</code> - Current month</li>';
        echo '<li><code>{day}</code> - Current day</li>';
        echo '</ul>';
        echo '<p class="description">Examples: <code>Write a news summary for {date_only}</code> or <code>Create a market analysis for {month}/{year}</code></p>';
    }

    /**
     * Get date/time replacements array
     * 
     * @return array Array of placeholders and their replacements
     */
    private function get_datetime_replacements() {
        $current_time = current_time('timestamp');
        return [
            '{date}' => date('Y-m-d H:i', $current_time),
            '{date_only}' => date('Y-m-d', $current_time),
            '{time_only}' => date('H:i', $current_time),
            '{year}' => date('Y', $current_time),
            '{month}' => date('m', $current_time),
            '{day}' => date('d', $current_time),
        ];
    }
    
    /**
     * Replace placeholders in a string
     * 
     * @param string $text Text with placeholders
     * @return string Text with placeholders replaced
     */
    private function replace_placeholders($text) {
        $replacements = $this->get_datetime_replacements();
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    public function scheduled_title_template_callback() {
        $options = get_option('openai_post_generator_options');
        $template = isset($options['scheduled_title_template']) ? $options['scheduled_title_template'] : 'AI Post for {date}';
        echo '<input type="text" id="scheduled_title_template" name="openai_post_generator_options[scheduled_title_template]" value="' . esc_attr($template) . '" class="regular-text" />';
        echo '<p class="description">Available placeholders:</p>';
        echo '<ul style="list-style-type: disc; margin-left: 20px;">';
        echo '<li><code>{date}</code> - Current date and time (e.g., 2025-05-04 18:30)</li>';
        echo '<li><code>{date_only}</code> - Current date only (e.g., 2025-05-04)</li>';
        echo '<li><code>{time_only}</code> - Current time only (e.g., 18:30)</li>';
        echo '<li><code>{year}</code> - Current year (e.g., 2025)</li>';
        echo '<li><code>{month}</code> - Current month (e.g., 05)</li>';
        echo '<li><code>{day}</code> - Current day (e.g., 04)</li>';
        echo '</ul>';
        echo '<p class="description">Examples: <code>Daily News Update: {date_only}</code> or <code>Market Report for {month}/{day}/{year}</code></p>';
    }

    public function scheduled_default_category_callback() {
        $options = get_option('openai_post_generator_options');
        $category = isset($options['scheduled_default_category']) ? $options['scheduled_default_category'] : 1;
        
        wp_dropdown_categories(array(
            'show_option_none' => 'Select Category',
            'hide_empty' => 0,
            'selected' => $category,
            'name' => 'openai_post_generator_options[scheduled_default_category]',
            'id' => 'scheduled_default_category',
            'class' => 'regular-text'
        ));
        
        echo '<p class="description">Posts generated on schedule will use this category.</p>';
    }
    
    public function scheduled_default_author_callback() {
        $options = get_option('openai_post_generator_options');
        $author_id = isset($options['scheduled_default_author']) ? $options['scheduled_default_author'] : get_current_user_id();
        
        $users = get_users(array(
            'role__in' => array('administrator', 'editor', 'author'),
            'orderby' => 'display_name'
        ));
        
        echo '<select id="scheduled_default_author" name="openai_post_generator_options[scheduled_default_author]" class="regular-text">';
        foreach ($users as $user) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($user->ID),
                selected($author_id, $user->ID, false),
                esc_html(sprintf('%s (%s)', $user->display_name, $user->user_login))
            );
        }
        echo '</select>';
        
        echo '<p class="description">Posts generated on schedule will be attributed to this author.</p>';
    }

    public function scheduled_default_status_callback() {
        $options = get_option('openai_post_generator_options');
        $status = isset($options['scheduled_default_status']) ? $options['scheduled_default_status'] : 'publish';
        ?>
        <select id="scheduled_default_status" name="openai_post_generator_options[scheduled_default_status]" class="regular-text">
            <option value="publish" <?php selected($status, 'publish'); ?>>Publish immediately</option>
            <option value="draft" <?php selected($status, 'draft'); ?>>Save as draft</option>
            <option value="pending" <?php selected($status, 'pending'); ?>>Pending review</option>
        </select>
        <p class="description">Choose whether scheduled posts should be published immediately or saved as drafts for review.</p>
        <?php
    }

    public function schedule_frequency_callback() {
        $options = get_option('openai_post_generator_options');
        $frequency = isset($options['schedule_frequency']) ? $options['schedule_frequency'] : '';
        ?>
        <select id="schedule_frequency" name="openai_post_generator_options[schedule_frequency]">
            <option value="">None</option>
            <option value="every_5_minutes" <?php selected($frequency, 'every_5_minutes'); ?>>Every 5 Minutes</option>
            <option value="every_10_minutes" <?php selected($frequency, 'every_10_minutes'); ?>>Every 10 Minutes</option>
            <option value="every_30_minutes" <?php selected($frequency, 'every_30_minutes'); ?>>Every 30 Minutes</option>
            <option value="every_60_minutes" <?php selected($frequency, 'every_60_minutes'); ?>>Every 60 Minutes</option>
            <option value="hourly" <?php selected($frequency, 'hourly'); ?>>Hourly</option>
            <option value="twicedaily" <?php selected($frequency, 'twicedaily'); ?>>Twice Daily</option>
            <option value="daily" <?php selected($frequency, 'daily'); ?>>Daily</option>
            <option value="weekly" <?php selected($frequency, 'weekly'); ?>>Weekly</option>
        </select>
        <?php
        // Display next scheduled time if available
        $next = wp_next_scheduled('openai_post_generator_cron_event');
        if ($next) {
            echo '<p class="description">Next scheduled run: ' . date('Y-m-d H:i:s', $next) . '</p>';
        }
    }

    public function schedule_time_callback() {
        $options = get_option('openai_post_generator_options');
        $time = isset($options['schedule_time']) ? $options['schedule_time'] : '00:00';
        $custom_intervals = ['every_5_minutes', 'every_10_minutes', 'every_30_minutes', 'every_60_minutes'];
        $frequency = isset($options['schedule_frequency']) ? $options['schedule_frequency'] : '';
        $disabled = in_array($frequency, $custom_intervals) ? 'disabled' : '';
        
        echo '<input type="time" id="schedule_time" name="openai_post_generator_options[schedule_time]" value="' . esc_attr($time) . '" ' . $disabled . ' />';
        
        if (in_array($frequency, $custom_intervals)) {
            echo '<p class="description">Time setting not used for minute-based schedules</p>';
        } else {
            echo '<p class="description">Set the time when the post should be generated (using your WordPress timezone: ' . wp_timezone_string() . ')</p>';
        }
    }

    /**
     * Render the settings page for this plugin.
     *
     * @since    1.0.0
     */
    public function display_plugin_setup_page() {
        include_once 'partials/openai-post-generator-admin-display.php';
    }

    /**
     * Validate the settings
     *
     * @since    1.0.0
     */
    public function validate($input) {
        $valid = array();
        $valid['api_key'] = sanitize_text_field($input['api_key']);
        $valid['model'] = sanitize_text_field($input['model']);
        $valid['max_tokens'] = absint($input['max_tokens']);
        $valid['prompt'] = sanitize_textarea_field($input['prompt']);
        $valid['scheduled_title_template'] = isset($input['scheduled_title_template']) ? sanitize_text_field($input['scheduled_title_template']) : 'AI Post for {date}';
        $valid['scheduled_default_category'] = isset($input['scheduled_default_category']) ? absint($input['scheduled_default_category']) : 1;
        $valid['scheduled_default_author'] = isset($input['scheduled_default_author']) ? absint($input['scheduled_default_author']) : get_current_user_id();
        $valid['scheduled_default_status'] = isset($input['scheduled_default_status']) && in_array($input['scheduled_default_status'], ['publish', 'draft', 'pending']) ? $input['scheduled_default_status'] : 'publish';
        $valid['schedule_frequency'] = isset($input['schedule_frequency']) ? sanitize_text_field($input['schedule_frequency']) : '';
        $valid['schedule_time'] = isset($input['schedule_time']) ? sanitize_text_field($input['schedule_time']) : '00:00';

        // Handle scheduling
        $this->handle_scheduling($valid['schedule_frequency'], $valid['schedule_time']);

        return $valid;
    }

    private function handle_scheduling($frequency, $time) {
        // Debug log
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[OpenAI Post Generator] Handling scheduling: Frequency=' . $frequency . ', Time=' . $time);
        }
        
        // Clear previous schedule
        wp_clear_scheduled_hook('openai_post_generator_cron_event');
        
        if (!$frequency) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] No schedule selected, cleared existing schedules');
            }
            return;
        }
        
        // Custom intervals don't use time setting, they run immediately and repeat
        $custom_intervals = ['every_5_minutes', 'every_10_minutes', 'every_30_minutes', 'every_60_minutes'];
        
        if (in_array($frequency, $custom_intervals)) {
            $timestamp = time();
            wp_schedule_event($timestamp, $frequency, 'openai_post_generator_cron_event');
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Scheduled with custom interval: ' . $frequency . ', first run at: ' . date('Y-m-d H:i:s', $timestamp));
            }
        } elseif ($frequency && $time) {
            $timestamp = $this->get_next_scheduled_time($time);
            wp_schedule_event($timestamp, $frequency, 'openai_post_generator_cron_event');
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Scheduled with standard interval: ' . $frequency . ' at ' . $time . ', first run at: ' . date('Y-m-d H:i:s', $timestamp));
            }
        }
    }

    private function get_next_scheduled_time($time) {
        $now = current_time('timestamp');
        list($hour, $minute) = explode(':', $time);
        $scheduled = mktime($hour, $minute, 0, date('n', $now), date('j', $now), date('Y', $now));
        if ($scheduled <= $now) {
            $scheduled = strtotime('+1 day', $scheduled);
        }
        return $scheduled;
    }

    /**
     * AJAX handler for post generation
     *
     * @since    1.0.0
     */
    public function generate_post_ajax_handler() {
        // Debug log
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[OpenAI Post Generator] generate_post_ajax_handler called at ' . date('Y-m-d H:i:s'));
        }
        
        // Check if this is a scheduled request or a manual AJAX request
        $is_scheduled = isset($_POST['is_scheduled']) && $_POST['is_scheduled'];
        
        // Verify nonce (different approach for scheduled vs manual)
        if ($is_scheduled) {
            // For scheduled posts, we don't need standard nonce verification
            // Instead, we check if this is called from our cron callback
            if (!isset($_POST['scheduled_secret']) || $_POST['scheduled_secret'] !== 'openai_scheduled_' . date('Ymd')) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[OpenAI Post Generator] Invalid scheduled secret for scheduled post');
                }
                return false;
            }
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Scheduled secret verified successfully');
            }
        } else {
            // For manual AJAX requests, use standard nonce verification
            if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'openai_post_generator_nonce')) {
                wp_send_json_error('Invalid nonce');
                return;
            }
        }
        
        // Prevent duplicate submissions with the same topic in quick succession
        static $processed_topics = array();
        $topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';
        
        if (in_array($topic, $processed_topics)) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Duplicate topic detected and blocked: ' . $topic);
            }
            return $is_scheduled ? false : null;
        }
        $processed_topics[] = $topic;

        // Check user capabilities for manual requests
        if (!$is_scheduled && !current_user_can('edit_posts')) {
            wp_send_json_error('Insufficient permissions');
            return;
        }

        // Get plugin options
        $options = get_option('openai_post_generator_options');
        if (empty($options['api_key'])) {
            if ($is_scheduled) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[OpenAI Post Generator] OpenAI API key is not configured');
                }
                return false;
            } else {
                wp_send_json_error('OpenAI API key is not configured');
                return;
            }
        }

        // Get post data
        $category = isset($_POST['category']) ? intval($_POST['category']) : 0;
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : 'draft';

        if (empty($topic)) {
            if ($is_scheduled) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[OpenAI Post Generator] Topic is required for scheduled post');
                }
                return false;
            } else {
                wp_send_json_error('Topic is required');
                return;
            }
        }

        try {
            // Prepare the prompt with placeholder replacements
            $raw_prompt = isset($_POST['custom_prompt']) ? $_POST['custom_prompt'] : $options['prompt'];
            $processed_prompt = isset($_POST['custom_prompt']) ? $raw_prompt : $this->replace_placeholders($raw_prompt);
            $prompt = $processed_prompt . ' ' . $topic;
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Making API request for topic: ' . $topic);
                error_log('[OpenAI Post Generator] Using processed prompt: ' . $processed_prompt);
            }

            // Prepare the request data for OpenAI API
            $data = [
                'model' => $options['model'],
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a helpful assistant that writes blog posts.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => $options['max_tokens'],
                'temperature' => 0.7,
            ];

            // Initialize cURL session for OpenAI API request
            $ch = curl_init('https://api.openai.com/v1/chat/completions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $options['api_key']
            ]);
            
            // Set timeout options
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // Connection timeout
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);       // Request timeout
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Executing API request...');
            }

            // Execute the request
            $response = curl_exec($ch);
            
            // Get HTTP status and other info for debugging
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $time_taken = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] API request completed. Status: ' . $http_status . ', Time: ' . $time_taken . 's');
            }

            // Check for errors
            if (curl_errno($ch)) {
                $curl_error = curl_error($ch);
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[OpenAI Post Generator] cURL Error: ' . $curl_error);
                }
                throw new Exception($curl_error);
            }

            curl_close($ch);

            // Decode the response
            $response_data = json_decode($response, true);
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('[OpenAI Post Generator] JSON decode error: ' . json_last_error_msg());
                    error_log('[OpenAI Post Generator] Response received: ' . substr($response, 0, 1000) . '...');
                }
            }

            if (!isset($response_data['choices'][0]['message']['content'])) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[OpenAI Post Generator] Invalid response structure from API: ' . json_encode($response_data));
                }
                throw new Exception('Invalid response from OpenAI API');
            }

            // Get the generated content
            $article_content = $response_data['choices'][0]['message']['content'];
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Successfully generated content, creating post');
            }

            // Create the post
            $post_data = array(
                'post_title'    => $topic,
                'post_content'  => $article_content,
                'post_status'   => $status,
                'post_author'   => isset($_POST['author_id']) ? intval($_POST['author_id']) : get_current_user_id(),
                'post_category' => array($category)
            );

            $post_id = wp_insert_post($post_data);

            if (is_wp_error($post_id)) {
                throw new Exception($post_id->get_error_message());
            }
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Post created successfully with ID: ' . $post_id);
            }

            // Return success response with edit URL
            if ($is_scheduled) {
                return $post_id;
            } else {
                wp_send_json_success(array(
                    'message' => 'Post generated successfully',
                    'edit_url' => get_edit_post_link($post_id, '')
                ));
            }

        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Error: ' . $e->getMessage());
            }
            
            if ($is_scheduled) {
                return false;
            } else {
                wp_send_json_error('Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Display the debug page for the plugin
     */
    public function display_debug_page() {
        // Check if the user wants to trigger a scheduled post
        if (isset($_POST['trigger_scheduled_post']) && current_user_can('manage_options')) {
            if (check_admin_referer('trigger_scheduled_post_nonce')) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[OpenAI Post Generator] Manually triggering scheduled post from debug page');
                }
                
                // Set a flag to indicate this is a manual debug run
                define('OPENAI_MANUAL_DEBUG_RUN', true);
                
                $result = self::generate_post_on_schedule();
                $message = $result ? 'Post generated successfully with ID: ' . $result : 'Failed to generate post';
            }
        }
        
        // Check next scheduled event
        $next = wp_next_scheduled('openai_post_generator_cron_event');
        $next_formatted = $next ? date('Y-m-d H:i:s', $next) : 'Not scheduled';
        
        // Show debug info
        include_once 'partials/openai-post-generator-debug-display.php';
    }

    /**
     * Static version of get_datetime_replacements for use in static methods
     */
    private static function get_static_datetime_replacements() {
        $current_time = current_time('timestamp');
        return [
            '{date}' => date('Y-m-d H:i', $current_time),
            '{date_only}' => date('Y-m-d', $current_time),
            '{time_only}' => date('H:i', $current_time),
            '{year}' => date('Y', $current_time),
            '{month}' => date('m', $current_time),
            '{day}' => date('d', $current_time),
        ];
    }
    
    /**
     * Static version of replace_placeholders for use in static methods
     */
    private static function static_replace_placeholders($text) {
        $replacements = self::get_static_datetime_replacements();
        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }

    public static function generate_post_on_schedule() {
        // Check if this is a manual debug run
        $is_manual_debug = defined('OPENAI_MANUAL_DEBUG_RUN') && OPENAI_MANUAL_DEBUG_RUN;
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[OpenAI Post Generator] Scheduled post generation triggered at ' . date('Y-m-d H:i:s') . ($is_manual_debug ? ' (manual debug run)' : ''));
        }
        
        $options = get_option('openai_post_generator_options');
        if (empty($options['api_key']) || empty($options['prompt'])) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Missing API key or prompt, aborting scheduled generation');
            }
            return;
        }
        
        // Process template with placeholders
        $template = isset($options['scheduled_title_template']) ? $options['scheduled_title_template'] : 'AI Post for {date}';
        $title = self::static_replace_placeholders($template);
        
        // Process prompt with placeholders
        $raw_prompt = $options['prompt'];
        $processed_prompt = self::static_replace_placeholders($raw_prompt);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[OpenAI Post Generator] Using processed prompt: ' . $processed_prompt);
        }
        
        // Get default category, author and status from settings
        $default_category = isset($options['scheduled_default_category']) ? absint($options['scheduled_default_category']) : 1;
        $default_author = isset($options['scheduled_default_author']) ? absint($options['scheduled_default_author']) : get_current_user_id();
        $default_status = isset($options['scheduled_default_status']) ? $options['scheduled_default_status'] : 'publish';
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[OpenAI Post Generator] Starting scheduled post generation with title: ' . $title);
            error_log('[OpenAI Post Generator] Using category ID: ' . $default_category . ', author ID: ' . $default_author . ', and status: ' . $default_status);
        }
        
        // Use a simpler approach for scheduled posts instead of nonces
        $_POST = [
            'topic' => $title,
            'category' => $default_category,
            'status' => $default_status,
            'author_id' => $default_author,
            'is_scheduled' => true,
            'scheduled_secret' => 'openai_scheduled_' . date('Ymd'),
            'custom_prompt' => $processed_prompt  // Pass the processed prompt to the handler
        ];
        
        try {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Creating instance for scheduled generation');
            }
            
            $instance = new self('openai-post-generator', OPENAI_POST_GENERATOR_VERSION);
            
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Calling generate_post_ajax_handler for scheduled generation');
            }
            
            // Execute with extended timeout for scheduled posts
            set_time_limit(180); // 3 minutes
            $result = $instance->generate_post_ajax_handler();
            
            if ($result) {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[OpenAI Post Generator] Scheduled post generation completed successfully. Post ID: ' . $result);
                }
                return $result;
            } else {
                if (defined('WP_DEBUG') && WP_DEBUG) {
                    error_log('[OpenAI Post Generator] Scheduled post generation failed.');
                }
                return false;
            }
        } catch (Exception $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('[OpenAI Post Generator] Exception in scheduled generation: ' . $e->getMessage());
            }
            return false;
        }
    }
}

// Register cron event callback outside the class
add_action('openai_post_generator_cron_event', ['OpenAI_Post_Generator_Settings', 'generate_post_on_schedule']); 