<?php
/**
 * Custom Post Type Handler for USA State-Wise Tax Calculators
 */

if (!defined('ABSPATH')) exit;

class UST_CPT {

    public function __construct() {
        add_action('init', [$this, 'register_taxonomy'], 9);
        add_action('init', [$this, 'register_post_type']);
        add_action('init', [$this, 'custom_rewrite_rules'], 10);
        add_filter('post_type_link', [$this, 'custom_post_link'], 10, 3);
        add_filter('template_include', [$this, 'load_calculator_template']);
        add_filter('parse_query', [$this, 'filter_admin_calculators_query']);
        add_action('pre_get_posts', [$this, 'include_calculators_in_search']);
    }

    /**
     * Registers the Custom Category Taxonomy
     */
    public function register_taxonomy() {
        $labels = [
            'name'              => _x('Tax Niches', 'taxonomy general name', 'usa-state-tax-calculators'),
            'singular_name'     => _x('Tax Niche', 'taxonomy singular name', 'usa-state-tax-calculators'),
            'search_items'      => __('Search Niches', 'usa-state-tax-calculators'),
            'all_items'         => __('All Niches', 'usa-state-tax-calculators'),
            'parent_item'       => __('Parent Niche', 'usa-state-tax-calculators'),
            'parent_item_colon' => __('Parent Niche:', 'usa-state-tax-calculators'),
            'edit_item'         => __('Edit Niche', 'usa-state-tax-calculators'),
            'update_item'       => __('Update Niche', 'usa-state-tax-calculators'),
            'add_new_item'      => __('Add New Niche', 'usa-state-tax-calculators'),
            'new_item_name'     => __('New Niche Name', 'usa-state-tax-calculators'),
            'menu_name'         => __('Tax Niches', 'usa-state-tax-calculators'),
        ];

        $args = [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'tax-niche'],
            'show_in_rest'      => true,
        ];

        register_taxonomy('ust_category', ['ust_calculator'], $args);
    }

    /**
     * Registers the Custom Post Type
     */
    public function register_post_type() {
        $labels = [
            'name'               => _x('Tax Calculators', 'post type general name', 'usa-state-tax-calculators'),
            'singular_name'      => _x('Tax Calculator', 'post type singular name', 'usa-state-tax-calculators'),
            'menu_name'          => _x('Tax Calculators', 'admin menu', 'usa-state-tax-calculators'),
            'name_admin_bar'     => _x('Tax Calculator', 'add new on admin bar', 'usa-state-tax-calculators'),
            'add_new'            => _x('Add New', 'tax calculator', 'usa-state-tax-calculators'),
            'add_new_item'       => __('Add New Tax Calculator', 'usa-state-tax-calculators'),
            'new_item'           => __('New Tax Calculator', 'usa-state-tax-calculators'),
            'edit_item'          => __('Edit Tax Calculator', 'usa-state-tax-calculators'),
            'view_item'          => __('View Tax Calculator', 'usa-state-tax-calculators'),
            'all_items'          => __('All Tax Calculators', 'usa-state-tax-calculators'),
            'search_items'       => __('Search Tax Calculators', 'usa-state-tax-calculators'),
            'parent_item_colon'  => __('Parent Tax Calculators:', 'usa-state-tax-calculators'),
            'not_found'          => __('No tax calculators found.', 'usa-state-tax-calculators'),
            'not_found_in_trash' => __('No tax calculators found in Trash.', 'usa-state-tax-calculators')
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'tax-calculator', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 31,
            'menu_icon'          => 'dashicons-calculator',
            'supports'           => ['title', 'editor', 'thumbnail', 'revisions'],
            'show_in_rest'       => true,
        ];

        register_post_type('ust_calculator', $args);
    }

    /**
     * Intercepts template inclusion and loads our plugin template for single calculators
     */
    public function load_calculator_template($template) {
        if (is_singular('ust_calculator')) {
            // Check if theme has single-ust_calculator.php
            $theme_file = locate_template(['single-ust_calculator.php']);
            if ($theme_file) {
                return $theme_file;
            }
            // Fall back to plugin's built-in template
            $plugin_file = UST_PATH . 'public/templates/single-ust_calculator.php';
            if (file_exists($plugin_file)) {
                return $plugin_file;
            }
        }
        return $template;
    }

    /**
     * Registers prefix-free rewrite rules
     */
    public function custom_rewrite_rules() {
        add_rewrite_rule(
            '^([a-z0-9\-]+-income-tax-calculator)/?$',
            'index.php?ust_calculator=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^([a-z0-9\-]+-property-tax-calculator)/?$',
            'index.php?ust_calculator=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^([a-z0-9\-]+-sales-tax-calculator)/?$',
            'index.php?ust_calculator=$matches[1]',
            'top'
        );

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
        foreach ($other_slugs as $slug) {
            add_rewrite_rule(
                '^(' . $slug . ')/?$',
                'index.php?ust_calculator=$matches[1]',
                'top'
            );
        }
    }

    /**
     * Filters permalinks to be prefix-free
     */
    public function custom_post_link($post_link, $post, $leavename) {
        if ($post->post_type === 'ust_calculator') {
            return home_url('/' . $post->post_name . '/');
        }
        return $post_link;
    }

    /**
     * Filters edit.php post list
     */
    public function filter_admin_calculators_query($query) {
        global $pagenow;
        if (is_admin() && $pagenow === 'edit.php' && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'ust_calculator') {
            if (isset($_GET['calc_type'])) {
                $calc_type = sanitize_key($_GET['calc_type']);
                $query->query_vars['meta_query'] = [
                    [
                        'key'     => '_ust_calc_type',
                        'value'   => $calc_type,
                        'compare' => '='
                    ]
                ];
            }
        }
    }

    /**
     * Includes CPT in search
     */
    public function include_calculators_in_search($query) {
        if (!is_admin() && $query->is_main_query() && $query->is_search()) {
            $post_types = $query->get('post_type');
            if (empty($post_types)) {
                $post_types = ['post', 'page', 'ust_calculator'];
            } elseif (is_array($post_types)) {
                if (!in_array('ust_calculator', $post_types)) {
                    $post_types[] = 'ust_calculator';
                }
            } elseif (is_string($post_types) && $post_types !== 'ust_calculator') {
                $post_types = [$post_types, 'ust_calculator'];
            }
            $query->set('post_type', $post_types);
        }
    }
}
