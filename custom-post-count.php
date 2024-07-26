<?php
/**
 * Plugin Name: Custom Post Count
 * Description: A plugin to display post count based on selected fields, tags, and date range.
 * Version: 1.1
 * Author: Donalda Feith
 */

// Prevent direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Add Admin Page
add_action('admin_menu', 'cpc_add_admin_page');

function cpc_add_admin_page() {
    add_menu_page(
        'Custom Post Count',        // Page title
        'Post Count',              // Menu title
        'manage_options',         // Capability
        'custom-post-count',     // Menu slug
        'cpc_admin_page',       // Function to display the page
        'dashicons-chart-bar', // Icon
        5                     // Position
    );
}

function cpc_admin_page() {
    ?>
    <div class="wrap">
        <h1>Custom Post Count</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cpc_settings_group');
            do_settings_sections('custom-post-count');
            submit_button();
            ?>
        </form>
        <div id="cpc-results">
            <?php cpc_display_post_count(); ?>
        </div>
    </div>
    <?php
}

// Register Settings
add_action('admin_init', 'cpc_register_settings');

function cpc_register_settings() {
    register_setting('cpc_settings_group', 'cpc_categories', 'cpc_sanitize_array');
    register_setting('cpc_settings_group', 'cpc_tags', 'sanitize_text_field');
    register_setting('cpc_settings_group', 'cpc_post_types', 'sanitize_text_field');
    register_setting('cpc_settings_group', 'cpc_date_from', 'sanitize_text_field');
    register_setting('cpc_settings_group', 'cpc_date_to', 'sanitize_text_field');

    add_settings_section('cpc_main_section', 'Settings', 'cpc_main_section_cb', 'custom-post-count');

    add_settings_field('cpc_categories', 'Categories', 'cpc_categories_cb', 'custom-post-count', 'cpc_main_section');
    add_settings_field('cpc_tags', 'Tags (comma-separated)', 'cpc_tags_cb', 'custom-post-count', 'cpc_main_section');
    add_settings_field('cpc_post_types', 'Custom Post Types (comma-separated)', 'cpc_post_types_cb', 'custom-post-count', 'cpc_main_section');
    add_settings_field('cpc_date_from', 'Date From', 'cpc_date_from_cb', 'custom-post-count', 'cpc_main_section');
    add_settings_field('cpc_date_to', 'Date To', 'cpc_date_to_cb', 'custom-post-count', 'cpc_main_section');
}

function cpc_sanitize_array($input) {
    return is_array($input) ? array_map('sanitize_text_field', $input) : [];
}

function cpc_main_section_cb() {
    echo 'Select categories, tags, custom post types, and date range to get the post count.';
}

function cpc_categories_cb() {
    $categories = get_option('cpc_categories', []);
    $all_categories = get_categories();
    foreach ($all_categories as $category) {
        echo '<input type="checkbox" name="cpc_categories[]" value="' . esc_attr($category->term_id) . '"' . (is_array($categories) && in_array($category->term_id, $categories) ? ' checked' : '') . '> ' . esc_html($category->name) . '<br>';
    }
}

function cpc_tags_cb() {
    $tags = get_option('cpc_tags');
    echo '<input type="text" name="cpc_tags" value="' . esc_attr($tags) . '" class="regular-text">';
}

function cpc_post_types_cb() {
    $post_types = get_option('cpc_post_types');
    echo '<input type="text" name="cpc_post_types" value="' . esc_attr($post_types) . '" class="regular-text">';
}

function cpc_date_from_cb() {
    $date_from = get_option('cpc_date_from');
    echo '<input type="date" name="cpc_date_from" value="' . esc_attr($date_from) . '">';
}

function cpc_date_to_cb() {
    $date_to = get_option('cpc_date_to');
    echo '<input type="date" name="cpc_date_to" value="' . esc_attr($date_to) . '">';
}

// Display Post Count
function cpc_display_post_count() {
    if (isset($_GET['page']) && $_GET['page'] == 'custom-post-count') {
        global $wpdb;
        
        $categories = get_option('cpc_categories', []);
        $tags = get_option('cpc_tags');
        $post_types = get_option('cpc_post_types');
        $date_from = get_option('cpc_date_from');
        $date_to = get_option('cpc_date_to');

        if (empty($categories) && empty($tags) && empty($post_types)) {
            echo '<p>Please select at least one category, tag, or custom post type.</p>';
            return;
        }

        $category_condition = !empty($categories) ? "(tt.taxonomy = 'category' AND t.term_id IN (" . implode(',', array_map('intval', $categories)) . "))" : '';
        $tags_array = array_filter(array_map('sanitize_text_field', array_map('trim', explode(',', $tags))));
        $tags_condition = !empty($tags_array) ? "(tt.taxonomy = 'post_tag' AND t.name IN ('" . implode("','", $tags_array) . "'))" : '';
        $conditions = array_filter([$category_condition, $tags_condition]);

        $conditions_sql = !empty($conditions) ? 'AND (' . implode(' OR ', $conditions) . ')' : '';

        $post_types_array = array_filter(array_map('sanitize_text_field', array_map('trim', explode(',', $post_types))));
        $post_types_sql = !empty($post_types_array) ? "AND p.post_type IN ('" . implode("','", $post_types_array) . "')" : '';

        $query = "
            SELECT COUNT(*)
            FROM {$wpdb->posts} AS p
            INNER JOIN {$wpdb->term_relationships} AS tr ON p.ID = tr.object_id
            INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
            WHERE p.post_status = 'publish'
            AND p.post_date BETWEEN %s AND %s
            $post_types_sql
            $conditions_sql
        ";

        $post_count = $wpdb->get_var($wpdb->prepare($query, $date_from, $date_to));
        
        echo '<div class="cpc-results-box">';
        echo '<h2>Results:</h2>';
        if (!empty($tags_array)) {
            echo '<p>Tags: ' . esc_html(implode(', ', $tags_array)) . '</p>';
        }
        if (!empty($categories)) {
            $category_names = array_map(function($cat_id) {
                $category = get_category($cat_id);
                return $category ? $category->name : '';
            }, $categories);
            echo '<p>Categories: ' . esc_html(implode(', ', $category_names)) . '</p>';
        }
        echo '<p>Post count: ' . esc_html($post_count) . '</p>';
        echo '</div>';
    }
}

add_action('admin_footer', 'cpc_display_post_count');
