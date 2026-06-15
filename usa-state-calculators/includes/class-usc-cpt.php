<?php
/**
 * Custom Post Type Handler for USA State-Wise Calculators
 */

if (!defined('ABSPATH')) exit;

class USC_CPT {

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
     * Registers the Custom Category Taxonomy (Niches)
     */
    public function register_taxonomy() {
        $labels = [
            'name'              => _x('Calculator Niches', 'taxonomy general name', 'usa-state-calculators'),
            'singular_name'     => _x('Calculator Niche', 'taxonomy singular name', 'usa-state-calculators'),
            'search_items'      => __('Search Niches', 'usa-state-calculators'),
            'all_items'         => __('All Niches', 'usa-state-calculators'),
            'parent_item'       => __('Parent Niche', 'usa-state-calculators'),
            'parent_item_colon' => __('Parent Niche:', 'usa-state-calculators'),
            'edit_item'         => __('Edit Niche', 'usa-state-calculators'),
            'update_item'       => __('Update Niche', 'usa-state-calculators'),
            'add_new_item'      => __('Add New Niche', 'usa-state-calculators'),
            'new_item_name'     => __('New Niche Name', 'usa-state-calculators'),
            'menu_name'         => __('Niches', 'usa-state-calculators'),
        ];

        $args = [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'rewrite'           => ['slug' => 'calculator-niche'],
            'show_in_rest'      => true,
        ];

        register_taxonomy('usc_category', [USC_CPT], $args);
    }

    /**
     * Registers the Custom Post Type
     */
    public function register_post_type() {
        $labels = [
            'name'               => _x('State Calculators', 'post type general name', 'usa-state-calculators'),
            'singular_name'      => _x('State Calculator', 'post type singular name', 'usa-state-calculators'),
            'menu_name'          => _x('State Calculators', 'admin menu', 'usa-state-calculators'),
            'name_admin_bar'     => _x('State Calculator', 'add new on admin bar', 'usa-state-calculators'),
            'add_new'            => _x('Add New', 'calculator', 'usa-state-calculators'),
            'add_new_item'       => __('Add New Calculator', 'usa-state-calculators'),
            'new_item'           => __('New Calculator', 'usa-state-calculators'),
            'edit_item'          => __('Edit Calculator', 'usa-state-calculators'),
            'view_item'          => __('View Calculator', 'usa-state-calculators'),
            'all_items'          => __('All Calculators', 'usa-state-calculators'),
            'search_items'       => __('Search Calculators', 'usa-state-calculators'),
            'parent_item_colon'  => __('Parent Calculators:', 'usa-state-calculators'),
            'not_found'          => __('No calculators found.', 'usa-state-calculators'),
            'not_found_in_trash' => __('No calculators found in Trash.', 'usa-state-calculators')
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'calculator', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 30,
            'menu_icon'          => 'dashicons-calculator',
            'supports'           => ['title', 'editor', 'thumbnail', 'revisions'],
            'show_in_rest'       => true, // Enable Block Editor support
        ];

        register_post_type(USC_CPT, $args);
    }

    /**
     * Intercepts template inclusion and loads our plugin template for single calculators
     */
    public function load_calculator_template($template) {
        if (is_singular(USC_CPT)) {
            // Check if theme has single-usc_calculator.php
            $theme_file = locate_template(['single-' . USC_CPT . '.php']);
            if ($theme_file) {
                return $theme_file;
            }
            // Fall back to plugin's built-in template
            $plugin_file = USC_PATH . 'public/templates/single-usc_calculator.php';
            if (file_exists($plugin_file)) {
                return $plugin_file;
            }
        }
        return $template;
    }

    /**
     * Registers prefix-free rewrite rules for both calculator CPT slugs
     */
    public function custom_rewrite_rules() {
        add_rewrite_rule(
            '^([a-z0-9\-]+-paycheck-calculator)/?$',
            'index.php?' . USC_CPT . '=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^([a-z0-9\-]+-child-support-calculator)/?$',
            'index.php?' . USC_CPT . '=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^([a-z0-9\-]+-alimony-calculator)/?$',
            'index.php?' . USC_CPT . '=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^([a-z0-9\-]+-mortgage-calculator)/?$',
            'index.php?' . USC_CPT . '=$matches[1]',
            'top'
        );
        add_rewrite_rule(
            '^(mortgage-calculator-[a-z0-9\-]+)/?$',
            'index.php?' . USC_CPT . '=$matches[1]',
            'top'
        );
    }

    /**
     * Filters permalink links on CPT to be prefix-free
     */
    public function custom_post_link($post_link, $post, $leavename) {
        if ($post->post_type === USC_CPT) {
            return home_url('/' . $post->post_name . '/');
        }
        return $post_link;
    }

    /**
     * Filters edit.php post lists to separate paycheck and child-support based on meta
     */
    public function filter_admin_calculators_query($query) {
        global $pagenow;
        if (is_admin() && $pagenow === 'edit.php' && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === USC_CPT) {
            if (isset($_GET['calc_type'])) {
                $calc_type = sanitize_key($_GET['calc_type']);
                $query->query_vars['meta_query'] = [
                    [
                        'key'     => '_usc_calc_type',
                        'value'   => $calc_type,
                        'compare' => '='
                    ]
                ];
            }
        }
    }

    /**
     * Includes our custom post type in frontend searches
     */
    public function include_calculators_in_search($query) {
        if (!is_admin() && $query->is_main_query() && $query->is_search()) {
            $post_types = $query->get('post_type');
            if (empty($post_types)) {
                $post_types = ['post', 'page', USC_CPT];
            } elseif (is_array($post_types)) {
                if (!in_array(USC_CPT, $post_types)) {
                    $post_types[] = USC_CPT;
                }
            } elseif (is_string($post_types) && $post_types !== USC_CPT) {
                $post_types = [$post_types, USC_CPT];
            }
            $query->set('post_type', $post_types);
        }
    }
}
