<?php
/**
 * Plugin Name: USA State-Wise Income & Property Tax Calculator
 * Plugin URI: #
 * Description: Premium SEO-optimized income, property, and sales tax calculators for all 50 US states. Auto-creates CPT pages with state-specific content and customizable HTML/CSS/JS editors.
 * Version: 1.1.0
 * Author: AI Assistant
 * Text Domain: usa-state-tax-calculators
 */

if (!defined('ABSPATH')) exit;

// Force error logging to a local file in the plugin directory to capture any activation or runtime fatal errors.
ini_set('log_errors', '1');
ini_set('error_log', dirname(__FILE__) . '/activation-error.log');


define('UST_PATH', plugin_dir_path(__FILE__));
define('UST_URL', plugin_dir_url(__FILE__));
define('UST_VERSION', '1.1.0');
define('UST_CPT', 'ust_calculator');

// Include components
require_once UST_PATH . 'includes/class-ust-cpt.php';
require_once UST_PATH . 'includes/class-ust-metaboxes.php';
require_once UST_PATH . 'includes/class-ust-seo.php';
require_once UST_PATH . 'data/income-tax.php';
require_once UST_PATH . 'data/property-tax.php';
require_once UST_PATH . 'data/sales-tax.php';
require_once UST_PATH . 'data/other-tax.php';
require_once UST_PATH . 'data/default-content.php';
require_once UST_PATH . 'data/default-templates.php';

// Initialize core classes
add_action('plugins_loaded', 'ust_init_plugin');
function ust_init_plugin() {
    new UST_CPT();
    new UST_Metaboxes();
    new UST_SEO();
}

// Auto-sync pages on admin load if transient is not set
add_action('admin_init', 'ust_admin_sync_pages');
function ust_admin_sync_pages() {
    if (!current_user_can('manage_options')) return;
    if (get_transient('ust_pages_generated_v13')) return;
    set_transient('ust_pages_generated_v13', true, DAY_IN_SECONDS);
    ust_auto_generate_state_pages();
    flush_rewrite_rules();
}

// Run taxonomy creation and migration on admin load
add_action('admin_init', 'ust_admin_init_taxonomy_migration', 11);
function ust_admin_init_taxonomy_migration() {
    if (!current_user_can('manage_options')) return;
    
    $categories = array(
        'income-tax'   => 'Income Tax',
        'property-tax' => 'Property Tax',
        'sales-tax'    => 'Sales Tax',
        'other'        => 'Other'
    );

    foreach ($categories as $slug => $name) {
        if (!term_exists($slug, 'ust_category')) {
            wp_insert_term($name, 'ust_category', array('slug' => $slug));
        }
    }

    // Migrate existing posts from meta to taxonomy if any
    $posts = get_posts(array(
        'post_type'      => UST_CPT,
        'posts_per_page' => -1,
        'post_status'    => array('publish', 'draft', 'pending', 'private'),
        'tax_query'      => array(
            array(
                'taxonomy' => 'ust_category',
                'field'    => 'slug',
                'terms'    => array_keys($categories),
                'operator' => 'NOT IN'
            )
        )
    ));

    if (!empty($posts)) {
        foreach ($posts as $p) {
            $calc_type = get_post_meta($p->ID, '_ust_calc_type', true);
            if ($calc_type && isset($categories[$calc_type])) {
                wp_set_object_terms($p->ID, $calc_type, 'ust_category');
            }
        }
    }
}

// Enqueue styles/scripts on front-end + localize AJAX nonce
add_action('wp_enqueue_scripts', 'ust_enqueue_assets');
function ust_enqueue_assets() {
    if (is_singular(UST_CPT)) {
        wp_enqueue_style('ust-style', UST_URL . 'public/assets/css/style.css', [], UST_VERSION);
        wp_enqueue_script('ust-nonce-init', UST_URL . 'public/assets/js/nonce.js', [], UST_VERSION, true);
        wp_localize_script('ust-nonce-init', 'ustAjax', [
            'ajaxurl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('ust_frontend_nonce'),
        ]);
    }
}

// Enqueue styles in WP admin panel
add_action('admin_enqueue_scripts', 'ust_enqueue_admin_assets');
function ust_enqueue_admin_assets($hook) {
    global $post;
    $post_type = $post ? $post->post_type : '';
    if (empty($post_type) && isset($_GET['post_type'])) {
        $post_type = sanitize_key($_GET['post_type']);
    }
    $page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
    if ($post_type === UST_CPT || strpos($hook, 'ust_') !== false || strpos($page, 'ust_') !== false) {
        $css_file = UST_PATH . 'public/assets/css/admin-style.css';
        $ver = file_exists($css_file) ? filemtime($css_file) : UST_VERSION;
        wp_enqueue_style('ust-admin-style', UST_URL . 'public/assets/css/admin-style.css', [], $ver);
    }
}

// Hook CSV exporter
add_action('admin_init', 'ust_export_leads_csv');
function ust_export_leads_csv() {
    if (isset($_GET['action']) && $_GET['action'] === 'ust_export_leads') {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized user.');
        }
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'ust_export_leads_nonce')) {
            wp_die('Security check failed.');
        }
        
        global $wpdb;
        $leads = $wpdb->get_results("SELECT l.id, p.post_title, l.name, l.email, l.created_at FROM {$wpdb->prefix}ust_leads l LEFT JOIN {$wpdb->posts} p ON l.post_id = p.ID ORDER BY l.created_at DESC", ARRAY_A);
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=captured_tax_leads_' . date('Y-m-d') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['Lead ID', 'State Tax Calculator Name', 'User Name', 'User Email', 'Submitted On']);
        
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
add_action('admin_init', 'ust_handle_sync_pages_action');
function ust_handle_sync_pages_action() {
    if (isset($_GET['action']) && $_GET['action'] === 'ust_sync_pages') {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized user.');
        }
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), 'ust_sync_pages_nonce')) {
            wp_die('Security check failed.');
        }
        
        delete_transient('ust_pages_generated_v1');
        delete_transient('ust_pages_generated_v2');
        delete_transient('ust_pages_generated_v4');
        delete_transient('ust_pages_generated_v6');
        delete_transient('ust_pages_generated_v7');
        delete_transient('ust_pages_generated_v8');
        delete_transient('ust_pages_generated_v9');
        delete_transient('ust_pages_generated_v10');
        delete_transient('ust_pages_generated_v11');
        delete_transient('ust_pages_generated_v12');
        delete_transient('ust_pages_generated_v13');
        ust_auto_generate_state_pages();
        flush_rewrite_rules();
        
        wp_safe_redirect(add_query_arg('ust_message', 'sync_success', admin_url('admin.php?page=ust_calculators_hub')));
        exit;
    }
}

// Display sync success notice
add_action('admin_notices', 'ust_admin_sync_notice');
function ust_admin_sync_notice() {
    if (isset($_GET['ust_message']) && $_GET['ust_message'] === 'sync_success') {
        echo '<div class="notice notice-success is-dismissible"><p><strong>Tax calculators and state pages successfully synchronized!</strong></p></div>';
    }
}

