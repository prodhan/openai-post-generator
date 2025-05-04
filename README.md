# OpenAI Post Generator

A powerful WordPress plugin that automatically generates and publishes articles using OpenAI's API.

## Description

OpenAI Post Generator is a feature-rich WordPress plugin that leverages OpenAI's language models to automatically generate high-quality blog posts. With an intuitive interface and flexible scheduling options, you can automate your content creation process while maintaining control over the output.

## Features

### Core Features
- Configure OpenAI API settings (API key, model, token limits)
- Choose between GPT-3.5 Turbo and GPT-4 models
- Set maximum token limits for cost control
- Define custom prompts with dynamic placeholders
- Generate posts on demand or on schedule
- Detailed debug tools and logging

### Post Generation
- Manual post generation with topic selection
- Preview generated content before publishing
- Choose post categories and status
- Fully automated scheduled post creation

### Scheduling System
- Multiple scheduling frequencies:
  - Every 5, 10, 30, or 60 minutes
  - Hourly, twice daily, daily, or weekly
- Set specific times for standard schedules
- Configurable default post settings:
  - Default author
  - Default category
  - Default post status (publish, draft, or pending)

### Dynamic Content
- Use placeholders in both titles and prompts:
  - `{date}`: Current date and time
  - `{date_only}`: Just the date
  - `{time_only}`: Just the time
  - `{year}`, `{month}`, `{day}`: Individual date components
- Create timely, contextual content automatically

### Debugging & Maintenance
- Debug page for testing and diagnostics
- Manually trigger scheduled post generation
- View upcoming scheduled posts
- Comprehensive logging

## Installation

1. Download the plugin files and upload to `/wp-content/plugins/openai-post-generator/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Post Generator' in the admin menu
4. Enter your OpenAI API key and configure your settings
5. Start generating content!

## Configuration

### API Setup
1. Obtain an API key from [OpenAI's platform](https://platform.openai.com/)
2. Enter your API key in the plugin settings
3. Select your preferred model (GPT-3.5 Turbo or GPT-4)
4. Set maximum tokens for generated content
5. Define your default prompt template

### Scheduling
1. Choose your preferred schedule frequency
2. Set the time for scheduled post generation (for standard schedules)
3. Configure default category, author and post status for scheduled posts
4. Create a custom title template with placeholders
5. Save your settings

### WordPress Cron
For scheduling to work properly:
- Ensure WordPress cron is functioning (or use a server cron job calling `wp-cron.php`)
- If using `DISABLE_WP_CRON`, make sure to set up an alternative cron method

## Usage

### Manual Post Generation
1. Go to the 'Generate Post' tab
2. Enter your desired topic
3. Select category and post status
4. Click 'Generate Post'
5. The generated post will be created according to your settings

### Scheduled Posts
1. Configure your scheduling preferences in settings
2. The plugin will automatically generate posts at your specified intervals
3. Posts will use your default settings for category, author, and status
4. Use the Debug page to test your scheduled post setup

### Using Placeholders
In both your default prompt and title templates, you can use these placeholders:
- `{date}` - Full date and time (e.g., 2025-05-04 18:30)
- `{date_only}` - Date only (e.g., 2025-05-04)
- `{time_only}` - Time only (e.g., 18:30)
- `{year}` - Current year (e.g., 2025)
- `{month}` - Current month (e.g., 05)
- `{day}` - Current day (e.g., 04)

Examples:
- Title: `Daily News Update: {date_only}`
- Prompt: `Write a comprehensive market analysis for {month}/{year} focusing on emerging trends.`

### Debugging
If you encounter issues:
1. Go to 'Post Generator > Debug'
2. Check the next scheduled run information
3. Test by manually generating a scheduled post
4. Review the debug logs at `wp-content/debug.log`

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- OpenAI API key
- WordPress cron enabled (or alternative cron setup)

## WordPress.org Plugin Info

* **Requires at least:** 5.0
* **Tested up to:** 6.4
* **Requires PHP:** 7.4
* **Stable tag:** 1.0.0
* **License:** GPLv2 or later
* **License URI:** https://www.gnu.org/licenses/gpl-2.0.html

## Author

- **Ariful Islam**
- Website: [ariful.net](https://ariful.net)
- GitHub: [prodhan](https://github.com/prodhan/openai-post-generator)

## License

This plugin is licensed under the GPL v2 or later.

See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for more information.

## Support

For support, please open an issue on the GitHub repository.

## Changelog

### 1.0.0
- Initial release
- OpenAI API integration
- Basic post generation
- Scheduling functionality
- Customizable settings
- Debug tools 