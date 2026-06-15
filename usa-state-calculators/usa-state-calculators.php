<?php
/**
 * Plugin Name: USA State-Wise Paycheck & Child Support Calculator
 * Plugin URI: #
 * Description: Premium SEO-optimized paycheck and child support calculators for all 50 US states. Auto-creates CPT pages with state-specific content and customizable HTML/CSS/JS editors.
 * Version: 1.0.7
 * Author: AI Assistant
 * Text Domain: usa-state-calculators
 */

if (!defined('ABSPATH')) exit;

define('USC_PATH', plugin_dir_path(__FILE__));
define('USC_URL', plugin_dir_url(__FILE__));
define('USC_VERSION', '1.0.7');
define('USC_CPT', 'usc_calculator');

// Include components
require_once USC_PATH . 'includes/class-usc-cpt.php';
require_once USC_PATH . 'includes/class-usc-metaboxes.php';
require_once USC_PATH . 'includes/class-usc-seo.php';
require_once USC_PATH . 'data/default-content.php';
require_once USC_PATH . 'data/default-templates.php';
require_once USC_PATH . 'data/alimony.php';
require_once USC_PATH . 'data/mortgage.php';

// Initialize core classes
add_action('plugins_loaded', 'usc_init_plugin');
function usc_init_plugin() {
    new USC_CPT();
    new USC_Metaboxes();
    new USC_SEO();
}

// Auto-sync pages on admin load if transient is not set
add_action('admin_init', 'usc_admin_sync_pages');
function usc_admin_sync_pages() {
    if (!current_user_can('manage_options')) return;
    if (get_transient('usc_pages_generated_v12')) return;
    usc_auto_generate_state_pages();
    flush_rewrite_rules();
    set_transient('usc_pages_generated_v12', true, DAY_IN_SECONDS);
}

// Run taxonomy creation and migration on admin load
add_action('admin_init', 'usc_admin_init_taxonomy_migration', 11);
function usc_admin_init_taxonomy_migration() {
    if (!current_user_can('manage_options')) return;
    
    $categories = array(
        'paycheck'      => 'Paycheck',
        'child-support' => 'Child Support',
        'alimony'       => 'Alimony',
        'mortgage'      => 'Mortgage',
        'tax'           => 'Tax',
        'auto-loan'     => 'Auto Loan',
        'insurance'     => 'Insurance'
    );

    foreach ($categories as $slug => $name) {
        if (!term_exists($slug, 'usc_category')) {
            wp_insert_term($name, 'usc_category', array('slug' => $slug));
        }
    }

    // Migrate existing posts from meta to taxonomy
    $posts = get_posts(array(
        'post_type'      => USC_CPT,
        'posts_per_page' => -1,
        'post_status'    => array('publish', 'draft', 'pending', 'private'),
        'tax_query'      => array(
            array(
                'taxonomy' => 'usc_category',
                'field'    => 'slug',
                'terms'    => array_keys($categories),
                'operator' => 'NOT IN'
            )
        )
    ));

    if (!empty($posts)) {
        foreach ($posts as $p) {
            $calc_type = get_post_meta($p->ID, '_usc_calc_type', true);
            if ($calc_type && isset($categories[$calc_type])) {
                wp_set_object_terms($p->ID, $calc_type, 'usc_category');
            }
        }
    }
}

// Enqueue styles/scripts on front-end + localize AJAX nonce
add_action('wp_enqueue_scripts', 'usc_enqueue_assets');
function usc_enqueue_assets() {
    if (is_singular(USC_CPT)) {
        wp_enqueue_style('usc-style', USC_URL . 'public/assets/css/style.css', [], USC_VERSION);
        // Localize nonce for AJAX calls — keeps nonce out of inline JS
        wp_enqueue_script('usc-nonce-init', USC_URL . 'public/assets/js/nonce.js', [], USC_VERSION, true);
        wp_localize_script('usc-nonce-init', 'uscAjax', [
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('usc_frontend_nonce'),
        ]);
    }
}

// Enqueue styles in WP admin panel
add_action('admin_enqueue_scripts', 'usc_enqueue_admin_assets');
function usc_enqueue_admin_assets($hook) {
    global $post;
    $post_type = $post ? $post->post_type : '';
    if (empty($post_type) && isset($_GET['post_type'])) {
        $post_type = sanitize_key($_GET['post_type']);
    }
    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    if ($post_type === USC_CPT || strpos($hook, 'usc_') !== false || strpos($page, 'usc_') !== false) {
        $css_file = USC_PATH . 'public/assets/css/admin-style.css';
        $ver = file_exists($css_file) ? filemtime($css_file) : USC_VERSION;
        wp_enqueue_style('usc-admin-style', USC_URL . 'public/assets/css/admin-style.css', [], $ver);
    }
}

// Custom list header hook removed to eliminate duplicate top nav bar

// Hook CSV exporter
add_action('admin_init', 'usc_export_leads_csv');
function usc_export_leads_csv() {
    if (isset($_GET['action']) && $_GET['action'] === 'usc_export_leads') {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized user.');
        }
        // SECURITY: verify nonce to prevent CSRF
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'usc_export_leads_nonce')) {
            wp_die('Security check failed.');
        }
        
        global $wpdb;
        $leads = $wpdb->get_results("SELECT l.id, p.post_title, l.name, l.email, l.created_at FROM {$wpdb->prefix}usc_leads l LEFT JOIN {$wpdb->posts} p ON l.post_id = p.ID ORDER BY l.created_at DESC", ARRAY_A);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=captured_leads_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Lead ID', 'State Calculator Name', 'User Name', 'User Email', 'Submitted On']);
        
        if (!empty($leads)) {
            foreach ($leads as $lead) {
                fputcsv($output, [
                    $lead['id'],
                    $lead['post_title'] ?: 'State Calculator',
                    $lead['name'],
                    $lead['email'],
                    $lead['created_at']
                ]);
            }
        }
        fclose($output);
        exit;
    }
}

// Hook Sync Pages Action
add_action('admin_init', 'usc_handle_sync_pages_action');
function usc_handle_sync_pages_action() {
    if (isset($_GET['action']) && $_GET['action'] === 'usc_sync_pages') {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized user.');
        }
        // SECURITY: verify nonce to prevent CSRF
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'usc_sync_pages_nonce')) {
            wp_die('Security check failed.');
        }
        
        // Clear transient to force generation
        delete_transient('usc_pages_generated_v11');
        delete_transient('usc_pages_generated_v12');
        usc_auto_generate_state_pages();
        flush_rewrite_rules();
        
        // Redirect back with success message
        wp_safe_redirect(add_query_arg('usc_message', 'sync_success', admin_url('admin.php?page=usc_calculators_hub')));
        exit;
    }
}

// Display sync success notice
add_action('admin_notices', 'usc_admin_sync_notice');
function usc_admin_sync_notice() {
    if (isset($_GET['usc_message']) && $_GET['usc_message'] === 'sync_success') {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Calculators and state pages successfully synchronized!</strong></p></div>';
    }
}

// Activation Hook to auto-generate 50 state pages and setup custom DB tables
register_activation_hook(__FILE__, 'usc_activate_plugin');
function usc_activate_plugin() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Create Leads table
    $t_leads = $wpdb->prefix . 'usc_leads';
    dbDelta("CREATE TABLE IF NOT EXISTS $t_leads (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        post_id     INT NOT NULL,
        name        VARCHAR(200) NOT NULL,
        email       VARCHAR(200) NOT NULL,
        created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
    ) $charset;");

    // Create Usage Stats table
    $t_usage = $wpdb->prefix . 'usc_usage_stats';
    dbDelta("CREATE TABLE IF NOT EXISTS $t_usage (
        id          INT AUTO_INCREMENT PRIMARY KEY,
        post_id     INT NOT NULL UNIQUE,
        count       BIGINT DEFAULT 0,
        last_used   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) $charset;");

    // Register CPT manually during activation so flush_rewrite_rules works
    $cpt = new USC_CPT();
    $cpt->register_post_type();

    // Auto create pages if they do not exist
    usc_auto_generate_state_pages();

    // Clear rewrite rules
    flush_rewrite_rules();
}

// Deactivation Hook
register_deactivation_hook(__FILE__, 'usc_deactivate_plugin');
function usc_deactivate_plugin() {
    flush_rewrite_rules();
}