// Activation Hook
register_activation_hook(__FILE__, 'ust_activate_plugin');
function ust_activate_plugin() {
    global $wpdb;
    $charset = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Create Leads table
    $t_leads = $wpdb->prefix . 'ust_leads';
    dbDelta("CREATE TABLE $t_leads (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        name varchar(200) NOT NULL,
        email varchar(200) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset;");

    // Create Usage Stats table
    $t_usage = $wpdb->prefix . 'ust_usage_stats';
    dbDelta("CREATE TABLE $t_usage (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        post_id bigint(20) NOT NULL,
        count bigint(20) DEFAULT 0 NOT NULL,
        last_used datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY post_id  (post_id)
    ) $charset;");

    // Remove CPT registration and flush rules from activation hook to avoid early registry conflicts.
    // Instead, delete the transient to trigger generation and rule flushing on the next admin page load (admin_init).
    delete_transient('ust_pages_generated_v13');
}

// Deactivation Hook
register_deactivation_hook(__FILE__, 'ust_deactivate_plugin');
function ust_deactivate_plugin() {
    flush_rewrite_rules();
}

// AJAX: Save Lead
add_action('wp_ajax_ust_submit_lead', 'ust_ajax_submit_lead');
add_action('wp_ajax_nopriv_ust_submit_lead', 'ust_ajax_submit_lead');
function ust_ajax_submit_lead() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ust_frontend_nonce')) {
        wp_send_json_error('Security check failed.', 403);
    }

    $ip_key = 'ust_lead_rate_' . md5($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $attempts = (int) get_transient($ip_key);
    if ($attempts >= 3) {
        wp_send_json_error('Too many submissions. Please try again later.', 429);
    }
    set_transient($ip_key, $attempts + 1, HOUR_IN_SECONDS);

    $post_id = intval($_POST['post_id'] ?? 0);
    $name    = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $email   = sanitize_email(wp_unslash($_POST['email'] ?? ''));

    if (!$post_id || empty($name) || empty($email)) {
        wp_send_json_error('Invalid input data.');
    }

    if (!is_email($email)) {
        wp_send_json_error('Invalid email address.');
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== UST_CPT || $post->post_status !== 'publish') {
        wp_send_json_error('Invalid calculator reference.');
    }

    global $wpdb;
    $wpdb->insert(
        $wpdb->prefix . 'ust_leads',
        ['post_id' => $post_id, 'name' => $name, 'email' => $email],
        ['%d', '%s', '%s']
    );
    wp_send_json_success('Lead saved successfully.');
}

// AJAX: Track Usage Count
add_action('wp_ajax_ust_track_usage', 'ust_ajax_track_usage');
add_action('wp_ajax_nopriv_ust_track_usage', 'ust_ajax_track_usage');
function ust_ajax_track_usage() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'ust_frontend_nonce')) {
        wp_send_json_error('Security check failed.', 403);
    }

    $post_id = intval($_POST['post_id'] ?? 0);
    if (!$post_id) {
        wp_send_json_error();
    }

    $post = get_post($post_id);
    if (!$post || $post->post_type !== UST_CPT) {
        wp_send_json_error();
    }

    global $wpdb;
    $table = $wpdb->prefix . 'ust_usage_stats';
    $wpdb->query($wpdb->prepare(
        "INSERT INTO $table (post_id, count, last_used)
         VALUES (%d, 1, NOW())
         ON DUPLICATE KEY UPDATE count = count + 1, last_used = NOW()",
         $post_id
    ));
    wp_send_json_success();
}

/**
 * Helper function to retrieve calculator by meta keys
 */
function ust_get_calculator_by_meta($calc_type, $state_slug) {
    global $wpdb;
    
    // Look up by meta fields first
    $post_id = $wpdb->get_var($wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
         INNER JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_ust_calc_type' AND pm1.meta_value = %s
         INNER JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_ust_state_slug' AND pm2.meta_value = %s
         WHERE p.post_type = %s AND p.post_status IN ('publish', 'draft', 'pending', 'private', 'future', 'trash')
         ORDER BY p.ID ASC LIMIT 1",
        $calc_type, $state_slug, UST_CPT
    ));
    
    if ($post_id) {
        return get_post($post_id);
    }
    
    // Fallback: look up by slug
    $expected_slug = $state_slug . '-' . $calc_type . '-calculator';
    if ($calc_type === 'other') {
        $expected_slug = $state_slug;
    }
    
    $post_id = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts}
         WHERE post_name = %s AND post_type = %s AND post_status IN ('publish', 'draft', 'pending', 'private', 'future', 'trash')
         ORDER BY ID ASC LIMIT 1",
        $expected_slug, UST_CPT
    ));
    
    if ($post_id) {
        return get_post($post_id);
    }
    
    return null;
}

/**
 * Clean up all duplicate calculators
 */
function ust_cleanup_duplicate_calculators() {
    global $wpdb;
    
    // Fetch all calculator posts directly from DB, sorted by ID ASC
    $posts = $wpdb->get_results($wpdb->prepare(
        "SELECT ID, post_name FROM {$wpdb->posts}
         WHERE post_type = %s AND post_status IN ('publish', 'draft', 'pending', 'private', 'future', 'trash')
         ORDER BY ID ASC",
        UST_CPT
    ));

    $seen = [];
    foreach ($posts as $post) {
        $calc_type = get_post_meta($post->ID, '_ust_calc_type', true);
        $state_slug = get_post_meta($post->ID, '_ust_state_slug', true);

        if (empty($calc_type) || empty($state_slug)) {
            $post_slug = $post->post_name;
            $detected_type = '';
            $other_slugs = [
                'federal-income-tax-calculator',
                'state-income-tax-calculator',
                'income-tax-refund-calculator',
                'tax-withholding-calculator',
                'tax-bracket-calculator',
                'estimated-tax-calculator',
                'capital-gains-tax-calculator',
                'self-employment-tax-calculator',
                'payroll-tax-calculator',
                'sales-tax-calculator',
                'property-tax-estimator',
                'effective-property-tax-rate-calculator',
                'state-tax-comparison-calculator'
            ];
            if (in_array($post_slug, $other_slugs)) {
                $detected_type = 'other';
                $state_slug = $post_slug;
            } elseif (strpos($post_slug, 'property-tax') !== false) {
                $detected_type = 'property-tax';
            } elseif (strpos($post_slug, 'sales-tax') !== false) {
                $detected_type = 'sales-tax';
            } elseif (strpos($post_slug, 'income-tax') !== false) {
                $detected_type = 'income-tax';
            }

            if ($detected_type) {
                $calc_type = $detected_type;
                if ($calc_type !== 'other') {
                    $prefixes = ['income-tax-calculator-', 'property-tax-calculator-', 'sales-tax-calculator-'];
                    $suffixes = ['-income-tax-calculator', '-property-tax-calculator', '-sales-tax-calculator'];
                    $state_slug = str_replace($prefixes, '', $post_slug);
                    $state_slug = str_replace($suffixes, '', $state_slug);
                    $state_slug = preg_replace('/-[0-9]+$/', '', $state_slug);
                }
                update_post_meta($post->ID, '_ust_calc_type', $calc_type);
                update_post_meta($post->ID, '_ust_state_slug', $state_slug);
            }
        }

        if ($calc_type && $state_slug) {
            $key = $calc_type . '_' . $state_slug;
            if (isset($seen[$key])) {
                wp_delete_post($post->ID, true);
            } else {
                $seen[$key] = $post->ID;
            }
        }
    }
}

/**
 * Auto-generates the 50 state income tax, property tax, and sales tax calculators
 */
