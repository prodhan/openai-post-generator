=== OpenAI Post Generator ===
Contributors: prodhan
Tags: post generator, schedule post, automation
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A powerful WordPress plugin that automatically generates and publishes articles using OpenAI\'s API.

== Description ==
OpenAI Post Generator is a feature-rich WordPress plugin that leverages OpenAI\'s language models to automatically generate high-quality blog posts. With an intuitive interface and flexible scheduling options, you can automate your content creation process while maintaining control over the output.

== Installation ==
1. Download the plugin files and upload to `/wp-content/plugins/openai-post-generator/` directory
2. Activate the plugin through the \'Plugins\' menu in WordPress
3. Go to \'Post Generator\' in the admin menu
4. Enter your OpenAI API key and configure your settings
5. Start generating content!

== Frequently Asked Questions ==
How to add Manual Post Generation?
1. Go to the \'Generate Post\' tab
2. Enter your desired topic
3. Select category and post status
4. Click \'Generate Post\'
5. The generated post will be created according to your settings

How to make Scheduled Posts?
1. Configure your scheduling preferences in settings
2. The plugin will automatically generate posts at your specified intervals
3. Posts will use your default settings for category, author, and status
4. Use the Debug page to test your scheduled post setup

How to use Placeholders?
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

== Changelog ==
- Initial release
- OpenAI API integration
- Basic post generation
- Scheduling functionality
- Customizable settings
- Debug tools 