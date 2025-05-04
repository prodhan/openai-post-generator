<?php
/**
 * Debug page for the OpenAI Post Generator plugin
 *
 * @since      1.0.0
 * @package    OpenAI_Post_Generator
 * @subpackage OpenAI_Post_Generator/admin/partials
 */
?>

<div class="wrap">
    <h1>Post Generator - Debug</h1>
    
    <?php if (isset($message)): ?>
    <div class="notice <?php echo strpos($message, 'success') !== false ? 'notice-success' : 'notice-error'; ?> is-dismissible">
        <p><?php echo esc_html($message); ?></p>
    </div>
    <?php endif; ?>
    
    <h2>Cron Information</h2>
    <p><strong>Next Scheduled Run:</strong> <?php echo esc_html($next_formatted); ?></p>
    <p><strong>Current Time:</strong> <?php echo esc_html(current_time('Y-m-d H:i:s')); ?></p>
    <p><strong>WP_CRON Enabled:</strong> <?php echo defined('DISABLE_WP_CRON') && DISABLE_WP_CRON ? 'No (DISABLE_WP_CRON is true)' : 'Yes'; ?></p>
    
    <h2>Test Scheduled Post Generation</h2>
    <form method="post" action="">
        <?php wp_nonce_field('trigger_scheduled_post_nonce'); ?>
        <p>
            <input type="submit" name="trigger_scheduled_post" value="Generate Scheduled Post Now" class="button button-primary">
        </p>
    </form>
    
    <h2>Debug Logs</h2>
    <p>Check wp-content/debug.log for entries starting with [OpenAI Post Generator]</p>
</div> 