function ust_auto_generate_state_pages() {
    ust_cleanup_duplicate_calculators();

    $states = ust_get_states_data();
    foreach ($states as $slug => $state) {
        // 1. Income Tax Page
        $income_slug = $slug . '-income-tax-calculator';
        $income_exists = ust_get_calculator_by_meta('income-tax', $slug);
        if (!$income_exists) {
            $post_id = wp_insert_post([
                'post_title'   => $state['name'] . ' Income Tax Calculator',
                'post_name'    => $income_slug,
                'post_status'  => 'publish',
                'post_type'    => UST_CPT,
                'post_content' => ust_get_income_tax_default_content($state),
            ]);
            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, '_ust_calc_type', 'income-tax');
                update_post_meta($post_id, '_ust_state_slug', $slug);
                update_post_meta($post_id, '_ust_seo_title', sprintf(__('%s Income Tax Calculator - Calfy', 'usa-state-tax-calculators'), $state['name']));
                update_post_meta($post_id, '_ust_seo_desc', sprintf(__('Estimate your annual take-home salary, federal income taxes, FICA withholdings, and %s state tax brackets using our free income tax calculator.', 'usa-state-tax-calculators'), $state['name']));
                
                $defaults = ust_get_default_templates('income-tax', $slug);
                update_post_meta($post_id, '_ust_calc_html', $defaults['html']);
                update_post_meta($post_id, '_ust_calc_css', $defaults['css']);
                update_post_meta($post_id, '_ust_calc_js', $defaults['js']);
                update_post_meta($post_id, '_ust_faqs', ust_get_income_tax_faqs($state));
                 update_post_meta($post_id, '_ust_template_version', '8');
                update_post_meta($post_id, '_ust_enable_lead_capture', '0');

                $thumb_id = ust_get_or_create_illustration_attachment('income-tax');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
                wp_set_object_terms($post_id, 'income-tax', 'ust_category');
            }
        } else {
            $post_id = $income_exists->ID;
            $post_status = get_post_status($post_id);
            if ($post_status === 'trash' || $post_status === 'draft') {
                wp_update_post(['ID' => $post_id, 'post_status' => 'publish']);
            }
            
            $post_content = $income_exists->post_content;
            if (empty($post_content) || strpos($post_content, '<!-- ust-v12-article -->') === false) {
                wp_update_post(['ID' => $post_id, 'post_content' => ust_get_income_tax_default_content($state) . '<!-- ust-v12-article -->']);
                update_post_meta($post_id, '_ust_faqs', ust_get_income_tax_faqs($state));
            }
            
            if (!has_post_thumbnail($post_id)) {
                $thumb_id = ust_get_or_create_illustration_attachment('income-tax');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
            }
            update_post_meta($post_id, '_ust_calc_type', 'income-tax');
            update_post_meta($post_id, '_ust_state_slug', $slug);
            wp_set_object_terms($post_id, 'income-tax', 'ust_category');

            $current_ver = get_post_meta($post_id, '_ust_template_version', true);
            if ($current_ver !== '16' || empty(get_post_meta($post_id, '_ust_calc_html', true))) {
                $defaults = ust_get_default_templates('income-tax', $slug);
                update_post_meta($post_id, '_ust_calc_html', $defaults['html']);
                update_post_meta($post_id, '_ust_calc_css', $defaults['css']);
                update_post_meta($post_id, '_ust_calc_js', $defaults['js']);
                update_post_meta($post_id, '_ust_template_version', '16');
            }
        }

        // 2. Property Tax Page
        $property_slug = $slug . '-property-tax-calculator';
        $property_exists = ust_get_calculator_by_meta('property-tax', $slug);
        if (!$property_exists) {
            $post_id = wp_insert_post([
                'post_title'   => $state['name'] . ' Property Tax Calculator',
                'post_name'    => $property_slug,
                'post_status'  => 'publish',
                'post_type'    => UST_CPT,
                'post_content' => ust_get_property_tax_default_content($state),
            ]);
            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, '_ust_calc_type', 'property-tax');
                update_post_meta($post_id, '_ust_state_slug', $slug);
                update_post_meta($post_id, '_ust_seo_title', sprintf(__('%s Property Tax Calculator - Calfy', 'usa-state-tax-calculators'), $state['name']));
                update_post_meta($post_id, '_ust_seo_desc', sprintf(__('Calculate your monthly and annual property taxes in %s. Select your county, apply senior/homestead exemptions, and view 5-year projections.', 'usa-state-tax-calculators'), $state['name']));
                
                $defaults = ust_get_default_templates('property-tax', $slug);
                update_post_meta($post_id, '_ust_calc_html', $defaults['html']);
                update_post_meta($post_id, '_ust_calc_css', $defaults['css']);
                update_post_meta($post_id, '_ust_calc_js', $defaults['js']);
                update_post_meta($post_id, '_ust_faqs', ust_get_property_tax_faqs($state));
                 update_post_meta($post_id, '_ust_template_version', '8');
                update_post_meta($post_id, '_ust_enable_lead_capture', '0');

                $thumb_id = ust_get_or_create_illustration_attachment('property-tax');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
                wp_set_object_terms($post_id, 'property-tax', 'ust_category');
            }
        } else {
            $post_id = $property_exists->ID;
            $post_status = get_post_status($post_id);
            if ($post_status === 'trash' || $post_status === 'draft') {
                wp_update_post(['ID' => $post_id, 'post_status' => 'publish']);
            }
            
            $post_content = $property_exists->post_content;
            if (empty($post_content) || strpos($post_content, '<!-- ust-v12-article -->') === false) {
                wp_update_post(['ID' => $post_id, 'post_content' => ust_get_property_tax_default_content($state) . '<!-- ust-v12-article -->']);
                update_post_meta($post_id, '_ust_faqs', ust_get_property_tax_faqs($state));
            }
            
            if (!has_post_thumbnail($post_id)) {
                $thumb_id = ust_get_or_create_illustration_attachment('property-tax');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
            }
            update_post_meta($post_id, '_ust_calc_type', 'property-tax');
            update_post_meta($post_id, '_ust_state_slug', $slug);
            wp_set_object_terms($post_id, 'property-tax', 'ust_category');

            $current_ver = get_post_meta($post_id, '_ust_template_version', true);
            if ($current_ver !== '16' || empty(get_post_meta($post_id, '_ust_calc_html', true))) {
                $defaults = ust_get_default_templates('property-tax', $slug);
                update_post_meta($post_id, '_ust_calc_html', $defaults['html']);
                update_post_meta($post_id, '_ust_calc_css', $defaults['css']);
                update_post_meta($post_id, '_ust_calc_js', $defaults['js']);
                update_post_meta($post_id, '_ust_template_version', '16');
            }
        }

        // 3. Sales Tax Page
        $sales_slug = $slug . '-sales-tax-calculator';
        $sales_exists = ust_get_calculator_by_meta('sales-tax', $slug);
        if (!$sales_exists) {
            $post_id = wp_insert_post([
                'post_title'   => $state['name'] . ' Sales Tax Calculator',
                'post_name'    => $sales_slug,
                'post_status'  => 'publish',
                'post_type'    => UST_CPT,
                'post_content' => ust_get_sales_tax_default_content($state),
            ]);
            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, '_ust_calc_type', 'sales-tax');
                update_post_meta($post_id, '_ust_state_slug', $slug);
                update_post_meta($post_id, '_ust_seo_title', sprintf(__('%s Sales Tax Calculator - Calfy', 'usa-state-tax-calculators'), $state['name']));
                update_post_meta($post_id, '_ust_seo_desc', sprintf(__('Calculate the combined state and local sales tax for purchases in %s. Apply tax-exempt status for groceries or medicine and view benchmarking costs.', 'usa-state-tax-calculators'), $state['name']));
                
                $defaults = ust_get_default_templates('sales-tax', $slug);
                update_post_meta($post_id, '_ust_calc_html', $defaults['html']);
                update_post_meta($post_id, '_ust_calc_css', $defaults['css']);
                update_post_meta($post_id, '_ust_calc_js', $defaults['js']);
                update_post_meta($post_id, '_ust_faqs', ust_get_sales_tax_faqs($state));
                 update_post_meta($post_id, '_ust_template_version', '8');
                update_post_meta($post_id, '_ust_enable_lead_capture', '0');

                $thumb_id = ust_get_or_create_illustration_attachment('sales-tax');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
                wp_set_object_terms($post_id, 'sales-tax', 'ust_category');
            }
        } else {
            $post_id = $sales_exists->ID;
            $post_status = get_post_status($post_id);
            if ($post_status === 'trash' || $post_status === 'draft') {
                wp_update_post(['ID' => $post_id, 'post_status' => 'publish']);
            }
            
            $post_content = $sales_exists->post_content;
            if (empty($post_content) || strpos($post_content, '<!-- ust-v12-article -->') === false) {
                wp_update_post(['ID' => $post_id, 'post_content' => ust_get_sales_tax_default_content($state) . '<!-- ust-v12-article -->']);
                update_post_meta($post_id, '_ust_faqs', ust_get_sales_tax_faqs($state));
            }
            
            if (!has_post_thumbnail($post_id)) {
                $thumb_id = ust_get_or_create_illustration_attachment('sales-tax');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
            }
            update_post_meta($post_id, '_ust_calc_type', 'sales-tax');
            update_post_meta($post_id, '_ust_state_slug', $slug);
            wp_set_object_terms($post_id, 'sales-tax', 'ust_category');

            $current_ver = get_post_meta($post_id, '_ust_template_version', true);
            if ($current_ver !== '16' || empty(get_post_meta($post_id, '_ust_calc_html', true))) {
                $defaults = ust_get_default_templates('sales-tax', $slug);
                update_post_meta($post_id, '_ust_calc_html', $defaults['html']);
                update_post_meta($post_id, '_ust_calc_css', $defaults['css']);
                update_post_meta($post_id, '_ust_calc_js', $defaults['js']);
                update_post_meta($post_id, '_ust_template_version', '16');
            }
        }
    }

    // 4. General / National Calculators (Other category)
    $other_registry = ust_get_other_calculators_registry();
    foreach ($other_registry as $other_slug => $other_data) {
        $other_exists = ust_get_calculator_by_meta('other', $other_slug);
        if (!$other_exists) {
            $post_id = wp_insert_post([
                'post_title'   => $other_data['name'],
                'post_name'    => $other_slug,
                'post_status'  => 'publish',
                'post_type'    => UST_CPT,
                'post_content' => ust_get_other_tax_default_content($other_slug),
            ]);
            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, '_ust_calc_type', 'other');
                update_post_meta($post_id, '_ust_state_slug', $other_slug);
                update_post_meta($post_id, '_ust_seo_title', $other_data['title_seo']);
                update_post_meta($post_id, '_ust_seo_desc', $other_data['desc_seo']);
                
                $defaults = ust_get_default_templates('other', $other_slug);
                update_post_meta($post_id, '_ust_calc_html', $defaults['html']);
                update_post_meta($post_id, '_ust_calc_css', $defaults['css']);
                update_post_meta($post_id, '_ust_calc_js', $defaults['js']);
                update_post_meta($post_id, '_ust_faqs', ust_get_other_tax_faqs($other_slug));
                 update_post_meta($post_id, '_ust_template_version', '8');
                update_post_meta($post_id, '_ust_enable_lead_capture', '0');

                $thumb_id = ust_get_or_create_illustration_attachment('income-tax');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
                wp_set_object_terms($post_id, 'other', 'ust_category');
            }
        } else {
            $post_id = $other_exists->ID;
            $post_status = get_post_status($post_id);
            if ($post_status === 'trash' || $post_status === 'draft') {
                wp_update_post(['ID' => $post_id, 'post_status' => 'publish']);
            }
            
            $post_content = $other_exists->post_content;
            if (empty($post_content) || strpos($post_content, '<!-- ust-v12-article -->') === false) {
                wp_update_post(['ID' => $post_id, 'post_content' => ust_get_other_tax_default_content($other_slug) . '<!-- ust-v12-article -->']);
                update_post_meta($post_id, '_ust_faqs', ust_get_other_tax_faqs($other_slug));
            }
            
            if (!has_post_thumbnail($post_id)) {
                $thumb_id = ust_get_or_create_illustration_attachment('income-tax');
                if ($thumb_id) {
                    set_post_thumbnail($post_id, $thumb_id);
                }
            }
            update_post_meta($post_id, '_ust_calc_type', 'other');
            update_post_meta($post_id, '_ust_state_slug', $other_slug);
            wp_set_object_terms($post_id, 'other', 'ust_category');

            $current_ver = get_post_meta($post_id, '_ust_template_version', true);
            if ($current_ver !== '16' || empty(get_post_meta($post_id, '_ust_calc_html', true))) {
                $defaults = ust_get_default_templates('other', $other_slug);
                update_post_meta($post_id, '_ust_calc_html', $defaults['html']);
                update_post_meta($post_id, '_ust_calc_css', $defaults['css']);
                update_post_meta($post_id, '_ust_calc_js', $defaults['js']);
                update_post_meta($post_id, '_ust_template_version', '16');
            }
        }
    }
}

