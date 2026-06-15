<?php
/**
 * SEO & Schema Markup Injection for Tax Calculators
 * Cross-plugin support for Yoast, Rank Math, SEOPress, All in One SEO, and default WP titles.
 */

if (!defined('ABSPATH')) exit;

class UST_SEO {

    public function __construct() {
        // Document Title Filters (Runs at 999 priority to override defaults)
        add_filter('pre_get_document_title', [$this, 'filter_wp_title'], 999);
        
        // Yoast SEO filters
        add_filter('wpseo_title', [$this, 'filter_wpseo_title'], 999);
        add_filter('wpseo_metadesc', [$this, 'filter_wpseo_desc'], 999);
        add_filter('wpseo_canonical', [$this, 'filter_wpseo_canonical'], 999);
        add_filter('wpseo_opengraph_title', [$this, 'filter_wpseo_title'], 999);
        add_filter('wpseo_opengraph_desc', [$this, 'filter_wpseo_desc'], 999);
        add_filter('wpseo_opengraph_url', [$this, 'filter_wpseo_canonical'], 999);
        
        // Rank Math filters
        add_filter('rank_math/frontend/title', [$this, 'filter_rankmath_title'], 999);
        add_filter('rank_math/frontend/description', [$this, 'filter_rankmath_desc'], 999);
        add_filter('rank_math/frontend/canonical', [$this, 'filter_rankmath_canonical'], 999);
        
        // SEOPress filters
        add_filter('seopress_titles_title', [$this, 'filter_seopress_title'], 999);
        add_filter('seopress_titles_desc', [$this, 'filter_seopress_desc'], 999);
        add_filter('seopress_social_accounts_canonical', [$this, 'filter_seopress_canonical'], 999);

        // AIOSEO filters
        add_filter('aioseo_title', [$this, 'filter_aioseo_title'], 999);
        add_filter('aioseo_description', [$this, 'filter_aioseo_desc'], 999);
        add_filter('aioseo_canonical_url', [$this, 'filter_aioseo_canonical'], 999);

        // Raw HTML injection (Fallback only if no SEO plugin is active)
        add_action('wp_head', [$this, 'inject_seo_meta_tags'], 5);
        
        // Schema Injection (Always injected as search engines parse multiple schemas perfectly)
        add_action('wp_head', [$this, 'inject_schema_structured_data'], 20);
    }

    /**
     * Helper to detect if any popular SEO plugin is active
     */
    private function is_seo_plugin_active() {
        return defined('WPSEO_VERSION') || 
               class_exists('RankMath') || 
               defined('AIOSEO_VERSION') || 
               function_exists('seopress_init') || 
               class_exists('All_in_One_SEO_Pack');
    }