// AJAX: Save Lead — with nonce, email validation, rate limiting
add_action('wp_ajax_usc_submit_lead', 'usc_ajax_submit_lead');
add_action('wp_ajax_nopriv_usc_submit_lead', 'usc_ajax_submit_lead');
function usc_ajax_submit_lead() {
    // 1. Nonce check (CSRF protection)
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'usc_frontend_nonce')) {
        wp_send_json_error('Security check failed.', 403);
    }

    // 2. Rate limiting — max 3 submissions per IP per hour
    $ip_key = 'usc_lead_rate_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $attempts = (int) get_transient($ip_key);
    if ($attempts >= 3) {
        wp_send_json_error('Too many submissions. Please try again later.', 429);
    }
    set_transient($ip_key, $attempts + 1, HOUR_IN_SECONDS);

    // 3. Sanitize & validate inputs
    $post_id = intval($_POST['post_id'] ?? 0);
    $name    = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $email   = sanitize_email(wp_unslash($_POST['email'] ?? ''));

    if (!$post_id || empty($name) || empty($email)) {
        wp_send_json_error('Invalid input data.');
    }

    // 4. Validate email format properly
    if (!is_email($email)) {
        wp_send_json_error('Invalid email address.');
    }

    // 5. Verify post_id belongs to our CPT and is published
    $post = get_post($post_id);
    if (!$post || $post->post_type !== USC_CPT || $post->post_status !== 'publish') {
        wp_send_json_error('Invalid calculator reference.');
    }

    // 6. Insert safely
    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'usc_leads',
        ['post_id' => $post_id, 'name' => $name, 'email' => $email],
        ['%d', '%s', '%s']
    );
    wp_send_json_success('Lead saved successfully.');
}

// AJAX: Track Usage Count — with nonce
add_action('wp_ajax_usc_track_usage', 'usc_ajax_track_usage');
add_action('wp_ajax_nopriv_usc_track_usage', 'usc_ajax_track_usage');
function usc_ajax_track_usage() {
    // Nonce check
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'usc_frontend_nonce')) {
        wp_send_json_error('Security check failed.', 403);
    }

    $post_id = intval($_POST['post_id'] ?? 0);
    if (!$post_id) {
        wp_send_json_error();
    }

    // Verify post exists and is our CPT
    $post = get_post($post_id);
    if (!$post || $post->post_type !== USC_CPT) {
        wp_send_json_error();
    }

    global $wpdb;
    $table = $wpdb->prefix . 'usc_usage_stats';
    $wpdb->query($wpdb->prepare(
        "INSERT INTO $table (post_id, count, last_used)
         VALUES (%d, 1, NOW())
         ON DUPLICATE KEY UPDATE count = count + 1, last_used = NOW()",
        $post_id
    ));
    wp_send_json_success();
}

/**
 * Helper function to robustly fetch calculator page by meta or slug fallback
 */
function usc_get_calculator_by_meta($calc_type, $state_slug) {
    // 1. Try to find by meta keys (most reliable)
    $posts = get_posts([
        'post_type'   => USC_CPT,
        'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
        'meta_query'  => [
            'relation' => 'AND',
            [
                'key'     => '_usc_calc_type',
                'value'   => $calc_type,
                'compare' => '='
            ],
            [
                'key'     => '_usc_state_slug',
                'value'   => $state_slug,
                'compare' => '='
            ]
        ],
        'numberposts' => 1
    ]);
    if (!empty($posts)) {
        return $posts[0];
    }

    // 2. Fallback: Try to find by post name (slug) to support migration
    $slug_options = [];
    if ($calc_type === 'mortgage') {
        $slug_options[] = 'mortgage-calculator-' . $state_slug;
        $slug_options[] = $state_slug . '-mortgage-calculator';
    } else {
        $slug_options[] = $state_slug . '-' . $calc_type . '-calculator';
    }

    foreach ($slug_options as $slug) {
        $posts_by_slug = get_posts([
            'post_type'   => USC_CPT,
            'name'        => $slug,
            'post_status' => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
            'numberposts' => 1
        ]);
        if (!empty($posts_by_slug)) {
            return $posts_by_slug[0];
        }
    }

    return null;
}

/**
 * Clean up all duplicate calculators and permanently remove property-tax calculators.
 */
function usc_cleanup_duplicate_calculators() {
    // 1. Permanently delete all posts of type 'usc_calculator' where _usc_calc_type is 'property-tax'
    $property_tax_posts = get_posts([
        'post_type'      => USC_CPT,
        'post_status'    => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
        'numberposts'    => -1,
        'meta_query'     => [
            [
                'key'     => '_usc_calc_type',
                'value'   => 'property-tax',
                'compare' => '='
            ]
        ]
    ]);
    foreach ($property_tax_posts as $post) {
        wp_delete_post($post->ID, true);
    }

    // Fallback: Delete any posts by slug/title search if meta isn't set yet
    $property_tax_slug_posts = get_posts([
        'post_type'      => USC_CPT,
        'post_status'    => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
        'numberposts'    => -1,
        's'              => 'property-tax'
    ]);
    foreach ($property_tax_slug_posts as $post) {
        if (strpos($post->post_name, 'property-tax') !== false) {
            wp_delete_post($post->ID, true);
        }
    }

    // 2. Identify and delete duplicate calculators
    $all_posts = get_posts([
        'post_type'      => USC_CPT,
        'post_status'    => ['publish', 'draft', 'pending', 'private', 'future', 'trash'],
        'numberposts'    => -1,
        'orderby'        => 'ID',
        'order'          => 'ASC'
    ]);

    $seen = [];
    foreach ($all_posts as $post) {
        $calc_type = get_post_meta($post->ID, '_usc_calc_type', true);
        $state_slug = get_post_meta($post->ID, '_usc_state_slug', true);

        // Auto-heal missing metadata
        if (empty($calc_type) || empty($state_slug)) {
            $post_slug = $post->post_name;
            $detected_type = '';
            if (strpos($post_slug, 'paycheck') !== false) $detected_type = 'paycheck';
            elseif (strpos($post_slug, 'child-support') !== false) $detected_type = 'child-support';
            elseif (strpos($post_slug, 'alimony') !== false) $detected_type = 'alimony';
            elseif (strpos($post_slug, 'mortgage') !== false) $detected_type = 'mortgage';

            if ($detected_type) {
                $calc_type = $detected_type;
                $prefixes = ['paycheck-calculator-', 'child-support-calculator-', 'alimony-calculator-', 'mortgage-calculator-'];
                $suffixes = ['-paycheck-calculator', '-child-support-calculator', '-alimony-calculator', '-mortgage-calculator'];
                $state_slug = str_replace($prefixes, '', $post_slug);
                $state_slug = str_replace($suffixes, '', $state_slug);
                $state_slug = preg_replace('/-[0-9]+$/', '', $state_slug);

                update_post_meta($post->ID, '_usc_calc_type', $calc_type);
                update_post_meta($post->ID, '_usc_state_slug', $state_slug);
            }
        }

        if ($calc_type && $state_slug) {
            $key = $calc_type . '_' . $state_slug;
            if (isset($seen[$key])) {
                // Duplicate found! Delete permanently.
                wp_delete_post($post->ID, true);
            } else {
                $seen[$key] = $post->ID;
            }
        }
    }
}

/**
 * Auto-generates the 50 state calculator pages if they don't exist, and forces update if outdated
 */