/**
 * Shortcode for Directory lists [ust_directory]
 */
add_shortcode('ust_directory', 'ust_render_directory_shortcode');
function ust_render_directory_shortcode($atts) {
    $atts = shortcode_atts([
        'type' => 'income-tax', // 'income-tax', 'property-tax', 'sales-tax'
    ], $atts, 'ust_directory');

    $states = ust_get_states_data();
    $type = sanitize_key($atts['type']);

    $title = 'Tax Calculators';
    $desc = 'Select your state below to run calculations accurately.';
    if ($type === 'income-tax') {
        $title = 'Income Tax Calculators';
        $desc = 'Select your state below to estimate federal/state income taxes, FICA withholdings, and take-home pay.';
    } elseif ($type === 'property-tax') {
        $title = 'Property Tax Calculators';
        $desc = 'Select your state below to estimate annual and monthly property taxes, assessments, and exemptions.';
    } elseif ($type === 'sales-tax') {
        $title = 'Sales Tax Calculators';
        $desc = 'Select your state below to calculate combined state and local sales taxes on retail purchases.';
    }

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
                $post = get_page_by_path($post_slug, OBJECT, UST_CPT);
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
 * Register Submenus
 */
add_action('admin_menu', 'ust_register_admin_submenus');
function ust_register_admin_submenus() {
    remove_menu_page('edit.php?post_type=' . UST_CPT);

    add_menu_page(
        'Tax Hub',
        'Tax Hub',
        'manage_options',
        'ust_calculators_hub',
        'ust_render_hub_dashboard',
        'dashicons-calculator',
        32
    );

    add_submenu_page(
        'ust_calculators_hub',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'ust_calculators_hub',
        'ust_render_hub_dashboard'
    );

    add_submenu_page(
        'ust_calculators_hub',
        'Income Tax Calculators',
        'Income Tax',
        'manage_options',
        'edit.php?post_type=' . UST_CPT . '&calc_type=income-tax'
    );

    add_submenu_page(
        'ust_calculators_hub',
        'Property Tax Calculators',
        'Property Tax',
        'manage_options',
        'edit.php?post_type=' . UST_CPT . '&calc_type=property-tax'
    );

    add_submenu_page(
        'ust_calculators_hub',
        'Sales Tax Calculators',
        'Sales Tax',
        'manage_options',
        'edit.php?post_type=' . UST_CPT . '&calc_type=sales-tax'
    );

    add_submenu_page(
        'ust_calculators_hub',
        'Other Tax Calculators',
        'Other Tax',
        'manage_options',
        'edit.php?post_type=' . UST_CPT . '&calc_type=other'
    );

    add_submenu_page(
        'ust_calculators_hub',
        'Add New',
        'Add New',
        'manage_options',
        'post-new.php?post_type=' . UST_CPT
    );

    add_submenu_page(
        'ust_calculators_hub',
        'Captured Leads',
        'Captured Leads',
        'manage_options',
        'ust_captured_leads',
        'ust_render_leads_page'
    );

    add_submenu_page(
        'ust_calculators_hub',
        'Usage Analytics',
        'Usage Analytics',
        'manage_options',
        'ust_usage_analytics',
        'ust_render_usage_page'
    );

    add_submenu_page(
        'ust_calculators_hub',
        'Ads Settings',
        'Ads Settings',
        'manage_options',
        'ust_ads_settings',
        'ust_render_ads_settings_page'
    );
}

// Highlight parent menu in admin
add_filter('parent_file', 'ust_admin_parent_menu_highlight');
function ust_admin_parent_menu_highlight($parent_file) {
    global $pagenow;
    if (is_admin()) {
        if (($pagenow === 'edit.php' || $pagenow === 'post-new.php' || $pagenow === 'post.php') && isset($_GET['post_type']) && $_GET['post_type'] === UST_CPT) {
            return 'ust_calculators_hub';
        }
        if ($pagenow === 'post.php' && isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            if (get_post_type($post_id) === UST_CPT) {
                return 'ust_calculators_hub';
            }
        }
    }
    return $parent_file;
}

add_filter('submenu_file', 'ust_admin_submenu_menu_highlight', 10, 2);
function ust_admin_submenu_menu_highlight($submenu_file, $parent_file) {
    global $pagenow;
    if ($parent_file === 'ust_calculators_hub') {
        if ($pagenow === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === UST_CPT) {
            $calc_type = isset($_GET['calc_type']) ? sanitize_key($_GET['calc_type']) : '';
            if ($calc_type) {
                return 'edit.php?post_type=' . UST_CPT . '&calc_type=' . $calc_type;
            }
        }
        if ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === UST_CPT) {
            return 'post-new.php?post_type=' . UST_CPT;
        }
        if ($pagenow === 'post.php' && isset($_GET['post'])) {
            $post_id = intval($_GET['post']);
            if (get_post_type($post_id) === UST_CPT) {
                $calc_type = get_post_meta($post_id, '_ust_calc_type', true);
                if ($calc_type) {
                    return 'edit.php?post_type=' . UST_CPT . '&calc_type=' . $calc_type;
                }
            }
        }
    }
    return $submenu_file;
}