    /**
     * Dynamically computes the SEO title and description for a calculator page
     */
    public function get_seo_data($post_id) {
        if (get_post_type($post_id) !== 'ust_calculator') {
            return null;
        }

        $custom_title = get_post_meta($post_id, '_ust_seo_title', true);
        $custom_desc = get_post_meta($post_id, '_ust_seo_desc', true);

        $state_slug = get_post_meta($post_id, '_ust_state_slug', true);
        $states = ust_get_states_data();
        $state_name = isset($states[$state_slug]) ? $states[$state_slug]['name'] : '';
        $calc_type = get_post_meta($post_id, '_ust_calc_type', true);

        if ($calc_type === 'other') {
            require_once UST_PATH . 'data/other-tax.php';
            $registry = ust_get_other_calculators_registry();
            $item = isset($registry[$state_slug]) ? $registry[$state_slug] : null;
            if ($item) {
                if (empty($custom_title)) {
                    $custom_title = $item['title_seo'];
                }
                if (empty($custom_desc)) {
                    $custom_desc = $item['desc_seo'];
                }
            }
        }

        // Fallbacks
        if (empty($custom_title)) {
            if ($calc_type === 'income-tax') {
                $custom_title = sprintf(__('%s Income Tax Calculator - Calfy', 'usa-state-tax-calculators'), $state_name);
            } elseif ($calc_type === 'property-tax') {
                $custom_title = sprintf(__('%s Property Tax Calculator - Calfy', 'usa-state-tax-calculators'), $state_name);
            } elseif ($calc_type === 'sales-tax') {
                $custom_title = sprintf(__('%s Sales Tax Calculator - Calfy', 'usa-state-tax-calculators'), $state_name);
            } else {
                $custom_title = get_the_title($post_id);
            }
        }

        if (empty($custom_desc)) {
            if ($calc_type === 'income-tax') {
                $custom_desc = sprintf(__('Estimate your annual take-home salary, federal income taxes, FICA withholdings, and %s state tax brackets using our free income tax calculator.', 'usa-state-tax-calculators'), $state_name);
            } elseif ($calc_type === 'property-tax') {
                $custom_desc = sprintf(__('Calculate your monthly and annual property taxes in %s. Select your county, apply senior/homestead exemptions, and view 5-year projections.', 'usa-state-tax-calculators'), $state_name);
            } elseif ($calc_type === 'sales-tax') {
                $custom_desc = sprintf(__('Calculate the combined state and local sales tax for purchases in %s. Apply tax-exempt status for groceries or medicine and view benchmarking costs.', 'usa-state-tax-calculators'), $state_name);
            } else {
                $post = get_post($post_id);
                $custom_desc = get_the_excerpt($post_id);
                if (empty($custom_desc) && $post) {
                    $custom_desc = wp_strip_all_tags(strip_shortcodes($post->post_content));
                    $custom_desc = wp_html_excerpt($custom_desc, 155, '...');
                }
            }
        }

        return [
            'title' => $custom_title,
            'desc'  => $custom_desc,
        ];
    }

    /* ── TITLE & META FILTERS FOR SEO PLUGINS ── */

    public function filter_wp_title($title) {
        if (is_singular('ust_calculator')) {
            $seo = $this->get_seo_data(get_the_ID());
            if ($seo) {
                return $seo['title'];
            }
        }
        return $title;
    }

    public function filter_wpseo_title($title) {
        if (is_singular('ust_calculator')) {
            $seo = $this->get_seo_data(get_the_ID());
            if ($seo) {
                return $seo['title'];
            }
        }
        return $title;
    }

    public function filter_wpseo_desc($desc) {
        if (is_singular('ust_calculator')) {
            $seo = $this->get_seo_data(get_the_ID());
            if ($seo) {
                return $seo['desc'];
            }
        }
        return $desc;
    }

    public function filter_wpseo_canonical($canonical) {
        if (is_singular('ust_calculator')) {
            return get_permalink(get_the_ID());
        }
        return $canonical;
    }

    public function filter_rankmath_title($title) {
        if (is_singular('ust_calculator')) {
            $seo = $this->get_seo_data(get_the_ID());
            if ($seo) {
                return $seo['title'];
            }
        }
        return $title;
    }

    public function filter_rankmath_desc($desc) {
        if (is_singular('ust_calculator')) {
            $seo = $this->get_seo_data(get_the_ID());
            if ($seo) {
                return $seo['desc'];
            }
        }
        return $desc;
    }

    public function filter_rankmath_canonical($canonical) {
        if (is_singular('ust_calculator')) {
            return get_permalink(get_the_ID());
        }
        return $canonical;
    }

    public function filter_seopress_title($title) {
        if (is_singular('ust_calculator')) {
            $seo = $this->get_seo_data(get_the_ID());
            if ($seo) {
                return $seo['title'];
            }
        }
        return $title;
    }

    public function filter_seopress_desc($desc) {
        if (is_singular('ust_calculator')) {
            $seo = $this->get_seo_data(get_the_ID());
            if ($seo) {
                return $seo['desc'];
            }
        }
        return $desc;
    }