function usc_auto_generate_state_pages() {
    // Run cleanup first
    usc_cleanup_duplicate_calculators();

    $states = usc_get_states_data();
    foreach ($states as $slug => $state) {
        // Create or Update Paycheck Calculator Page
        $paycheck_slug = $slug . '-paycheck-calculator';
        $paycheck_exists = usc_get_calculator_by_meta('paycheck', $slug);
        if (!$paycheck_exists) {
            $post_id = wp_insert_post([
                'post_title'   => $state['name'] . ' Paycheck Calculator',
                'post_name'    => $paycheck_slug,
                'post_status'  => 'publish',
                'post_type'    => USC_CPT,
                'post_content' => usc_get_default_paycheck_article_content($state),
            ]);
            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, '_usc_calc_type', 'paycheck');
                update_post_meta($post_id, '_usc_state_slug', $slug);
                update_post_meta($post_id, '_usc_seo_title', usc_get_default_seo_title('paycheck', $state['name']));
                update_post_meta($post_id, '_usc_seo_desc', usc_get_default_seo_desc('paycheck', $state));
                
                // Add default HTML, CSS, JS
                $defaults = usc_get_default_templates('paycheck', $slug);
                update_post_meta($post_id, '_usc_calc_html', $defaults['html']);
                update_post_meta($post_id, '_usc_calc_css', $defaults['css']);
                update_post_meta($post_id, '_usc_calc_js', $defaults['js']);
                update_post_meta($post_id, '_usc_faqs', usc_get_default_paycheck_faqs($state));
                update_post_meta($post_id, '_usc_template_version', '16');

                // Set Featured Image
                $thumb_id = usc_get_or_create_illustration_attachment('paycheck');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
                wp_set_object_terms($post_id, 'paycheck', 'usc_category');
            }
        } else {
            // Post exists. Update if content is outdated or contains duplicate FAQs
            $post_id = $paycheck_exists->ID;
            
            // Re-publish if draft or trash
            $post_status = get_post_status($post_id);
            if ($post_status === 'trash' || $post_status === 'draft') {
                wp_update_post([
                    'ID'          => $post_id,
                    'post_status' => 'publish'
                ]);
            }
            
            $post_content = $paycheck_exists->post_content;
            if (empty($post_content) || strpos($post_content, '<!-- usc-v5-article -->') === false || strpos($post_content, '<h2>13. Frequently Asked Questions') !== false) {
                $new_content = usc_get_default_paycheck_article_content($state);
                wp_update_post([
                    'ID'           => $post_id,
                    'post_content' => $new_content,
                ]);
                update_post_meta($post_id, '_usc_faqs', usc_get_default_paycheck_faqs($state));
            }
            
            // Set Featured Image if not set
            if (!has_post_thumbnail($post_id)) {
                $thumb_id = usc_get_or_create_illustration_attachment('paycheck');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
            }
            update_post_meta($post_id, '_usc_calc_type', 'paycheck');
            update_post_meta($post_id, '_usc_state_slug', $slug);
            wp_set_object_terms($post_id, 'paycheck', 'usc_category');

            // Force template update if version is old or templates are empty
            $current_ver = get_post_meta($post_id, '_usc_template_version', true);
            if ($current_ver !== '16' || empty(get_post_meta($post_id, '_usc_calc_html', true)) || empty(get_post_meta($post_id, '_usc_calc_js', true))) {
                $defaults = usc_get_default_templates('paycheck', $slug);
                update_post_meta($post_id, '_usc_calc_html', $defaults['html']);
                update_post_meta($post_id, '_usc_calc_css', $defaults['css']);
                update_post_meta($post_id, '_usc_calc_js', $defaults['js']);
                update_post_meta($post_id, '_usc_template_version', '16');
            }
        }

        // Create or Update Child Support Calculator Page
        $cs_slug = $slug . '-child-support-calculator';
        $cs_exists = usc_get_calculator_by_meta('child-support', $slug);
        if (!$cs_exists) {
            $post_id = wp_insert_post([
                'post_title'   => $state['name'] . ' Child Support Calculator',
                'post_name'    => $cs_slug,
                'post_status'  => 'publish',
                'post_type'    => USC_CPT,
                'post_content' => usc_get_default_child_support_article_content($state),
            ]);
            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, '_usc_calc_type', 'child-support');
                update_post_meta($post_id, '_usc_state_slug', $slug);
                update_post_meta($post_id, '_usc_seo_title', usc_get_default_seo_title('child-support', $state['name']));
                update_post_meta($post_id, '_usc_seo_desc', usc_get_default_seo_desc('child-support', $state));
                
                // Add default HTML, CSS, JS
                $defaults = usc_get_default_templates('child-support', $slug);
                update_post_meta($post_id, '_usc_calc_html', $defaults['html']);
                update_post_meta($post_id, '_usc_calc_css', $defaults['css']);
                update_post_meta($post_id, '_usc_calc_js', $defaults['js']);
                update_post_meta($post_id, '_usc_faqs', usc_get_default_child_support_faqs($state));
                update_post_meta($post_id, '_usc_template_version', '16');

                // Set Featured Image
                $thumb_id = usc_get_or_create_illustration_attachment('child-support');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
                wp_set_object_terms($post_id, 'child-support', 'usc_category');
            }
        } else {
            // Post exists. Update if content is outdated or contains duplicate FAQs
            $post_id = $cs_exists->ID;

            // Re-publish if draft or trash
            $post_status = get_post_status($post_id);
            if ($post_status === 'trash' || $post_status === 'draft') {
                wp_update_post([
                    'ID'          => $post_id,
                    'post_status' => 'publish'
                ]);
            }

            $post_content = $cs_exists->post_content;
            if (empty($post_content) || strpos($post_content, '<!-- usc-v5-article -->') === false || strpos($post_content, '<h2>13. Frequently Asked Questions') !== false) {
                $new_content = usc_get_default_child_support_article_content($state);
                wp_update_post([
                    'ID'           => $post_id,
                    'post_content' => $new_content,
                ]);
                update_post_meta($post_id, '_usc_faqs', usc_get_default_child_support_faqs($state));
            }
            
            // Set Featured Image if not set
            if (!has_post_thumbnail($post_id)) {
                $thumb_id = usc_get_or_create_illustration_attachment('child-support');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
            }
            update_post_meta($post_id, '_usc_calc_type', 'child-support');
            update_post_meta($post_id, '_usc_state_slug', $slug);
            wp_set_object_terms($post_id, 'child-support', 'usc_category');

            // Force template update if version is old or templates are empty
            $current_ver = get_post_meta($post_id, '_usc_template_version', true);
            if ($current_ver !== '16' || empty(get_post_meta($post_id, '_usc_calc_html', true)) || empty(get_post_meta($post_id, '_usc_calc_js', true))) {
                $defaults = usc_get_default_templates('child-support', $slug);
                update_post_meta($post_id, '_usc_calc_html', $defaults['html']);
                update_post_meta($post_id, '_usc_calc_css', $defaults['css']);
                update_post_meta($post_id, '_usc_calc_js', $defaults['js']);
                update_post_meta($post_id, '_usc_template_version', '16');
            }
        }

        // Create or Update Alimony Calculator Page
        $alimony_slug = $slug . '-alimony-calculator';
        $alimony_exists = usc_get_calculator_by_meta('alimony', $slug);
        if (!$alimony_exists) {
            $post_id = wp_insert_post([
                'post_title'   => $state['name'] . ' Alimony Calculator',
                'post_name'    => $alimony_slug,
                'post_status'  => 'publish',
                'post_type'    => USC_CPT,
                'post_content' => usc_get_default_alimony_article_content($state),
            ]);
            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, '_usc_calc_type', 'alimony');
                update_post_meta($post_id, '_usc_state_slug', $slug);
                update_post_meta($post_id, '_usc_seo_title', usc_get_default_alimony_seo_title($state['name']));
                update_post_meta($post_id, '_usc_seo_desc', usc_get_default_alimony_seo_desc($state));
                
                // Add default HTML, CSS, JS
                $defaults = usc_get_default_templates('alimony', $slug);
                update_post_meta($post_id, '_usc_calc_html', $defaults['html']);
                update_post_meta($post_id, '_usc_calc_css', $defaults['css']);
                update_post_meta($post_id, '_usc_calc_js', $defaults['js']);
                update_post_meta($post_id, '_usc_faqs', usc_get_default_alimony_faqs($state));
                update_post_meta($post_id, '_usc_template_version', '16');
                wp_set_object_terms($post_id, 'alimony', 'usc_category');
            }
        } else {
            // Post exists. Update if content is outdated
            $post_id = $alimony_exists->ID;

            // Re-publish if draft or trash
            $post_status = get_post_status($post_id);
            if ($post_status === 'trash' || $post_status === 'draft') {
                wp_update_post([
                    'ID'          => $post_id,
                    'post_status' => 'publish'
                ]);
            }

            $post_content = $alimony_exists->post_content;
            if (empty($post_content) || strpos($post_content, '<!-- usc-v5-article -->') === false) {
                $new_content = usc_get_default_alimony_article_content($state);
                wp_update_post([
                    'ID'           => $post_id,
                    'post_content' => $new_content,
                ]);
                update_post_meta($post_id, '_usc_faqs', usc_get_default_alimony_faqs($state));
            }
            // Ensure state slug and category term is assigned
            update_post_meta($post_id, '_usc_calc_type', 'alimony');
            update_post_meta($post_id, '_usc_state_slug', $slug);
            wp_set_object_terms($post_id, 'alimony', 'usc_category');

            // Force template update if version is old or templates are empty
            $current_ver = get_post_meta($post_id, '_usc_template_version', true);
            if ($current_ver !== '16' || empty(get_post_meta($post_id, '_usc_calc_html', true)) || empty(get_post_meta($post_id, '_usc_calc_js', true))) {
                $defaults = usc_get_default_templates('alimony', $slug);
                update_post_meta($post_id, '_usc_calc_html', $defaults['html']);
                update_post_meta($post_id, '_usc_calc_css', $defaults['css']);
                update_post_meta($post_id, '_usc_calc_js', $defaults['js']);
                update_post_meta($post_id, '_usc_template_version', '16');
            }
        }

        // Create or Update Mortgage Calculator Page
        $new_mortgage_slug = 'mortgage-calculator-' . $slug;
        $mortgage_exists = usc_get_calculator_by_meta('mortgage', $slug);

        if (!$mortgage_exists) {
            $post_id = wp_insert_post([
                'post_title'   => 'Mortgage Calculator ' . $state['name'],
                'post_name'    => $new_mortgage_slug,
                'post_status'  => 'publish',
                'post_type'    => USC_CPT,
                'post_content' => usc_get_default_mortgage_article_content($state),
            ]);
            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, '_usc_calc_type', 'mortgage');
                update_post_meta($post_id, '_usc_state_slug', $slug);
                update_post_meta($post_id, '_usc_seo_title', 'Mortgage Calculator ' . $state['name'] . ' - Calfy');
                update_post_meta($post_id, '_usc_seo_desc', usc_get_default_mortgage_seo_desc($state));
                
                // Add default HTML, CSS, JS
                $defaults = usc_get_mortgage_templates($slug);
                update_post_meta($post_id, '_usc_calc_html', $defaults['html']);
                update_post_meta($post_id, '_usc_calc_css', $defaults['css']);
                update_post_meta($post_id, '_usc_calc_js', $defaults['js']);
                update_post_meta($post_id, '_usc_faqs', usc_get_default_mortgage_faqs($state));
                update_post_meta($post_id, '_usc_template_version', '16');
                wp_set_object_terms($post_id, 'mortgage', 'usc_category');
            }
        } else {
            // Post exists. Update if content is outdated or needs title/slug rename
            $post_id = $mortgage_exists->ID;

            // Re-publish if draft or trash
            $post_status = get_post_status($post_id);
            if ($post_status === 'trash' || $post_status === 'draft') {
                wp_update_post([
                    'ID'          => $post_id,
                    'post_status' => 'publish'
                ]);
            }

            $post_content = $mortgage_exists->post_content;
            $post_title = $mortgage_exists->post_title;
            $post_name = $mortgage_exists->post_name;
            
            $needs_update = false;
            $update_args = ['ID' => $post_id];
            
            $expected_title = 'Mortgage Calculator ' . $state['name'];
            
            if ($post_title !== $expected_title) {
                $update_args['post_title'] = $expected_title;
                $needs_update = true;
            }
            if ($post_name !== $new_mortgage_slug) {
                // Permanently delete any conflicting trashed posts first
                $conflicting_posts = get_posts([
                    'post_type'   => USC_CPT,
                    'post_status' => 'trash',
                    'name'        => $new_mortgage_slug,
                    'numberposts' => -1
                ]);
                foreach ($conflicting_posts as $cp) {
                    wp_delete_post($cp->ID, true);
                }
                $update_args['post_name'] = $new_mortgage_slug;
                $needs_update = true;
            }
            if (empty($post_content) || strpos($post_content, '<!-- usc-v5-article -->') === false) {
                $update_args['post_content'] = usc_get_default_mortgage_article_content($state);
                $needs_update = true;
                update_post_meta($post_id, '_usc_faqs', usc_get_default_mortgage_faqs($state));
            }
            
            if ($needs_update) {
                wp_update_post($update_args);
            }
            
            // Ensure state slug and category term is assigned
            update_post_meta($post_id, '_usc_calc_type', 'mortgage');
            update_post_meta($post_id, '_usc_state_slug', $slug);
            wp_set_object_terms($post_id, 'mortgage', 'usc_category');

            // Force template update if version is old or templates are empty
            $current_ver = get_post_meta($post_id, '_usc_template_version', true);
            if ($current_ver !== '16' || empty(get_post_meta($post_id, '_usc_calc_html', true)) || empty(get_post_meta($post_id, '_usc_calc_js', true))) {
                $defaults = usc_get_mortgage_templates($slug);
                update_post_meta($post_id, '_usc_calc_html', $defaults['html']);
                update_post_meta($post_id, '_usc_calc_css', $defaults['css']);
                update_post_meta($post_id, '_usc_calc_js', $defaults['js']);
                update_post_meta($post_id, '_usc_template_version', '16');
            }
        }
    }
}

