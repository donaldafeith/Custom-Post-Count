
# Custom Post Count Plugin

## Description
The Custom Post Count plugin allows you to display the number of posts based on selected categories, tags, custom post types, and a specified date range. This plugin adds an admin page where you can configure your selections and view the resulting post count.

## Installation
1. Upload the `custom-post-count` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage
1. Navigate to the 'Post Count' page in the WordPress admin menu.
2. Select the categories, enter tags (comma-separated), custom post types (comma-separated), and specify the date range.
3. Click the 'Save Changes' button to save your selections.
4. The post count based on your selections will be displayed on the same page.

## Features
- Select one or more categories.
- Enter tags as a comma-separated list.
- Enter custom post types as a comma-separated list.
- Specify a date range to filter posts.
- View the total number of posts that match your criteria.

## Code Explanation

### Prevent Direct Access
Prevents direct access to the plugin file.

```php
if (!defined('ABSPATH')) {
    exit;
}
```

### Add Admin Page
Adds a new menu item for the plugin in the WordPress admin.

```php
add_action('admin_menu', 'cpc_add_admin_page');
```

### Register Settings
Registers settings and fields for the plugin's admin page.

```php
add_action('admin_init', 'cpc_register_settings');
```

### Display Post Count
Calculates and displays the post count based on selected criteria.

```php
function cpc_display_post_count() {
    // Implementation
}
add_action('admin_footer', 'cpc_display_post_count');
```

## Functions

### `cpc_add_admin_page`
Adds the admin page to the WordPress menu.

### `cpc_admin_page`
Displays the settings form and post count results.

### `cpc_register_settings`
Registers the settings, sections, and fields for the plugin.

### `cpc_sanitize_array`
Sanitizes an array of inputs.

### `cpc_main_section_cb`
Callback function for the main section description.

### `cpc_categories_cb`
Displays the categories as checkboxes.

### `cpc_tags_cb`
Displays the tags input field.

### `cpc_post_types_cb`
Displays the custom post types input field.

### `cpc_date_from_cb`
Displays the 'Date From' input field.

### `cpc_date_to_cb`
Displays the 'Date To' input field.

### `cpc_display_post_count`
Fetches and displays the post count based on selected criteria.

## License
This plugin is open-source and available under the GPL-2.0+ license.

<a href="https://www.buymeacoffee.com/donalda" target="_blank"><img src="https://cdn.buymeacoffee.com/buttons/default-orange.png" alt="Buy Me A Coffee" height="41" width="174"></a>