    public function filter_seopress_canonical($canonical) {
        if (is_singular('ust_calculator')) {
            return get_permalink(get_the_ID());
        }
        return $canonical;
    }

    public function filter_aioseo_title($title) {
        if (is_singular('ust_calculator')) {
            $seo = $this->get_seo_data(get_the_ID());
            if ($seo) {
                return $seo['title'];
            }
        }
        return $title;
    }

    public function filter_aioseo_desc($desc) {
        if (is_singular('ust_calculator')) {
            $seo = $this->get_seo_data(get_the_ID());
            if ($seo) {
                return $seo['desc'];
            }
        }
        return $desc;
    }

    public function filter_aioseo_canonical($canonical) {
        if (is_singular('ust_calculator')) {
            return get_permalink(get_the_ID());
        }
        return $canonical;
    }

    /**
     * Injects standard SEO tags if NO external SEO plugin is active
     */
    public function inject_seo_meta_tags() {
        if (!is_singular('ust_calculator')) return;

        // Do not output tags if Yoast, Rank Math, etc. are active to prevent duplication
        if ($this->is_seo_plugin_active()) {
            return;
        }

        global $post;
        $post_id = $post->ID;

        $seo = $this->get_seo_data($post_id);
        if (!$seo) return;

        $custom_title = $seo['title'];
        $custom_desc = $seo['desc'];

        echo "\n<!-- USA State Tax Calculators SEO -->\n";
        echo '<title>' . esc_html($custom_title) . "</title>\n";
        echo '<meta name="description" content="' . esc_attr($custom_desc) . "\"/>\n";
        echo '<link rel="canonical" href="' . esc_url(get_permalink($post_id)) . "\" />\n";
        echo '<meta property="og:title" content="' . esc_attr($custom_title) . "\" />\n";
        echo '<meta property="og:description" content="' . esc_attr($custom_desc) . "\" />\n";
        echo '<meta property="og:url" content="' . esc_url(get_permalink($post_id)) . "\" />\n";
        echo '<meta property="og:type" content="article" />' . "\n";
        echo "<!-- End USA State Tax Calculators SEO -->\n\n";

        // Remove standard theme titles
        remove_action('wp_head', '_wp_render_title_tag', 1);
    }

    /**
     * Inject JSON-LD Schema Markups (WebApplication, FAQPage, BreadcrumbList, HowTo)
     */
    public function inject_schema_structured_data() {
        if (!is_singular('ust_calculator')) return;

        global $post;
        $post_id = $post->ID;
        $calc_type = get_post_meta($post_id, '_ust_calc_type', true);
        $state_slug = get_post_meta($post_id, '_ust_state_slug', true);

        $states = ust_get_states_data();
        $state_name = isset($states[$state_slug]) ? $states[$state_slug]['name'] : '';

        $seo = $this->get_seo_data($post_id);
        $title = $seo ? $seo['title'] : get_the_title($post_id);
        $desc = $seo ? $seo['desc'] : '';
        $url = get_permalink($post_id);
        $site_name = get_bloginfo('name');

        echo "\n<!-- USA State Tax Calculators Schema -->\n";

        /* 1 ── SoftwareApplication with aggregateRating */
        if ($calc_type === 'income-tax') {
            $features = ['Federal Tax brackets calculation', 'State Tax deduction settings', 'FICA Withholdings', 'Filing Status (Single/Married/Head)', 'Pre-tax benefits splits'];
        } elseif ($calc_type === 'property-tax') {
            $features = ['Assessed Value vs Market value calculations', 'Exemption Relief (Homestead/Senior/Veteran)', 'County-by-County Property Tax Rates', '5-Year appreciation projections'];
        } elseif ($calc_type === 'sales-tax') {
            $features = ['State baseline sales tax rate calculations', 'Local county/city surtax rate lookup', 'Exemption adjustments for groceries/medicines', 'Purchase benchmarking tables'];
        } else {
            $features = ['Interactive tax calculations', 'Standard IRS tax rules assessment', 'Detailed financial reports', 'Dynamic tax rate lookups'];
        }

        $software_schema = [
            '@context'               => 'https://schema.org',
            '@type'                  => ['SoftwareApplication', 'WebApplication'],
            'name'                   => $title,
            'description'            => $desc,
            'url'                    => $url,
            'inLanguage'             => 'en-US',
            'applicationCategory'    => 'FinanceApplication',
            'applicationSubCategory' => 'Calculator',
            'operatingSystem'        => 'Web Browser, Mobile, Desktop',
            'browserRequirements'    => 'Requires JavaScript. Requires HTML5.',
            'featureList'            => $features,
            'datePublished'          => get_the_date('Y-m-d', $post_id),
            'dateModified'           => get_the_modified_date('Y-m-d', $post_id),
            'offers'                 => [
                '@type'         => 'Offer',
                'price'         => '0',
                'priceCurrency' => 'USD',
                'availability'  => 'https://schema.org/InStock',
            ],
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => '4.8',
                'ratingCount' => '186',
                'bestRating'  => '5',
                'worstRating' => '1',
            ],
            'author'     => ['@type' => 'Organization', 'name' => $site_name, 'url' => home_url()],
            'publisher'  => ['@type' => 'Organization', 'name' => $site_name, 'url' => home_url()],
        ];
        $this->_echo_schema($software_schema);