/**
 * Render Admin Nav Header
 */
function ust_render_admin_nav($active_tab) {
    ?>
    <div class="ust-admin-nav">
        <div class="ust-admin-nav-brand">
            <span class="dashicons dashicons-calculator" style="font-size: 20px; width: 20px; height: 20px; line-height: 20px; color: var(--ust-primary); margin-right: 8px;"></span>
            <span>USA State Tax Calculators Hub</span>
        </div>
        <div class="ust-admin-nav-links">
            <a href="<?php echo admin_url('admin.php?page=ust_calculators_hub'); ?>" class="<?php echo $active_tab === 'dashboard' ? 'active' : ''; ?>">Dashboard</a>
            <a href="<?php echo admin_url('edit.php?post_type=' . UST_CPT . '&calc_type=income-tax'); ?>" class="<?php echo $active_tab === 'income-tax' ? 'active' : ''; ?>">Income Tax</a>
            <a href="<?php echo admin_url('edit.php?post_type=' . UST_CPT . '&calc_type=property-tax'); ?>" class="<?php echo $active_tab === 'property-tax' ? 'active' : ''; ?>">Property Tax</a>
            <a href="<?php echo admin_url('edit.php?post_type=' . UST_CPT . '&calc_type=sales-tax'); ?>" class="<?php echo $active_tab === 'sales-tax' ? 'active' : ''; ?>">Sales Tax</a>
            <a href="<?php echo admin_url('edit.php?post_type=' . UST_CPT . '&calc_type=other'); ?>" class="<?php echo $active_tab === 'other' ? 'active' : ''; ?>">Other Tax</a>
            <a href="<?php echo admin_url('admin.php?page=ust_captured_leads'); ?>" class="<?php echo $active_tab === 'leads' ? 'active' : ''; ?>">Captured Leads</a>
            <a href="<?php echo admin_url('admin.php?page=ust_usage_analytics'); ?>" class="<?php echo $active_tab === 'usage' ? 'active' : ''; ?>">Usage Analytics</a>
            <a href="<?php echo admin_url('admin.php?page=ust_ads_settings'); ?>" class="<?php echo $active_tab === 'ads' ? 'active' : ''; ?>">Ads Settings</a>
        </div>
    </div>
    <?php
}

/**
 * Render Ads Settings Page
 */
function ust_render_ads_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user.');
    }

    if (isset($_POST['ust_save_ads_settings'])) {
        if (!isset($_POST['ust_ads_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ust_ads_nonce'])), 'ust_save_ads')) {
            wp_die('Security check failed.');
        }

        $enabled = isset($_POST['ust_global_ads_enabled']) ? '1' : '0';
        update_option('ust_global_ads_enabled', $enabled);

        if (current_user_can('unfiltered_html')) {
            $code = isset($_POST['ust_global_ads_code']) ? wp_unslash($_POST['ust_global_ads_code']) : '';
            update_option('ust_global_ads_code', $code);
        }

        echo '<div class="notice notice-success is-dismissible"><p><strong>Settings saved successfully.</strong></p></div>';
    }

    $global_enabled = get_option('ust_global_ads_enabled', '1');
    $global_code = get_option('ust_global_ads_code', '');
    
    ust_render_admin_nav('ads');
    ?>
    <div class="ust-admin-wrap">
        <div class="ust-panel">
            <div class="ust-panel-header">
                <h2>Native Ads Configuration</h2>
            </div>
            <div class="ust-panel-content">
                <form method="post" action="">
                    <?php wp_nonce_field('ust_save_ads', 'ust_ads_nonce'); ?>

                    <div class="ust-meta-row" style="margin-bottom: 24px;">
                        <label for="ust_global_ads_enabled" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-size: 14px;">
                            <input type="checkbox" name="ust_global_ads_enabled" id="ust_global_ads_enabled" value="1" <?php checked($global_enabled, '1'); ?> style="margin: 0; width: 16px; height: 16px;" />
                            <strong>Enable Native Ads Globally</strong>
                        </label>
                        <p class="description" style="margin-top: 6px; margin-left: 24px; color: var(--ust-text-muted);">
                            Toggle this checkbox to turn all native advertisements on or off across all tax calculator pages instantly.
                        </p>
                    </div>

                    <div class="ust-meta-row" style="margin-bottom: 24px;">
                        <label for="ust_global_ads_code" style="font-size: 14px; font-weight: 700; margin-bottom: 8px; display: block;">Global Native Ads Script Code</label>
                        <textarea name="ust_global_ads_code" id="ust_global_ads_code" style="width: 100%; height: 180px; font-family: monospace; font-size: 13px; padding: 12px; border-radius: 8px; border: 1px solid var(--ust-border);" placeholder="Paste your ad script code here..."><?php echo esc_textarea($global_code); ?></textarea>
                    </div>

                    <div class="ust-meta-row">
                        <button type="submit" name="ust_save_ads_settings" class="ust-btn ust-btn-primary" style="padding: 12px 24px; font-size: 14px;">
                            <span class="dashicons dashicons-saved"></span> Save Configurations
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render Unified Dashboard Page
 */
