<?php
/**
 * Plugin Name: OpenAI Post Generator
 * Plugin URI: https://github.com/prodhan/openai-post-generator
 * Description: A WordPress plugin that generates and publishes articles using OpenAI's API.
 * Version: 1.0.0
 * Author: Ariful Islam
 * Author URI: https://ariful.net
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: openai-post-generator
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('OPENAI_POST_GENERATOR_VERSION', '1.0.0');
define('OPENAI_POST_GENERATOR_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('OPENAI_POST_GENERATOR_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include required files
require_once OPENAI_POST_GENERATOR_PLUGIN_DIR . 'includes/class-openai-post-generator.php';
require_once OPENAI_POST_GENERATOR_PLUGIN_DIR . 'includes/class-openai-post-generator-settings.php';

// Initialize the plugin
function openai_post_generator_init() {
    $plugin = new OpenAI_Post_Generator();
    $plugin->run();
}
add_action('plugins_loaded', 'openai_post_generator_init');

// Activation hook
register_activation_hook(__FILE__, 'openai_post_generator_activate');
function openai_post_generator_activate() {
    // Add activation tasks here
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'openai_post_generator_deactivate');
function openai_post_generator_deactivate() {
    // Add deactivation tasks here
}