        /* 2 ── FAQPage */
        $faqs = get_post_meta($post_id, '_ust_faqs', true);
        if (!empty($faqs) && is_array($faqs)) {
            $entities = [];
            foreach ($faqs as $faq) {
                if (!empty($faq['q']) && !empty($faq['a'])) {
                    $entities[] = [
                        '@type' => 'Question',
                        'name'  => $faq['q'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq['a']],
                    ];
                }
            }
            if (!empty($entities)) {
                $this->_echo_schema([
                    '@context'   => 'https://schema.org',
                    '@type'      => 'FAQPage',
                    'inLanguage' => 'en-US',
                    'mainEntity' => $entities,
                ]);
            }
        }

        /* 3 ── BreadcrumbList */
        if ($calc_type === 'income-tax') {
            $crumb_name = 'Income Tax Calculators';
            $crumb_slug = 'income-tax';
        } elseif ($calc_type === 'property-tax') {
            $crumb_name = 'Property Tax Calculators';
            $crumb_slug = 'property-tax';
        } elseif ($calc_type === 'sales-tax') {
            $crumb_name = 'Sales Tax Calculators';
            $crumb_slug = 'sales-tax';
        } else {
            $crumb_name = 'Tax Calculators';
            $crumb_slug = 'other';
        }
        $this->_echo_schema([
            '@context'        => 'https://schema.org',
            '@type'           => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => home_url()],
                [
                    '@type'    => 'ListItem',
                    'position' => 2,
                    'name'     => $crumb_name,
                    'item'     => home_url('/' . $crumb_slug . '-calculators/'),
                ],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $title, 'item' => $url],
            ],
        ]);

        /* 4 ── HowTo */
        if ($calc_type === 'income-tax') {
            $steps = [
                ['@type' => 'HowToStep', 'name' => 'Input Earnings', 'text' => 'Enter your gross annual salary or wage earnings.'],
                ['@type' => 'HowToStep', 'name' => 'Select Filing Status', 'text' => 'Choose Single, Married, or Head of Household W-4 status.'],
                ['@type' => 'HowToStep', 'name' => 'Add Deductions', 'text' => 'Enter pre-tax contributions such as traditional 401(k) or health premiums.'],
                ['@type' => 'HowToStep', 'name' => 'View Pay Breakdown', 'text' => 'Click calculate to view your take-home cash flow breakdown table.']
            ];
            $ht_name = 'How to Calculate ' . $state_name . ' Income Tax';
            $ht_desc = 'Calculate your estimated federal, state, and FICA tax withholdings in ' . $state_name . '.';
        } elseif ($calc_type === 'property-tax') {
            $steps = [
                ['@type' => 'HowToStep', 'name' => 'Input Home Value', 'text' => 'Enter the estimated market value of your property.'],
                ['@type' => 'HowToStep', 'name' => 'Choose County', 'text' => 'Select your county from the dropdown to load the local rate.'],
                ['@type' => 'HowToStep', 'name' => 'Check Exemptions', 'text' => 'Apply Homestead, Senior, or Veteran exemptions if applicable.'],
                ['@type' => 'HowToStep', 'name' => 'Review Projections', 'text' => 'View annual and monthly tax totals along with 5-year trend projections.']
            ];
            $ht_name = 'How to Estimate ' . $state_name . ' Property Tax';
            $ht_desc = 'Calculate property tax payments using county rates and exemptions in ' . $state_name . '.';
        } elseif ($calc_type === 'sales-tax') {
            $steps = [
                ['@type' => 'HowToStep', 'name' => 'Input Price', 'text' => 'Enter the purchase price of the items.'],
                ['@type' => 'HowToStep', 'name' => 'Select Jurisdiction', 'text' => 'Select your local city/county to load the local rate, or override manually.'],
                ['@type' => 'HowToStep', 'name' => 'Enter Exemptions', 'text' => 'Enter any tax-exempt cost values such as groceries or prescription drugs.'],
                ['@type' => 'HowToStep', 'name' => 'Verify Combined Cost', 'text' => 'Review the combined sales tax rate, tax amount, and total purchase cost.']
            ];
            $ht_name = 'How to Calculate ' . $state_name . ' Sales Tax';
            $ht_desc = 'Calculate sales tax payments using state/county rates and item exemptions in ' . $state_name . '.';
        } else {
            if ($state_slug === 'state-tax-comparison-calculator') {
                $steps = [
                    ['@type' => 'HowToStep', 'name' => 'Select States', 'text' => 'Choose your origin state and target destination state.'],
                    ['@type' => 'HowToStep', 'name' => 'Enter Income & Home Value', 'text' => 'Input your gross household income and estimated property market value.'],
                    ['@type' => 'HowToStep', 'name' => 'Compare Tax Burdens', 'text' => 'Click compare to see a side-by-side comparison of income and property taxes.'],
                    ['@type' => 'HowToStep', 'name' => 'Analyze Savings', 'text' => 'Review the net tax savings, cost of living adjustment, and total financial variance.']
                ];
                $ht_name = 'How to Compare State Taxes Side-by-Side';
                $ht_desc = 'Easily compare the combined income tax and property tax differences between any two US states.';
            } else {
                $steps = [
                    ['@type' => 'HowToStep', 'name' => 'Enter Financial Details', 'text' => 'Provide your financial inputs (income, gains, or expenses).'],
                    ['@type' => 'HowToStep', 'name' => 'Adjust Settings', 'text' => 'Configure specific tax brackets, rates, or exemptions.'],
                    ['@type' => 'HowToStep', 'name' => 'Perform Calculation', 'text' => 'Click the calculate button to trigger the tax estimation engine.'],
                    ['@type' => 'HowToStep', 'name' => 'Review Breakdown', 'text' => 'Examine the detailed reports, visual charts, and tax breakdown values.']
                ];
                $ht_name = 'How to Use the ' . $title;
                $ht_desc = 'Calculate your tax liabilities or savings in a few simple steps using our interactive calculator.';
            }
        }

        $this->_echo_schema([
            '@context'    => 'https://schema.org',
            '@type'       => 'HowTo',
            'name'        => $ht_name,
            'description' => $ht_desc,
            'step'        => $steps,
        ]);

        echo "<!-- End USA State Tax Calculators Schema -->\n\n";
    }

    private function _echo_schema($data) {
        echo '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "</script>\n";
    }
}