/**
 * Shortcode for State Calculators Directory Page [usc_directory]
 */
add_shortcode('usc_directory', 'usc_render_directory_shortcode');
function usc_render_directory_shortcode($atts) {
    $atts = shortcode_atts([
        'type' => 'paycheck', // 'paycheck', 'child-support', 'alimony', 'property-tax', 'mortgage'
    ], $atts, 'usc_directory');

    $states = usc_get_states_data();
    $type = sanitize_key($atts['type']);

    $title = 'Calculators';
    if ($type === 'paycheck') $title = 'Paycheck Calculators';
    elseif ($type === 'child-support') $title = 'Child Support Calculators';
    elseif ($type === 'alimony') $title = 'Alimony Calculators';
    elseif ($type === 'property-tax') $title = 'Property Tax Calculators';
    elseif ($type === 'mortgage') $title = 'Mortgage Calculators';

    $desc = 'Select your state below to run calculations accurately.';
    if ($type === 'paycheck') $desc = 'Select your state below to estimate taxes and take-home pay accurately.';
    elseif ($type === 'child-support') $desc = 'Select your state below to estimate child support obligations accurately.';
    elseif ($type === 'alimony') $desc = 'Select your state below to estimate alimony and maintenance payments.';
    elseif ($type === 'property-tax') $desc = 'Select your state below to estimate property tax payments and exemptions.';
    elseif ($type === 'mortgage') $desc = 'Select your state below to calculate mortgage payments, amortization, and extra payments.';

    ob_start();
    ?>
    <div class="usc-dir-container">
        <div class="usc-dir-header">
            <h3>Explore <?php echo esc_html($title); ?> by State</h3>
            <p><?php echo esc_html($desc); ?></p>
        </div>
        <div class="usc-dir-grid">
            <?php foreach ($states as $slug => $state) : 
                $post_slug = $slug . '-' . $type . '-calculator';
                $post = get_page_by_path($post_slug, OBJECT, USC_CPT);
                $url = $post ? get_permalink($post->ID) : '#';
                ?>
                <a href="<?php echo esc_url($url); ?>" class="usc-dir-card">
                    <span class="usc-dir-flag">🇺🇸</span>
                    <span class="usc-dir-name"><?php echo esc_html($state['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Register Calculators Hub top-level menu and submenus
 */
add_action('admin_menu', 'usc_register_admin_submenus');
function usc_register_admin_submenus() {
    // Remove CPT standard sidebar menu page
    remove_menu_page('edit.php?post_type=' . USC_CPT);

    // Create custom main menu folder
    add_menu_page(
        'Calculators Hub',
        'Calculators Hub',
        'manage_options',
        'usc_calculators_hub',
        'usc_render_hub_dashboard',
        'dashicons-calculator',
        30
    );

    // Submenu 1: Dashboard
    add_submenu_page(
        'usc_calculators_hub',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'usc_calculators_hub',
        'usc_render_hub_dashboard'
    );

    // Submenu 2: Paycheck Calculators
    add_submenu_page(
        'usc_calculators_hub',
        'Paycheck Calculators',
        'Paycheck Calculators',
        'manage_options',
        'edit.php?post_type=' . USC_CPT . '&calc_type=paycheck'
    );

    // Submenu 3: Child Support Calculators
    add_submenu_page(
        'usc_calculators_hub',
        'Child Support Calculators',
        'Child Support Calculators',
        'manage_options',
        'edit.php?post_type=' . USC_CPT . '&calc_type=child-support'
    );

    // Submenu 4: Alimony Calculators
    add_submenu_page(
        'usc_calculators_hub',
        'Alimony Calculators',
        'Alimony Calculators',
        'manage_options',
        'edit.php?post_type=' . USC_CPT . '&calc_type=alimony'
    );

    // Submenu: Mortgage Calculators
    add_submenu_page(
        'usc_calculators_hub',
        'Mortgage Calculators',
        'Mortgage Calculators',
        'manage_options',
        'edit.php?post_type=' . USC_CPT . '&calc_type=mortgage'
    );

    // Submenu 5: Add New Calculator
    add_submenu_page(
        'usc_calculators_hub',
        'Add New',
        'Add New',
        'manage_options',
        'post-new.php?post_type=' . USC_CPT
    );

    // Submenu 5: Captured Leads list
    add_submenu_page(
        'usc_calculators_hub',
        'Captured Leads',
        'Captured Leads',
        'manage_options',
        'usc_captured_leads',
        'usc_render_leads_page'
    );

    // Submenu 6: Traffic/Usage Analytics
    add_submenu_page(
        'usc_calculators_hub',
        'Usage Analytics',
        'Usage Analytics',
        'manage_options',
        'usc_usage_analytics',
        'usc_render_usage_page'
    );

    // Submenu 7: Ads Settings
    add_submenu_page(
        'usc_calculators_hub',
        'Ads Settings',
        'Ads Settings',
        'manage_options',
        'usc_ads_settings',
        'usc_render_ads_settings_page'
    );
}

// Highlight the custom menu parent and correct submenu for post type screens
add_filter('parent_file', 'usc_admin_parent_menu_highlight');
function usc_admin_parent_menu_highlight($parent_file) {
    global $pagenow;
    if (is_admin()) {
        if (($pagenow === 'edit.php' || $pagenow === 'post-new.php' || $pagenow === 'post.php') && isset($_GET['post_type']) && $_GET['post_type'] === USC_CPT) {
            return 'usc_calculators_hub';
        }
        // Also if we are on edit screen of an existing post without post_type in GET
        if ($pagenow === 'post.php' && isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            if (get_post_type($post_id) === USC_CPT) {
                return 'usc_calculators_hub';
            }
        }
    }
    return $parent_file;
}

add_filter('submenu_file', 'usc_admin_submenu_menu_highlight', 10, 2);
function usc_admin_submenu_menu_highlight($submenu_file, $parent_file) {
    global $pagenow;
    if ($parent_file === 'usc_calculators_hub') {
        if ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === USC_CPT) {
            $calc_type = isset($_GET['calc_type']) ? sanitize_key($_GET['calc_type']) : '';
            if ($calc_type) {
                return 'edit.php?post_type=' . USC_CPT . '&calc_type=' . $calc_type;
            }
        }
        if ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === USC_CPT) {
            return 'post-new.php?post_type=' . USC_CPT;
        }
        if ($pagenow === 'post.php' && isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            if (get_post_type($post_id) === USC_CPT) {
                $calc_type = get_post_meta($post_id, '_usc_calc_type', true);
                if ($calc_type) {
                    return 'edit.php?post_type=' . USC_CPT . '&calc_type=' . $calc_type;
                }
            }
        }
    }
    return $submenu_file;
}

/**
 * Renders the Unified Admin Navigation Header
 */
function usc_render_admin_nav($active_tab) {
    ?>
    <div class="usc-admin-nav">
        <div class="usc-admin-nav-brand">
            <span class="dashicons dashicons-calculator" style="font-size: 20px; width: 20px; height: 20px; line-height: 20px; color: var(--usc-primary); margin-right: 8px;"></span>
            <span>USA State Calculators Hub</span>
        </div>
        <div class="usc-admin-nav-links">
            <a href="<?php echo admin_url('admin.php?page=usc_calculators_hub'); ?>" class="<?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
            <a href="<?php echo admin_url('edit.php?post_type=' . USC_CPT . '&calc_type=paycheck'); ?>" class="<?php echo $active_tab === 'paycheck' ? 'active' : ''; ?>">Paycheck Calculators</a>
            <a href="<?php echo admin_url('edit.php?post_type=' . USC_CPT . '&calc_type=child-support'); ?>" class="<?php echo $active_tab === 'child-support' ? 'active' : ''; ?>">Child Support Calculators</a>
            <a href="<?php echo admin_url('edit.php?post_type=' . USC_CPT . '&calc_type=alimony'); ?>" class="<?php echo $active_tab === 'alimony' ? 'active' : ''; ?>">Alimony Calculators</a>
            <a href="<?php echo admin_url('edit.php?post_type=' . USC_CPT . '&calc_type=mortgage'); ?>" class="<?php echo $active_tab === 'mortgage' ? 'active' : ''; ?>">Mortgage Calculators</a>
            <a href="<?php echo admin_url('admin.php?page=usc_captured_leads'); ?>" class="<?php echo $active_tab === 'leads' ? 'active' : ''; ?>">Captured Leads</a>
            <a href="<?php echo admin_url('admin.php?page=usc_usage_analytics'); ?>" class="<?php echo $active_tab === 'usage' ? 'active' : ''; ?>">Usage Analytics</a>
            <a href="<?php echo admin_url('admin.php?page=usc_ads_settings'); ?>" class="<?php echo $active_tab === 'ads' ? 'active' : ''; ?>">Ads Settings</a>
        </div>
    </div>
    <?php
}

/**
 * Renders the Native Ads Settings Page
 */
function usc_render_ads_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user.');
    }

    // Handle form submission
    if (isset($_POST['usc_save_ads_settings'])) {
        if (!isset($_POST['usc_ads_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['usc_ads_nonce'])), 'usc_save_ads')) {
            wp_die('Security check failed.');
        }

        $enabled = isset($_POST['usc_global_ads_enabled']) ? '1' : '0';
        update_option('usc_global_ads_enabled', $enabled);

        // Sanitize code only if user has unfiltered_html capability
        if (current_user_can('unfiltered_html')) {
            $code = isset($_POST['usc_global_ads_code']) ? wp_unslash($_POST['usc_global_ads_code']) : '';
            update_option('usc_global_ads_code', $code);
        }

        echo '<div class="notice notice-success is-dismissible"><p><strong>Settings saved successfully.</strong></p></div>';
    }

    $global_enabled = get_option('usc_global_ads_enabled', '1'); // Default to enabled
    $global_code = get_option('usc_global_ads_code', '');
    ?>
    <div class="usc-admin-wrap">

        <div class="usc-panel">
            <div class="usc-panel-header">
                <h2>Native Ads Configuration</h2>
            </div>
            <div class="usc-panel-content">
                <form method="post" action="">
                    <?php wp_nonce_field('usc_save_ads', 'usc_ads_nonce'); ?>

                    <div class="usc-meta-row" style="margin-bottom: 24px;">
                        <label for="usc_global_ads_enabled" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px;">
                            <input type="checkbox" name="usc_global_ads_enabled" id="usc_global_ads_enabled" value="1" <?php checked($global_enabled, '1'); ?> style="margin: 0; width: 16px; height: 16px;" />
                            <strong>Enable Native Ads Globally</strong>
                        </label>
                        <p class="description" style="margin-top: 6px; margin-left: 24px; color: var(--usc-text-muted);">
                            Toggle this checkbox to turn all native advertisements on or off across all paycheck and child support calculator pages instantly.
                        </p>
                    </div>

                    <div class="usc-meta-row" style="margin-bottom: 24px;">
                        <label for="usc_global_ads_code" style="font-size: 14px; font-weight: 700; margin-bottom: 8px; display: block;">Global Native Ads Script Code</label>
                        <p class="description" style="margin-bottom: 12px; color: var(--usc-text-muted);">
                            Paste your global AdSense, Ezoic, or custom advertising snippet here. This code will automatically render on the front-end template if global ads are enabled.
                        </p>
                        <textarea name="usc_global_ads_code" id="usc_global_ads_code" style="width: 100%; height: 180px; font-family: monospace; font-size: 13px; padding: 12px; border-radius: 8px; border: 1px solid var(--usc-border); box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);" placeholder="Paste your ad script code here..."><?php echo esc_textarea($global_code); ?></textarea>
                    </div>

                    <div class="usc-meta-row">
                        <button type="submit" name="usc_save_ads_settings" class="usc-btn usc-btn-primary" style="padding: 12px 24px; font-size: 14px;">
                            <span class="dashicons dashicons-saved" style="margin-top: 2px;"></span> Save Configurations
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Renders the Premium Calculators Hub Dashboard Page
 */
function usc_render_hub_dashboard() {
    global $wpdb;

    // Fetch metrics
    $total_leads = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}usc_leads"));
    $total_usage = intval($wpdb->get_var("SELECT SUM(count) FROM {$wpdb->prefix}usc_usage_stats"));

    // Recent Captured Leads
    $recent_leads = $wpdb->get_results("SELECT l.*, p.post_title FROM {$wpdb->prefix}usc_leads l LEFT JOIN {$wpdb->posts} p ON l.post_id = p.ID ORDER BY l.created_at DESC LIMIT 5", ARRAY_A);

    // Popular Calculators
    $popular_calcs = $wpdb->get_results("SELECT u.*, p.post_title, p.ID FROM {$wpdb->prefix}usc_usage_stats u LEFT JOIN {$wpdb->posts} p ON u.post_id = p.ID ORDER BY u.count DESC LIMIT 5", ARRAY_A);

    // Fetch dynamic categories
    $categories = get_terms(array(
        'taxonomy'   => 'usc_category',
        'hide_empty' => false,
    ));

    $cat_order = array('paycheck', 'child-support', 'alimony', 'mortgage', 'tax', 'auto-loan', 'insurance');
    if (!empty($categories) && !is_wp_error($categories)) {
        usort($categories, function($a, $b) use ($cat_order) {
            $pos_a = array_search($a->slug, $cat_order);
            $pos_b = array_search($b->slug, $cat_order);
            $pos_a = ($pos_a === false) ? 999 : $pos_a;
            $pos_b = ($pos_b === false) ? 999 : $pos_b;
            return $pos_a - $pos_b;
        });
    } else {
        $categories = array();
    }

    // Single query to fetch all calculators CPT
    $calcs_query = new WP_Query(array(
        'post_type'      => USC_CPT,
        'posts_per_page' => -1,
        'post_status'    => array('publish', 'draft', 'pending', 'private')
    ));
    $categorized_calcs = array();
    foreach ($categories as $cat) {
        $categorized_calcs[$cat->slug] = array();
    }

    if ($calcs_query->have_posts()) {
        while ($calcs_query->have_posts()) {
            $calcs_query->the_post();
            $pid = get_the_ID();
            $slug = get_post_field('post_name');
            $post_terms = wp_get_object_terms($pid, 'usc_category');
            
            $term_slug = 'uncategorized';
            if (!empty($post_terms) && !is_wp_error($post_terms)) {
                $term_slug = $post_terms[0]->slug;
            }
            
            $state_slug = get_post_meta($pid, '_usc_state_slug', true);
            if (empty($state_slug)) {
                $state_slug = $slug;
                $prefixes = array('paycheck-calculator-', 'child-support-calculator-', 'alimony-calculator-', 'mortgage-calculator-');
                $suffixes = array('-paycheck-calculator', '-child-support-calculator', '-alimony-calculator', '-mortgage-calculator', '-tax-calculator', '-auto-loan-calculator', '-insurance-calculator');
                $state_slug = str_replace($prefixes, '', $state_slug);
                $state_slug = str_replace($suffixes, '', $state_slug);
            }

            $categorized_calcs[$term_slug][$state_slug] = array(
                'id'     => $pid,
                'status' => get_post_status($pid),
                'url'    => get_permalink($pid),
                'title'  => get_the_title()
            );
        }
        wp_reset_postdata();
    }

    // Fetch usage stats
    $usage_stats_results = $wpdb->get_results("SELECT post_id, count FROM {$wpdb->prefix}usc_usage_stats", ARRAY_A);
    $usage_stats = array();
    if (!empty($usage_stats_results)) {
        foreach ($usage_stats_results as $row) {
            $usage_stats[intval($row['post_id'])] = intval($row['count']);
        }
    }

    // Fetch leads stats
    $leads_stats_results = $wpdb->get_results("SELECT post_id, COUNT(*) as count FROM {$wpdb->prefix}usc_leads GROUP BY post_id", ARRAY_A);
    $leads_stats = array();
    if (!empty($leads_stats_results)) {
        foreach ($leads_stats_results as $row) {
            $leads_stats[intval($row['post_id'])] = intval($row['count']);
        }
    }

    // Calculate aggregated metrics for each Category (Niche)
    $category_metrics = array();
    $count_paycheck = 0;
    $count_child_support = 0;
    $total_calcs_count = 0;
    
    foreach ($categories as $cat) {
        $cat_runs = 0;
        $cat_leads = 0;
        $cat_active_pages = 0;
        
        if (isset($categorized_calcs[$cat->slug])) {
            foreach ($categorized_calcs[$cat->slug] as $state_slug => $c) {
                $pid = $c['id'];
                $total_calcs_count++;
                if ($c['status'] === 'publish') {
                    $cat_active_pages++;
                }
                $cat_runs += isset($usage_stats[$pid]) ? $usage_stats[$pid] : 0;
                $cat_leads += isset($leads_stats[$pid]) ? $leads_stats[$pid] : 0;
            }
        }
        
        if ($cat->slug === 'paycheck') {
            $count_paycheck = $cat_active_pages;
        } elseif ($cat->slug === 'child-support') {
            $count_child_support = $cat_active_pages;
        }
        
        $conversion_rate = ($cat_runs > 0) ? ($cat_leads / $cat_runs) * 100 : 0;
        
        $category_metrics[$cat->slug] = array(
            'name'         => $cat->name,
            'runs'         => $cat_runs,
            'leads'        => $cat_leads,
            'active_pages' => $cat_active_pages,
            'conv_rate'    => $conversion_rate
        );
    }
    
    $total_calcs = $count_paycheck + $count_child_support;
    $states = usc_get_states_data();
    ?>
    <div class="usc-admin-wrap">

        <!-- Premium Mockup Header Banner -->
        <div class="usc-dashboard-banner-new">
            <div class="usc-banner-left">
                <div class="usc-banner-title-row">
                    <span class="usc-banner-icon">🔢</span>
                    <h1>USA State Calculators Hub</h1>
                </div>
                <div class="usc-banner-status-checks">
                    <span>FICA Calculations <span class="usc-chk-icon">✓</span></span>
                    <span class="usc-divider">|</span>
                    <span>Filing Status <span class="usc-chk-icon">✓</span></span>
                    <span class="usc-divider">|</span>
                    <span>SEO Schema <span class="usc-chk-icon">✓</span></span>
                    <span class="usc-divider">|</span>
                    <span>Dynamic Niches <span class="usc-chk-icon">✓</span></span>
                </div>
            </div>
            <div class="usc-banner-right">
                <span class="usc-banner-tool-badge"><?php echo $total_calcs_count; ?> Calculators</span>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?action=usc_export_leads'), 'usc_export_leads_nonce')); ?>" class="usc-banner-btn-white">
                    <span class="dashicons dashicons-download"></span> Export Leads
                </a>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?action=usc_sync_pages'), 'usc_sync_pages_nonce')); ?>" class="usc-banner-btn-purple" onclick="return confirm('Are you sure you want to synchronize all calculators?');">
                    <span class="dashicons dashicons-update"></span> Sync Pages
                </a>
            </div>
        </div>

        <!-- Mockup Metrics Grid -->
        <div class="usc-metrics-grid-new">
            <div class="usc-metric-card-new">
                <div class="usc-metric-card-header">
                    <span class="usc-metric-icon icon-blue">🇺🇸</span>
                </div>
                <div class="usc-metric-card-body">
                    <h2>50</h2>
                    <p>ACTIVE STATES</p>
                </div>
            </div>
            <div class="usc-metric-card-new">
                <div class="usc-metric-card-header">
                    <span class="usc-metric-icon icon-yellow">📊</span>
                </div>
                <div class="usc-metric-card-body">
                    <h2><?php echo $total_calcs_count; ?></h2>
                    <p>TOTAL CALCULATORS</p>
                </div>
            </div>
            <div class="usc-metric-card-new">
                <div class="usc-metric-card-header">
                    <span class="usc-metric-icon icon-orange">✉️</span>
                </div>
                <div class="usc-metric-card-body">
                    <h2><?php echo number_format($total_leads); ?></h2>
                    <p>CAPTURED LEADS</p>
                </div>
            </div>
            <div class="usc-metric-card-new">
                <div class="usc-metric-card-header">
                    <span class="usc-metric-icon icon-green">🚀</span>
                </div>
                <div class="usc-metric-card-body">
                    <h2><?php echo number_format($total_usage); ?></h2>
                    <p>TOTAL RUNS</p>
                </div>
            </div>
        </div>

        <!-- Horizontal Status Check Bar -->
        <div class="usc-status-check-bar">
            <div class="usc-status-check-item">
                <span class="usc-dot-light green"></span>
                <span class="dashicons dashicons-category"></span>
                <span>CPT Registry: Connected</span>
            </div>
            <div class="usc-status-check-item">
                <span class="usc-dot-light green"></span>
                <span class="dashicons dashicons-database"></span>
                <span>Leads Database: Connected</span>
            </div>
            <div class="usc-status-check-item">
                <span class="usc-dot-light green"></span>
                <span class="dashicons dashicons-shield"></span>
                <span>AJAX Handler: Secure</span>
            </div>
        </div>

        <!-- Search Box (Hinglish) -->
        <div class="usc-search-box-large">
            <span class="dashicons dashicons-search large-search-icon"></span>
            <input type="text" id="usc-calc-search" placeholder="State ya niche dhundho... (California, Paycheck, Texas)" onkeyup="filterCalculators()" />
        </div>

        <!-- Category Filter Pills Navigation -->
        <div class="usc-category-pills-wrap">
            <button class="usc-category-pill active" data-category-slug="all" onclick="switchCategoryTab('all', this)">All Tools</button>
            <?php foreach ($categories as $cat) : 
                $metrics = $category_metrics[$cat->slug];
                ?>
                <button class="usc-category-pill" data-category-slug="<?php echo esc_attr($cat->slug); ?>" onclick="switchCategoryTab('<?php echo esc_attr($cat->slug); ?>', this)">
                    <?php echo esc_html($cat->name); ?> (<?php echo $metrics['active_pages']; ?>)
                </button>
            <?php endforeach; ?>
        </div>

        <!-- List View of Calculators -->
        <div class="usc-panel usc-list-table-panel" style="margin-top: 24px; box-shadow: 0 4px 20px -2px rgba(15,23,42,0.05);">
            <div class="usc-panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 style="font-size:16px; font-weight:700; color:var(--usc-secondary);">Calculators Registry</h2>
                <span class="usc-banner-tool-badge" id="usc-visible-count" style="background: var(--usc-bg); color: var(--usc-secondary); border: 1px solid var(--usc-border);">0 items</span>
            </div>
            <div class="usc-panel-content" style="padding: 0;">
                <table class="usc-custom-table" id="usc-calc-list-table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>State / Calculator</th>
                            <th>Niche Category</th>
                            <th>Status</th>
                            <th>Calculations Run</th>
                            <th>Captured Leads</th>
                            <th style="width: 120px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usc-calc-list-tbody">
                        <?php 
                        $has_cards = false;
                        foreach ($categories as $cat) : 
                            if (isset($categorized_calcs[$cat->slug])) :
                                foreach ($categorized_calcs[$cat->slug] as $state_slug => $c) :
                                    $has_cards = true;
                                    $pid = $c['id'];
                                    $status = $c['status'];
                                    $url = $c['url'];
                                    $title = $c['title'];
                                    $runs = isset($usage_stats[$pid]) ? $usage_stats[$pid] : 0;
                                    $leads = isset($leads_stats[$pid]) ? $leads_stats[$pid] : 0;
                                    
                                    $icon = '🔢';
                                    if ($cat->slug === 'paycheck') $icon = '💵';
                                    elseif ($cat->slug === 'child-support') $icon = '👪';
                                    elseif ($cat->slug === 'alimony') $icon = '⚖️';
                                    elseif ($cat->slug === 'mortgage') $icon = '🏠';
                                    elseif ($cat->slug === 'tax') $icon = '📈';
                                    elseif ($cat->slug === 'auto-loan') $icon = '🚗';
                                    elseif ($cat->slug === 'insurance') $icon = '🛡️';
                                    ?>
                                    <tr class="usc-calc-row" data-name="<?php echo esc_attr($title); ?>" data-state="<?php echo esc_attr($state_slug); ?>" data-category="<?php echo esc_attr($cat->slug); ?>">
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <span class="usc-card-icon-wrap" style="width:36px; height:36px; font-size:18px; border-radius:8px; margin:0; display:flex; align-items:center; justify-content:center;"><?php echo $icon; ?></span>
                                                <div>
                                                    <strong style="font-size: 14px; color: var(--usc-secondary);"><?php echo esc_html($title); ?></strong>
                                                    <div style="font-size: 11px; color: var(--usc-text-muted); margin-top: 2px;">ID: <?php echo $pid; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="usc-tag-badge"><?php echo esc_html(strtoupper($cat->name)); ?></span>
                                        </td>
                                        <td>
                                            <span class="usc-card-status status-<?php echo esc_attr($status); ?>"><?php echo esc_html(ucfirst($status)); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($runs); ?> runs</strong>
                                        </td>
                                        <td>
                                            <span class="usc-badge-lead" style="font-size: 11px; padding: 3px 8px;"><?php echo number_format($leads); ?> leads</span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 8px; justify-content: center;">
                                                <a href="<?php echo esc_url(get_edit_post_link($pid)); ?>" class="usc-footer-action-btn edit" title="Edit Settings"><span class="dashicons dashicons-edit"></span></a>
                                                <a href="<?php echo esc_url($url); ?>" target="_blank" class="usc-footer-action-btn view" title="View Live"><span class="dashicons dashicons-visibility"></span></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            endif;
                        endforeach;
                        
                        if (!$has_cards) : ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 50px; color: var(--usc-text-muted);">
                                    No calculators generated yet. Click "Sync Pages" in the banner to create them.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dynamic Category-wise Analytics Panel -->
        <div class="usc-panel usc-analytics-panel" style="margin-top: 40px; box-shadow: 0 4px 20px -2px rgba(15,23,42,0.05);">
            <div class="usc-panel-header">
                <h2 style="font-size:16px; font-weight:700; color:var(--usc-secondary);">Niche Performance & Conversion Analytics</h2>
            </div>
            <div class="usc-panel-content" style="padding: 0;">
                <table class="usc-custom-table">
                    <thead>
                        <tr>
                            <th>Niche / Category</th>
                            <th>Active Pages</th>
                            <th>Total Calculations Run</th>
                            <th>Captured Leads</th>
                            <th>Conversion Rate</th>
                            <th style="width: 300px;">Performance Strength</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($category_metrics as $slug => $metric) : 
                            $strength_pct = min(100, $metric['conv_rate'] * 5);
                            $strength_color = 'var(--usc-primary)';
                            if ($metric['conv_rate'] > 15) $strength_color = 'var(--usc-success)';
                            elseif ($metric['conv_rate'] < 5) $strength_color = 'var(--usc-text-muted)';
                            ?>
                            <tr>
                                <td>
                                    <strong style="font-size:14px; color:var(--usc-secondary);"><?php echo esc_html($metric['name']); ?></strong>
                                </td>
                                <td>
                                    <span class="usc-runs-badge" style="background:#f1f5f9; color:#475569; border-radius:6px; font-size:11px; padding:3px 8px;"><?php echo $metric['active_pages']; ?> / 50 active</span>
                                </td>
                                <td><strong><?php echo number_format($metric['runs']); ?> runs</strong></td>
                                <td>
                                    <span class="usc-badge-lead" style="font-size:11px; padding:3px 8px;"><?php echo number_format($metric['leads']); ?> leads</span>
                                </td>
                                <td>
                                    <strong style="color: <?php echo $strength_color; ?>; font-size:14px;"><?php echo number_format($metric['conv_rate'], 2); ?>%</strong>
                                </td>
                                <td>
                                    <div class="usc-strength-bar-wrap" style="background:#e2e8f0; border-radius:6px; height:8px; overflow:hidden; width:100%;">
                                        <div class="usc-strength-bar" style="background:<?php echo $strength_color; ?>; width:<?php echo $strength_pct; ?>%; height:100%; transition: width 0.3s ease;"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            function switchCategoryTab(categorySlug, button) {
                // Remove active class from all pills
                document.querySelectorAll(".usc-category-pill").forEach(pill => pill.classList.remove("active"));
                // Add active class to clicked pill
                button.classList.add("active");
                
                // Filter the calculators grid
                filterCalculators();
            }

            function filterCalculators() {
                var searchInput = document.getElementById("usc-calc-search");
                var filter = searchInput.value.toLowerCase();
                var activePill = document.querySelector(".usc-category-pill.active");
                var activeCategory = activePill ? activePill.getAttribute("data-category-slug") : "all";
                
                var rows = document.querySelectorAll(".usc-calc-row");
                var visibleCount = 0;
                rows.forEach(function(row) {
                    var name = row.getAttribute("data-name").toLowerCase();
                    var category = row.getAttribute("data-category");
                    
                    var matchesSearch = name.indexOf(filter) > -1;
                    var matchesCategory = (activeCategory === "all") || (category === activeCategory);
                    
                    if (matchesSearch && matchesCategory) {
                        row.style.display = "";
                        visibleCount++;
                    } else {
                        row.style.display = "none";
                    }
                });
                
                var badge = document.getElementById("usc-visible-count");
                if (badge) {
                    badge.innerText = visibleCount + " items";
                }
            }

            // Run on load to set count
            document.addEventListener("DOMContentLoaded", function() {
                filterCalculators();
            });
        </script>

        <!-- Dashboard Columns -->
        <div class="usc-dashboard-row">
            <!-- Recent Leads Panel -->
            <div class="usc-dashboard-col">
                <div class="usc-panel" style="box-shadow: 0 4px 20px -2px rgba(15,23,42,0.05);">
                    <div class="usc-panel-header">
                        <h2 style="font-size:15px; font-weight:700; color:var(--usc-secondary);">Recent Captured Leads</h2>
                        <a href="<?php echo admin_url('admin.php?page=usc_captured_leads'); ?>" class="usc-btn usc-btn-white" style="padding: 4px 8px; font-size:12px; border-radius:6px;">View All</a>
                    </div>
                    <div class="usc-panel-content" style="padding: 0;">
                        <?php if (empty($recent_leads)) : ?>
                            <p style="padding: 24px; color: var(--usc-text-muted); text-align: center; margin: 0;">No leads captured yet.</p>
                        <?php else : ?>
                            <table class="usc-custom-table">
                                <thead>
                                    <tr>
                                        <th>State</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_leads as $lead) : ?>
                                        <tr>
                                            <td><strong><?php echo esc_html(str_replace([' Paycheck Calculator', ' Child Support Calculator', ' Alimony Calculator', ' Mortgage Calculator', 'Mortgage Calculator '], '', $lead['post_title'])); ?></strong></td>
                                            <td><?php echo esc_html($lead['name']); ?></td>
                                            <td><a href="mailto:<?php echo esc_attr($lead['email']); ?>"><?php echo esc_html($lead['email']); ?></a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Popular Calculators Panel -->
            <div class="usc-dashboard-col">
                <div class="usc-panel" style="box-shadow: 0 4px 20px -2px rgba(15,23,42,0.05);">
                    <div class="usc-panel-header">
                        <h2 style="font-size:15px; font-weight:700; color:var(--usc-secondary);">Most Popular Calculators</h2>
                        <a href="<?php echo admin_url('admin.php?page=usc_usage_analytics'); ?>" class="usc-btn usc-btn-white" style="padding: 4px 8px; font-size:12px; border-radius:6px;">View All</a>
                    </div>
                    <div class="usc-panel-content" style="padding: 0;">
                        <?php if (empty($popular_calcs)) : ?>
                            <p style="padding: 24px; color: var(--usc-text-muted); text-align: center; margin: 0;">No usage recorded yet.</p>
                        <?php else : ?>
                            <table class="usc-custom-table">
                                <thead>
                                    <tr>
                                        <th>Calculator Name</th>
                                        <th>Run Count</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($popular_calcs as $use) : ?>
                                        <tr>
                                            <td><strong><?php echo esc_html($use['post_title']); ?></strong></td>
                                            <td><span class="usc-badge-run"><?php echo esc_html($use['count']); ?> runs</span></td>
                                            <td><a href="<?php echo esc_url(get_edit_post_link($use['ID'])); ?>" class="usc-btn usc-btn-white" style="padding: 4px 8px; font-size:11px; border-radius:6px;">Edit</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Renders Captured Leads Table
 */
function usc_render_leads_page() {
    global $wpdb;
    $leads = $wpdb->get_results("SELECT l.*, p.post_title FROM {$wpdb->prefix}usc_leads l LEFT JOIN {$wpdb->posts} p ON l.post_id = p.ID ORDER BY l.created_at DESC", ARRAY_A);
    ?>
    <div class="usc-admin-wrap">

        <div class="usc-panel">
            <div class="usc-panel-header" style="border-bottom: none; padding-bottom: 0;">
                <div>
                    <h2 style="font-size:22px; margin-bottom: 5px;">Captured Leads Registry</h2>
                    <p style="margin: 0; color: var(--usc-text-muted); font-size:13px; font-weight: normal;">List of users who requested estimate reports before printing or downloading paycheck/child support details.</p>
                </div>
                <div>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?action=usc_export_leads'), 'usc_export_leads_nonce')); ?>" class="usc-btn usc-btn-primary">
                        <span class="dashicons dashicons-download"></span> Export to CSV
                    </a>
                </div>
            </div>
            
            <div class="usc-panel-content" style="padding: 24px 0 0 0;">
                <table class="usc-custom-table" style="border-top: 1px solid var(--usc-border);">
                    <thead>
                        <tr>
                            <th style="width: 70px; text-align: center;">ID</th>
                            <th>Calculator State</th>
                            <th>User Name</th>
                            <th>User Email</th>
                            <th>Submitted On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leads)) : ?>
                            <tr><td colspan="5" style="text-align: center; padding: 30px; color: var(--usc-text-muted);">No leads captured yet.</td></tr>
                        <?php else : ?>
                            <?php foreach ($leads as $lead) : ?>
                                <tr>
                                    <td style="text-align: center; color: var(--usc-text-muted); font-weight: 600;"><?php echo esc_html($lead['id']); ?></td>
                                    <td><strong><?php echo esc_html($lead['post_title'] ?: 'State Calculator'); ?></strong></td>
                                    <td><span class="usc-badge-lead" style="background:#e0f2fe; color:#0369a1;"><?php echo esc_html($lead['name']); ?></span></td>
                                    <td><a href="mailto:<?php echo esc_attr($lead['email']); ?>" style="text-decoration:none; color: var(--usc-primary); font-weight:600;"><?php echo esc_html($lead['email']); ?></a></td>
                                    <td style="color: var(--usc-text-muted);"><?php echo esc_html($lead['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Renders Usage Analytics Table
 */
function usc_render_usage_page() {
    global $wpdb;
    $usage = $wpdb->get_results("SELECT u.*, p.post_title FROM {$wpdb->prefix}usc_usage_stats u LEFT JOIN {$wpdb->posts} p ON u.post_id = p.ID ORDER BY u.count DESC", ARRAY_A);
    ?>
    <div class="usc-admin-wrap">

        <div class="usc-panel">
            <div class="usc-panel-header" style="border-bottom: none; padding-bottom: 0;">
                <div>
                    <h2 style="font-size:22px; margin-bottom: 5px;">Usage Analytics & Traffic logs</h2>
                    <p style="margin: 0; color: var(--usc-text-muted); font-size:13px; font-weight: normal;">Analytics showing total calculations triggered per calculator state page.</p>
                </div>
            </div>
            
            <div class="usc-panel-content" style="padding: 24px 0 0 0;">
                <table class="usc-custom-table" style="border-top: 1px solid var(--usc-border);">
                    <thead>
                        <tr>
                            <th>State Calculator</th>
                            <th>Total Run Count</th>
                            <th>Last Run Date</th>
                            <th style="width: 100px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usage)) : ?>
                            <tr><td colspan="4" style="text-align: center; padding: 30px; color: var(--usc-text-muted);">No logs recorded yet.</td></tr>
                        <?php else : ?>
                            <?php foreach ($usage as $use) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($use['post_title'] ?: 'State Calculator'); ?></strong></td>
                                    <td><span class="usc-badge-run"><?php echo esc_html($use['count']); ?> calculations</span></td>
                                    <td style="color: var(--usc-text-muted);"><?php echo esc_html($use['last_used']); ?></td>
                                    <td style="text-align: center;"><a href="<?php echo esc_url(get_edit_post_link($use['post_id'])); ?>" class="usc-btn usc-btn-white" style="padding: 4px 8px; font-size:12px;">Edit Settings</a></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Override the theme's AJAX Live Search to merge state calculators
 */
add_action('init', 'usc_override_theme_live_search', 20);
function usc_override_theme_live_search() {
    remove_action('wp_ajax_fxtool_live_search', 'fxtool_ajax_live_search');
    remove_action('wp_ajax_nopriv_fxtool_live_search', 'fxtool_ajax_live_search');
    
    add_action('wp_ajax_fxtool_live_search', 'usc_custom_ajax_live_search');
    add_action('wp_ajax_nopriv_fxtool_live_search', 'usc_custom_ajax_live_search');
}

function usc_custom_ajax_live_search() {
    check_ajax_referer('fxtool_search', 'nonce');

    $q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    if (strlen($q) < 2) {
        wp_send_json_success(array('items' => array()));
    }

    $out = array();

    // 1. Search in our custom CPT (State calculators)
    $calc_query = new WP_Query(array(
        'post_type'      => USC_CPT,
        'posts_per_page' => 10,
        'post_status'    => 'publish',
        's'              => $q,
    ));

    if ($calc_query->have_posts()) {
        while ($calc_query->have_posts()) {
            $calc_query->the_post();
            $pid = get_the_ID();
            $calc_type = get_post_meta($pid, '_usc_calc_type', true);
            $icon = '🔢';
            if ($calc_type === 'paycheck') $icon = '💵';
            elseif ($calc_type === 'child-support') $icon = '👪';
            elseif ($calc_type === 'alimony') $icon = '⚖️';
            $out[] = array(
                'name' => get_the_title(),
                'url'  => get_permalink(),
                'desc' => wp_trim_words(get_the_excerpt(), 10, '…'),
                'cat'  => $calc_type,
                'icon' => $icon,
            );
        }
        wp_reset_postdata();
    }

    // 2. Query theme's directory items as well if we have space
    if (count($out) < 10 && function_exists('fxtool_get_directory_items')) {
        $q_lower = strtolower($q);
        $items   = fxtool_get_directory_items();
        foreach ($items as $item) {
            $name = strtolower($item['name']);
            $desc = strtolower($item['desc'] ?? '');
            $slug = strtolower($item['slug'] ?? '');
            if (
                false !== strpos($name, $q_lower) ||
                false !== strpos($desc, $q_lower) ||
                false !== strpos($slug, $q_lower)
            ) {
                // Ensure no duplicate URL
                $exists = false;
                foreach ($out as $o) {
                    if ($o['url'] === $item['url']) {
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $out[] = array(
                        'name' => $item['name'],
                        'url'  => $item['url'],
                        'desc' => $item['desc'] ? wp_trim_words($item['desc'], 10, '…') : '',
                        'cat'  => $item['category'],
                        'icon' => $item['icon'] ?? '🔢',
                    );
                }
            }
            if (count($out) >= 10) break;
        }
    }

    wp_send_json_success(array('items' => $out));
}

/**
 * Helper to register or retrieve the illustration attachment ID in the Media Library
 */
function usc_get_or_create_illustration_attachment($type) {
    $option_name = 'usc_attachment_id_' . $type;
    $attachment_id = get_option($option_name);
    if ($attachment_id && wp_get_attachment_url($attachment_id)) {
        return (int) $attachment_id;
    }

    $filename = ($type === 'paycheck') ? 'paycheck-illustration.png' : 'child-support-illustration.png';
    $file_path = USC_PATH . 'public/assets/images/' . $filename;
    
    if (!file_exists($file_path)) {
        return 0;
    }

    $wp_upload_dir = wp_upload_dir();
    if (!empty($wp_upload_dir['error'])) {
        return 0;
    }

    $target_path = $wp_upload_dir['path'] . '/' . $filename;
    
    // Copy the file from plugin assets to WP uploads directory
    if (!copy($file_path, $target_path)) {
        return 0;
    }

    $filetype = wp_check_filetype($filename, null);
    $attachment = [
        'guid'           => $wp_upload_dir['url'] . '/' . basename($filename),
        'post_mime_type' => $filetype['type'],
        'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
        'post_content'   => '',
        'post_status'    => 'inherit'
    ];

    $attach_id = wp_insert_attachment($attachment, $target_path);
    if (is_wp_error($attach_id)) {
        return 0;
    }
    
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attach_data = wp_generate_attachment_metadata($attach_id, $target_path);
    wp_update_attachment_metadata($attach_id, $attach_data);

    update_option($option_name, $attach_id);
    return (int) $attach_id;
}