function ust_render_hub_dashboard() {
    global $wpdb;

    $total_leads = intval($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ust_leads"));
    $total_usage = intval($wpdb->get_var("SELECT SUM(count) FROM {$wpdb->prefix}ust_usage_stats"));

    $recent_leads = $wpdb->get_results("SELECT l.*, p.post_title FROM {$wpdb->prefix}ust_leads l LEFT JOIN {$wpdb->posts} p ON l.post_id = p.ID ORDER BY l.created_at DESC LIMIT 5", ARRAY_A);
    $popular_calcs = $wpdb->get_results("SELECT u.*, p.post_title, p.ID FROM {$wpdb->prefix}ust_usage_stats u LEFT JOIN {$wpdb->posts} p ON u.post_id = p.ID ORDER BY u.count DESC LIMIT 5", ARRAY_A);

    $categories = get_terms(array(
        'taxonomy'   => 'ust_category',
        'hide_empty' => false,
    ));

    $cat_order = array('income-tax', 'property-tax', 'sales-tax', 'other');
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

    $calcs_query = new WP_Query(array(
        'post_type'      => UST_CPT,
        'posts_per_page' => -1,
        'post_status'    => array('publish', 'draft', 'pending', 'private')
    ));
    $categorized_calcs = array();
    foreach ($categories as $cat) {
        $categorized_calcs[$cat->slug] = array();
    }

    $total_calcs_count = 0;
    if ($calcs_query->have_posts()) {
        while ($calcs_query->have_posts()) {
            $calcs_query->the_post();
            $pid = get_the_ID();
            $slug = get_post_field('post_name');
            $post_terms = wp_get_object_terms($pid, 'ust_category');
            
            $term_slug = 'uncategorized';
            if (!empty($post_terms) && !is_wp_error($post_terms)) {
                $term_slug = $post_terms[0]->slug;
            }
            
            $state_slug = get_post_meta($pid, '_ust_state_slug', true);
            if (empty($state_slug)) {
                $state_slug = $slug;
                $prefixes = array('income-tax-calculator-', 'property-tax-calculator-', 'sales-tax-calculator-');
                $suffixes = array('-income-tax-calculator', '-property-tax-calculator', '-sales-tax-calculator');
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

    $usage_stats_results = $wpdb->get_results("SELECT post_id, count FROM {$wpdb->prefix}ust_usage_stats", ARRAY_A);
    $usage_stats = array();
    if (!empty($usage_stats_results)) {
        foreach ($usage_stats_results as $row) {
            $usage_stats[intval($row['post_id'])] = intval($row['count']);
        }
    }

    $leads_stats_results = $wpdb->get_results("SELECT post_id, COUNT(*) as count FROM {$wpdb->prefix}ust_leads GROUP BY post_id", ARRAY_A);
    $leads_stats = array();
    if (!empty($leads_stats_results)) {
        foreach ($leads_stats_results as $row) {
            $leads_stats[intval($row['post_id'])] = intval($row['count']);
        }
    }

    $category_metrics = array();
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
        
        $conversion_rate = ($cat_runs > 0) ? ($cat_leads / $cat_runs) * 100 : 0;
        
        $category_metrics[$cat->slug] = array(
            'name'         => $cat->name,
            'runs'         => $cat_runs,
            'leads'        => $cat_leads,
            'active_pages' => $cat_active_pages,
            'conv_rate'    => $conversion_rate
        );
    }
    
    ust_render_admin_nav('dashboard');
    ?>
    <div class="ust-admin-wrap">

        <!-- Banner Header -->
        <div class="ust-dashboard-banner-new">
            <div class="ust-banner-left">
                <div class="ust-banner-title-row">
                    <span class="ust-banner-icon">📊</span>
                    <h1>USA State Tax Calculators Hub</h1>
                </div>
                <div class="ust-banner-status-checks">
                    <span>Brackets Load <span class="ust-chk-icon">✓</span></span>
                    <span class="ust-divider">|</span>
                    <span>County Rates <span class="ust-chk-icon">✓</span></span>
                    <span class="ust-divider">|</span>
                    <span>Sales Tax Databases <span class="ust-chk-icon">✓</span></span>
                    <span class="ust-divider">|</span>
                    <span>SEO Schema <span class="ust-chk-icon">✓</span></span>
                </div>
            </div>
            <div class="ust-banner-right">
                <span class="ust-banner-tool-badge"><?php echo $total_calcs_count; ?> Calculators</span>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?action=ust_export_leads'), 'ust_export_leads_nonce')); ?>" class="ust-banner-btn-white">
                    <span class="dashicons dashicons-download"></span> Export Leads
                </a>
                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?action=ust_sync_pages'), 'ust_sync_pages_nonce')); ?>" class="ust-banner-btn-purple" onclick="return confirm('Are you sure you want to sync all tax calculators?');">
                    <span class="dashicons dashicons-update"></span> Sync Pages
                </a>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="ust-metrics-grid-new">
            <div class="ust-metric-card-new">
                <div class="ust-metric-icon icon-blue">🇺🇸</div>
                <div class="ust-metric-card-body">
                    <h2>50</h2>
                    <p>ACTIVE STATES</p>
                </div>
            </div>
            <div class="ust-metric-card-new">
                <div class="ust-metric-icon icon-yellow">📊</div>
                <div class="ust-metric-card-body">
                    <h2><?php echo $total_calcs_count; ?></h2>
                    <p>TOTAL CALCULATORS</p>
                </div>
            </div>
            <div class="ust-metric-card-new">
                <div class="ust-metric-icon icon-orange">✉️</div>
                <div class="ust-metric-card-body">
                    <h2><?php echo number_format($total_leads); ?></h2>
                    <p>CAPTURED LEADS</p>
                </div>
            </div>
            <div class="ust-metric-card-new">
                <div class="ust-metric-icon icon-green">🚀</div>
                <div class="ust-metric-card-body">
                    <h2><?php echo number_format($total_usage); ?></h2>
                    <p>TOTAL RUNS</p>
                </div>
            </div>
        </div>

        <div class="ust-status-check-bar">
            <div class="ust-status-check-item">
                <span class="ust-dot-light green"></span>
                <span class="dashicons dashicons-category"></span>
                <span>Registry: Connected</span>
            </div>
            <div class="ust-status-check-item">
                <span class="ust-dot-light green"></span>
                <span class="dashicons dashicons-database"></span>
                <span>Leads DB: Ready</span>
            </div>
        </div>

        <!-- Search Box -->
        <div class="ust-search-box-large">
            <span class="dashicons dashicons-search large-search-icon"></span>
            <input type="text" id="ust-calc-search" placeholder="Search state or category... (California, Sales Tax, Texas)" onkeyup="filterUstCalculators()" />
        </div>

        <!-- Category Filter Pills -->
        <div class="ust-category-pills-wrap">
            <button class="ust-category-pill active" data-category-slug="all" onclick="switchUstCategoryTab('all', this)">All Tools</button>
            <?php foreach ($categories as $cat) : 
                $metrics = $category_metrics[$cat->slug];
                ?>
                <button class="ust-category-pill" data-category-slug="<?php echo esc_attr($cat->slug); ?>" onclick="switchUstCategoryTab('<?php echo esc_attr($cat->slug); ?>', this)">
                    <?php echo esc_html($cat->name); ?> (<?php echo $metrics['active_pages']; ?>)
                </button>
            <?php endforeach; ?>
        </div>

        <!-- Registry List Table -->
        <div class="ust-panel" style="margin-top: 24px;">
            <div class="ust-panel-header">
                <h2>Calculators Registry</h2>
                <span class="ust-banner-tool-badge" id="ust-visible-count" style="background:#f1f5f9; color:#475569; border:1px solid #e2e8f0;">0 items</span>
            </div>
            <div class="ust-panel-content" style="padding: 0;">
                <table class="ust-custom-table" id="ust-calc-list-table">
                    <thead>
                        <tr>
                            <th>State / Calculator</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Calculations Run</th>
                            <th>Captured Leads</th>
                            <th style="width: 120px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ust-calc-list-tbody">
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
                                    if ($cat->slug === 'income-tax') $icon = '💵';
                                    elseif ($cat->slug === 'property-tax') $icon = '🏠';
                                    elseif ($cat->slug === 'sales-tax') $icon = '🛒';
                                    elseif ($cat->slug === 'other') $icon = '⚙️';
                                    ?>
                                    <tr class="ust-calc-row" data-name="<?php echo esc_attr($title); ?>" data-state="<?php echo esc_attr($state_slug); ?>" data-category="<?php echo esc_attr($cat->slug); ?>">
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <span class="ust-card-icon-wrap"><?php echo $icon; ?></span>
                                                <div>
                                                    <strong style="font-size: 14px; color: var(--ust-secondary);"><?php echo esc_html($title); ?></strong>
                                                    <div style="font-size: 11px; color: var(--ust-text-muted); margin-top: 2px;">ID: <?php echo $pid; ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="ust-tag-badge"><?php echo esc_html(strtoupper($cat->name)); ?></span>
                                        </td>
                                        <td>
                                            <span class="ust-card-status status-<?php echo esc_attr($status); ?>"><?php echo esc_html(ucfirst($status)); ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($runs); ?> runs</strong>
                                        </td>
                                        <td>
                                            <span class="ust-badge-lead"><?php echo number_format($leads); ?> leads</span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 8px; justify-content: center;">
                                                <a href="<?php echo esc_url(get_edit_post_link($pid)); ?>" class="ust-footer-action-btn" title="Edit Settings"><span class="dashicons dashicons-edit"></span></a>
                                                <a href="<?php echo esc_url($url); ?>" target="_blank" class="ust-footer-action-btn" title="View Live"><span class="dashicons dashicons-visibility"></span></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            endif;
                        endforeach;
                        
                        if (!$has_cards) : ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 50px; color: var(--ust-text-muted);">
                                    No tax calculators generated yet. Click "Sync Pages" in the banner to create them.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Analytics Performance Panel -->
        <div class="ust-panel">
            <div class="ust-panel-header">
                <h2>Category Performance & Conversion Analytics</h2>
            </div>
            <div class="ust-panel-content" style="padding: 0;">
                <table class="ust-custom-table">
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
                            $strength_color = 'var(--ust-primary)';
                            if ($metric['conv_rate'] > 15) $strength_color = 'var(--ust-success)';
                            elseif ($metric['conv_rate'] < 5) $strength_color = 'var(--ust-text-muted)';
                            ?>
                            <tr>
                                <td>
                                    <strong style="font-size:14px; color:var(--ust-secondary);"><?php echo esc_html($metric['name']); ?></strong>
                                </td>
                                <td>
                                    <span class="ust-tag-badge" style="background:#f1f5f9; color:#475569;"><?php echo $metric['active_pages']; ?> / 50 active</span>
                                </td>
                                <td><strong><?php echo number_format($metric['runs']); ?> runs</strong></td>
                                <td>
                                    <span class="ust-badge-lead"><?php echo number_format($metric['leads']); ?> leads</span>
                                </td>
                                <td>
                                    <strong style="color: <?php echo $strength_color; ?>; font-size:14px;"><?php echo number_format($metric['conv_rate'], 2); ?>%</strong>
                                </td>
                                <td>
                                    <div class="ust-strength-bar-wrap">
                                        <div class="ust-strength-bar" style="background:<?php echo $strength_color; ?>; width:<?php echo $strength_pct; ?>%;"></div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Dashboard Columns -->
        <div class="ust-dashboard-row">
            <!-- Recent Leads -->
            <div class="ust-dashboard-col">
                <div class="ust-panel">
                    <div class="ust-panel-header">
                        <h2>Recent Captured Leads</h2>
                        <a href="<?php echo admin_url('admin.php?page=ust_captured_leads'); ?>" class="ust-btn ust-btn-white" style="padding: 4px 8px; font-size:12px; border-radius:6px;">View All</a>
                    </div>
                    <div class="ust-panel-content" style="padding: 0;">
                        <?php if (empty($recent_leads)) : ?>
                            <p style="padding: 24px; color: var(--ust-text-muted); text-align: center; margin: 0;">No leads captured yet.</p>
                        <?php else : ?>
                            <table class="ust-custom-table">
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
                                            <td><strong><?php echo esc_html(str_replace([' Income Tax Calculator', ' Property Tax Calculator', ' Sales Tax Calculator'], '', $lead['post_title'])); ?></strong></td>
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

            <!-- Popular Calculators -->
            <div class="ust-dashboard-col">
                <div class="ust-panel">
                    <div class="ust-panel-header">
                        <h2>Most Popular Calculators</h2>
                        <a href="<?php echo admin_url('admin.php?page=ust_usage_analytics'); ?>" class="ust-btn ust-btn-white" style="padding: 4px 8px; font-size:12px; border-radius:6px;">View All</a>
                    </div>
                    <div class="ust-panel-content" style="padding: 0;">
                        <?php if (empty($popular_calcs)) : ?>
                            <p style="padding: 24px; color: var(--ust-text-muted); text-align: center; margin: 0;">No usage recorded yet.</p>
                        <?php else : ?>
                            <table class="ust-custom-table">
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
                                            <td><span class="ust-badge-run"><?php echo esc_html($use['count']); ?> runs</span></td>
                                            <td><a href="<?php echo esc_url(get_edit_post_link($use['ID'])); ?>" class="ust-btn ust-btn-white" style="padding: 4px 8px; font-size:11px; border-radius:6px;">Edit</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function switchUstCategoryTab(categorySlug, button) {
                document.querySelectorAll(".ust-category-pill").forEach(pill => pill.classList.remove("active"));
                button.classList.add("active");
                filterUstCalculators();
            }

            function filterUstCalculators() {
                var searchInput = document.getElementById("ust-calc-search");
                var filter = searchInput.value.toLowerCase();
                var activePill = document.querySelector(".ust-category-pill.active");
                var activeCategory = activePill ? activePill.getAttribute("data-category-slug") : "all";
                
                var rows = document.querySelectorAll(".ust-calc-row");
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
                
                var badge = document.getElementById("ust-visible-count");
                if (badge) {
                    badge.innerText = visibleCount + " items";
                }
            }

            document.addEventListener("DOMContentLoaded", function() {
                filterUstCalculators();
            });
        </script>
    </div>
    <?php
}

/**
 * Render Captured Leads Registry
 */
function ust_render_leads_page() {
    global $wpdb;
    $leads = $wpdb->get_results("SELECT l.*, p.post_title FROM {$wpdb->prefix}ust_leads l LEFT JOIN {$wpdb->posts} p ON l.post_id = p.ID ORDER BY l.created_at DESC", ARRAY_A);
    ust_render_admin_nav('leads');
    ?>
    <div class="ust-admin-wrap">
        <div class="ust-panel">
            <div class="ust-panel-header" style="border-bottom: none; padding-bottom: 0;">
                <div>
                    <h2 style="font-size:22px; margin-bottom: 5px;">Captured Leads Registry</h2>
                    <p style="margin: 0; color: var(--ust-text-muted); font-size:13px; font-weight: normal;">List of users who requested estimate reports before printing or downloading tax calculations details.</p>
                </div>
                <div>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?action=ust_export_leads'), 'ust_export_leads_nonce')); ?>" class="ust-btn ust-btn-primary">
                        <span class="dashicons dashicons-download"></span> Export to CSV
                    </a>
                </div>
            </div>
            
            <div class="ust-panel-content" style="padding: 24px 0 0 0;">
                <table class="ust-custom-table" style="border-top: 1px solid var(--ust-border);">
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
                            <tr><td colspan="5" style="text-align: center; padding: 30px; color: var(--ust-text-muted);">No leads captured yet.</td></tr>
                        <?php else : ?>
                            <?php foreach ($leads as $lead) : ?>
                                <tr>
                                    <td style="text-align: center; color: var(--ust-text-muted); font-weight: 600;"><?php echo esc_html($lead['id']); ?></td>
                                    <td><strong><?php echo esc_html($lead['post_title'] ?: 'State Calculator'); ?></strong></td>
                                    <td><span class="ust-badge-lead"><?php echo esc_html($lead['name']); ?></span></td>
                                    <td><a href="mailto:<?php echo esc_attr($lead['email']); ?>" style="text-decoration:none; color: var(--ust-primary); font-weight:600;"><?php echo esc_html($lead['email']); ?></a></td>
                                    <td style="color: var(--ust-text-muted);"><?php echo esc_html($lead['created_at']); ?></td>
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
 * Render Usage Analytics Logs
 */
function ust_render_usage_page() {
    global $wpdb;
    $usage = $wpdb->get_results("SELECT u.*, p.post_title FROM {$wpdb->prefix}ust_usage_stats u LEFT JOIN {$wpdb->posts} p ON u.post_id = p.ID ORDER BY u.count DESC", ARRAY_A);
    ust_render_admin_nav('usage');
    ?>
    <div class="ust-admin-wrap">
        <div class="ust-panel">
            <div class="ust-panel-header" style="border-bottom: none; padding-bottom: 0;">
                <div>
                    <h2 style="font-size:22px; margin-bottom: 5px;">Usage Analytics & Traffic logs</h2>
                    <p style="margin: 0; color: var(--ust-text-muted); font-size:13px; font-weight: normal;">Analytics showing total calculations triggered per state tax calculator page.</p>
                </div>
            </div>
            
            <div class="ust-panel-content" style="padding: 24px 0 0 0;">
                <table class="ust-custom-table" style="border-top: 1px solid var(--ust-border);">
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
                            <tr><td colspan="4" style="text-align: center; padding: 30px; color: var(--ust-text-muted);">No logs recorded yet.</td></tr>
                        <?php else : ?>
                            <?php foreach ($usage as $use) : ?>
                                <tr>
                                    <td><strong><?php echo esc_html($use['post_title'] ?: 'State Calculator'); ?></strong></td>
                                    <td><span class="ust-badge-run"><?php echo esc_html($use['count']); ?> calculations</span></td>
                                    <td style="color: var(--ust-text-muted);"><?php echo esc_html($use['last_used']); ?></td>
                                    <td style="text-align: center;"><a href="<?php echo esc_url(get_edit_post_link($use['post_id'])); ?>" class="ust-btn ust-btn-white" style="padding: 4px 8px; font-size:12px;">Edit Settings</a></td>
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
 * Intercept search and live search
 */
add_action('init', 'ust_override_theme_live_search', 21);
function ust_override_theme_live_search() {
    if (!has_action('wp_ajax_fxtool_live_search', 'usc_custom_ajax_live_search')) {
        remove_action('wp_ajax_fxtool_live_search', 'fxtool_ajax_live_search');
        remove_action('wp_ajax_nopriv_fxtool_live_search', 'fxtool_ajax_live_search');
        
        add_action('wp_ajax_fxtool_live_search', 'ust_custom_ajax_live_search');
        add_action('wp_ajax_nopriv_fxtool_live_search', 'ust_custom_ajax_live_search');
    }
}

function ust_custom_ajax_live_search() {
    check_ajax_referer('fxtool_search', 'nonce');

    $q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    if (strlen($q) < 2) {
        wp_send_json_success(array('items' => array()));
    }

    $out = array();

    // Query our CPT
    $calc_query = new WP_Query(array(
        'post_type'      => UST_CPT,
        'posts_per_page' => 10,
        'post_status'    => 'publish',
        's'              => $q,
    ));

    if ($calc_query->have_posts()) {
        while ($calc_query->have_posts()) {
            $calc_query->the_post();
            $pid = get_the_ID();
            $calc_type = get_post_meta($pid, '_ust_calc_type', true);
            $icon = '🔢';
            if ($calc_type === 'income-tax') $icon = '💵';
            elseif ($calc_type === 'property-tax') $icon = '🏠';
            elseif ($calc_type === 'sales-tax') $icon = '🛒';
            
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

    // Append paycheck CPT results if active
    if (count($out) < 10 && post_type_exists('usc_calculator')) {
        $usc_query = new WP_Query(array(
            'post_type'      => 'usc_calculator',
            'posts_per_page' => 10 - count($out),
            'post_status'    => 'publish',
            's'              => $q,
        ));
        if ($usc_query->have_posts()) {
            while ($usc_query->have_posts()) {
                $usc_query->the_post();
                $pid = get_the_ID();
                $calc_type = get_post_meta($pid, '_usc_calc_type', true);
                $icon = '🔢';
                if ($calc_type === 'paycheck') $icon = '💵';
                elseif ($calc_type === 'child-support') $icon = '👪';
                
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
    }

    // Query theme's directory items fallback
    if (count($out) < 10 && function_exists('fxtool_get_directory_items')) {
        $q_lower = strtolower($q);
        $items   = fxtool_get_directory_items();
        foreach ($items as $item) {
            $name = strtolower($item['name']);
            if (false !== strpos($name, $q_lower)) {
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
 * Helper to register or retrieve featured image attachment in Media Library
 */
function ust_get_or_create_illustration_attachment($type) {
    $option_name = 'ust_attachment_id_' . $type;
    $attachment_id = get_option($option_name);
    if ($attachment_id && wp_get_attachment_url($attachment_id)) {
        return (int) $attachment_id;
    }

    $filename = ($type === 'income-tax' || $type === 'sales-tax') ? 'income-tax-illustration.png' : 'property-tax-illustration.png';
    $file_path = UST_PATH . 'public/assets/images/' . $filename;
    
    if (!file_exists($file_path)) {
        return 0;
    }

    $wp_upload_dir = wp_upload_dir();
    if (!empty($wp_upload_dir['error'])) {
        return 0;
    }

    $target_path = $wp_upload_dir['path'] . '/' . $filename;
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


