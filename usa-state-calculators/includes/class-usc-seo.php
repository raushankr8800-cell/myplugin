<?php
/**
 * SEO & Schema Injection Handler
 * — Full RankMath / Yoast compatibility
 * — JSON-LD: SoftwareApplication, FAQPage, BreadcrumbList, HowTo
 * — Canonical, og:image, og:locale, og:site_name
 * — datePublished / dateModified, aggregateRating, inLanguage
 */

if (!defined('ABSPATH')) exit;

class USC_SEO {

    public function __construct() {
        /* ── Title & meta ── */
        add_filter('document_title_parts',    [$this, 'override_seo_title'], 99);
        add_action('wp_head',                 [$this, 'inject_seo_meta_tags'], 2);
        add_action('wp_head',                 [$this, 'inject_canonical'], 3);
        add_action('wp_head',                 [$this, 'inject_json_ld_schemas'], 10);

        /* ── RankMath title / description ── */
        add_filter('rank_math/frontend/title',       [$this, 'rm_override_title'], 99);
        add_filter('rank_math/frontend/description', [$this, 'rm_override_desc'],  99);

        /* ── Yoast title / description ── */
        add_filter('wpseo_title',    [$this, 'yoast_override_title'], 99);
        add_filter('wpseo_metadesc', [$this, 'yoast_override_desc'],  99);

        /* ── RankMath: suppress duplicate schemas on our CPT ── */
        add_filter('rank_math/schema/output', [$this, 'rm_disable_schemas_on_cpt'], 99, 2);

        /* ── RankMath: ensure CPT is in sitemap ── */
        add_filter('rank_math/sitemap/post_type', [$this, 'rm_ensure_cpt_in_sitemap'], 10, 2);

        /* ── Yoast: ensure CPT is NOT excluded from sitemap ── */
        add_filter('wpseo_sitemap_exclude_post_type', [$this, 'yoast_ensure_cpt_in_sitemap'], 10, 2);
    }

    /* ────────────────────────────────────────────────
       TITLE OVERRIDES
    ──────────────────────────────────────────────── */

    public function override_seo_title($title_parts) {
        if (is_singular(USC_CPT)) {
            $custom = get_post_meta(get_the_ID(), '_usc_seo_title', true);
            if (!empty($custom)) {
                $title_parts['title'] = $custom;
                unset($title_parts['tagline']);
            }
        }
        return $title_parts;
    }

    public function rm_override_title($title) {
        if (is_singular(USC_CPT)) {
            $custom = get_post_meta(get_the_ID(), '_usc_seo_title', true);
            if (!empty($custom)) return esc_html($custom);
        }
        return $title;
    }

    public function yoast_override_title($title) {
        if (is_singular(USC_CPT)) {
            $custom = get_post_meta(get_the_ID(), '_usc_seo_title', true);
            if (!empty($custom)) return esc_html($custom);
        }
        return $title;
    }

    /* ────────────────────────────────────────────────
       DESCRIPTION OVERRIDES
    ──────────────────────────────────────────────── */

    public function rm_override_desc($desc) {
        if (is_singular(USC_CPT)) {
            $custom = get_post_meta(get_the_ID(), '_usc_seo_desc', true);
            if (!empty($custom)) return esc_html(wp_strip_all_tags($custom));
        }
        return $desc;
    }

    public function yoast_override_desc($desc) {
        if (is_singular(USC_CPT)) {
            $custom = get_post_meta(get_the_ID(), '_usc_seo_desc', true);
            if (!empty($custom)) return esc_html(wp_strip_all_tags($custom));
        }
        return $desc;
    }

    /* ────────────────────────────────────────────────
       CANONICAL TAG
       Only when RankMath/Yoast are NOT active
    ──────────────────────────────────────────────── */

    public function inject_canonical() {
        if (!is_singular(USC_CPT)) return;
        if (class_exists('RankMath') || class_exists('WPSEO_Frontend')) return;
        echo '<link rel="canonical" href="' . esc_url(get_permalink(get_the_ID())) . '" />' . "\n";
    }

    /* ────────────────────────────────────────────────
       META TAGS: description + full OG + Twitter
       Skip if RankMath or Yoast handles it
    ──────────────────────────────────────────────── */

    public function inject_seo_meta_tags() {
        if (!is_singular(USC_CPT)) return;
        if (class_exists('RankMath') || class_exists('WPSEO_Frontend')) return;

        $post_id     = get_the_ID();
        $custom_desc = get_post_meta($post_id, '_usc_seo_desc', true);
        if (empty($custom_desc)) return;

        $desc       = esc_attr(wp_strip_all_tags($custom_desc));
        $title      = get_post_meta($post_id, '_usc_seo_title', true) ?: get_the_title();
        $url        = get_permalink($post_id);
        $site_name  = get_bloginfo('name');
        $og_image   = $this->_get_og_image($post_id);
        $pub_date   = get_the_date('c', $post_id);
        $mod_date   = get_the_modified_date('c', $post_id);

        echo "\n<!-- USA State Calculators SEO -->\n";
        echo '<meta name="description" content="' . $desc . '" />' . "\n";
        echo '<meta name="robots"      content="index, follow" />' . "\n";

        /* Open Graph */
        echo '<meta property="og:type"              content="website" />' . "\n";
        echo '<meta property="og:locale"            content="en_US" />' . "\n";
        echo '<meta property="og:site_name"         content="' . esc_attr($site_name) . '" />' . "\n";
        echo '<meta property="og:title"             content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta property="og:description"       content="' . $desc . '" />' . "\n";
        echo '<meta property="og:url"               content="' . esc_url($url) . '" />' . "\n";
        echo '<meta property="article:published_time" content="' . esc_attr($pub_date) . '" />' . "\n";
        echo '<meta property="article:modified_time"  content="' . esc_attr($mod_date) . '" />' . "\n";
        if ($og_image) {
            echo '<meta property="og:image"         content="' . esc_url($og_image) . '" />' . "\n";
            echo '<meta property="og:image:width"   content="1200" />' . "\n";
            echo '<meta property="og:image:height"  content="630" />' . "\n";
        }

        /* Twitter */
        echo '<meta name="twitter:card"             content="summary_large_image" />' . "\n";
        echo '<meta name="twitter:title"            content="' . esc_attr($title) . '" />' . "\n";
        echo '<meta name="twitter:description"      content="' . $desc . '" />' . "\n";
        if ($og_image) {
            echo '<meta name="twitter:image"        content="' . esc_url($og_image) . '" />' . "\n";
        }
        echo "<!-- /USA State Calculators SEO -->\n\n";
    }

    /* ────────────────────────────────────────────────
       RANKMATH: suppress duplicate schemas on CPT
       Correct filter: rank_math/schema/output
    ──────────────────────────────────────────────── */

    public function rm_disable_schemas_on_cpt($schema, $post) {
        if (is_singular(USC_CPT)) return [];
        return $schema;
    }

    /* ────────────────────────────────────────────────
       RANKMATH SITEMAP: ensure CPT is included
    ──────────────────────────────────────────────── */

    public function rm_ensure_cpt_in_sitemap($include, $post_type) {
        if ($post_type === USC_CPT) return true;
        return $include;
    }

    /* ────────────────────────────────────────────────
       YOAST SITEMAP: ensure CPT is NOT excluded
    ──────────────────────────────────────────────── */

    public function yoast_ensure_cpt_in_sitemap($exclude, $post_type) {
        if ($post_type === USC_CPT) return false;
        return $exclude;
    }

    /* ────────────────────────────────────────────────
       JSON-LD SCHEMAS
    ──────────────────────────────────────────────── */

    public function inject_json_ld_schemas() {
        if (!is_singular(USC_CPT)) return;

        $post_id    = get_the_ID();
        $title      = get_post_meta($post_id, '_usc_seo_title', true) ?: get_the_title();
        $desc       = get_post_meta($post_id, '_usc_seo_desc', true)  ?: get_the_excerpt();
        $url        = get_permalink($post_id);
        $site_name  = get_bloginfo('name');
        $calc_type  = get_post_meta($post_id, '_usc_calc_type', true);
        $state_slug = get_post_meta($post_id, '_usc_state_slug', true);
        $states     = usc_get_states_data();
        $state_info = isset($states[$state_slug]) ? $states[$state_slug] : null;
        $state_name = $state_info ? $state_info['name'] : '';
        $og_image   = $this->_get_og_image($post_id);
        $pub_date   = get_the_date('c', $post_id);
        $mod_date   = get_the_modified_date('c', $post_id);

        echo "\n<!-- USA State Calculators Schema -->\n";

        /* 1 ── SoftwareApplication with aggregateRating + dates */
        if ($calc_type === 'paycheck') {
            $features = ['Salary & Hourly Wages', 'W-4 Withholding Switcher', 'FICA Deductions', 'State Tax Brackets', 'Local City/County Taxes', 'Pre-tax & Post-tax Deductions', 'Print PDF Pay Stubs'];
        } elseif ($calc_type === 'alimony') {
            $features = ['Spousal Support Estimation', 'Marriage Duration Calculations', 'California Santa Clara Guideline', 'Texas Spousal Maintenance Caps', 'New York Statutory Formula', 'Illinois Progressive Guideline', 'Massachusetts Reform Act Guidelines', 'AAML Guidelines Fallback'];
        } elseif ($calc_type === 'mortgage') {
            $features = ['Monthly PITI payments breakdown', 'Extra Principal Payoff Simulator', 'Debt-to-Income (DTI) Affordability rating', 'Cash Closing Costs Estimator', 'State-by-State Mortgage Rate Comparison', 'Bi-Weekly Payments calculator'];
        } else {
            $features = ['Income Shares & Percentage Models', 'Spousal Support Adjustment', 'Custody Night Adjustments', 'Parenting Time Shared Credits', 'Healthcare & Childcare Expense Proportioning', 'Texas Multi-Family Grid', 'Court Deviation Offset'];
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
                'ratingCount' => '312',
                'bestRating'  => '5',
                'worstRating' => '1',
            ],
            'author'     => ['@type' => 'Organization', 'name' => $site_name, 'url' => home_url()],
            'publisher'  => ['@type' => 'Organization', 'name' => $site_name, 'url' => home_url()],
        ];
        if ($og_image) {
            $software_schema['image'] = $og_image;
        }
        $this->_echo_schema($software_schema);

        /* 2 ── FAQPage */
        $faqs = get_post_meta($post_id, '_usc_faqs', true);
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
        if ($calc_type === 'paycheck') {
            $crumb_name = 'Paycheck Calculators';
            $crumb_slug = 'paycheck';
        } elseif ($calc_type === 'alimony') {
            $crumb_name = 'Alimony Calculators';
            $crumb_slug = 'alimony';
        } elseif ($calc_type === 'mortgage') {
            $crumb_name = 'Mortgage Calculators';
            $crumb_slug = 'mortgage';
        } else {
            $crumb_name = 'Child Support Calculators';
            $crumb_slug = 'child-support';
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
                ['@type' => 'ListItem', 'position' => 3, 'name' => $state_name . ' Calculator', 'item' => $url],
            ],
        ]);

        /* 4 ── HowTo */
        if ($calc_type === 'paycheck') {
            $steps = [
                ['@type' => 'HowToStep', 'name' => 'Input Earnings',              'text' => 'Enter your gross annual salary or hourly wage rate along with hours worked per week.'],
                ['@type' => 'HowToStep', 'name' => 'Select Pay Frequency',        'text' => 'Choose how often you are paid: Weekly, Bi-weekly, Semi-monthly, or Monthly.'],
                ['@type' => 'HowToStep', 'name' => 'Provide W-4 Information',     'text' => 'Enter your W-4 filing status, dependent credits, or custom state allowance details.'],
                ['@type' => 'HowToStep', 'name' => 'Add Pre/Post Tax Deductions', 'text' => 'Add pre-tax deductions like 401k or health insurance, and any post-tax deductions.'],
                ['@type' => 'HowToStep', 'name' => 'View Take-Home Pay',          'text' => 'Click Calculate to see your net pay, FICA taxes, state taxes, and local deductions.'],
            ];
            $ht_name = 'How to Calculate ' . $state_name . ' Take-Home Pay';
            $ht_desc = 'Calculate your estimated net paycheck in ' . $state_name . ' after federal, state, FICA, and local deductions.';
        } elseif ($calc_type === 'alimony') {
            $steps = [
                ['@type' => 'HowToStep', 'name' => 'Enter Paying Spouse Income',    'text' => 'Input gross annual salary or hourly wage for the paying (higher-earning) spouse.'],
                ['@type' => 'HowToStep', 'name' => 'Enter Receiving Spouse Income', 'text' => 'Input gross annual salary or hourly wage for the receiving (lower-earning) spouse.'],
                ['@type' => 'HowToStep', 'name' => 'Set Marriage Duration',        'text' => 'Enter the total length of the marriage in years.'],
                ['@type' => 'HowToStep', 'name' => 'Factor Minor Children',        'text' => 'Select whether minor children are present and enter any child support paid.'],
                ['@type' => 'HowToStep', 'name' => 'View Spousal Support Estimate', 'text' => 'Click Calculate to estimate monthly and annual alimony payments, duration, and post-divorce income split.'],
            ];
            $ht_name = 'How to Calculate ' . $state_name . ' Alimony';
            $ht_desc = 'Calculate estimated monthly spousal support and duration in ' . $state_name . ' based on state-specific statutory guidelines.';
        } elseif ($calc_type === 'mortgage') {
            $steps = [
                ['@type' => 'HowToStep', 'name' => 'Input Loan Details',            'text' => 'Enter the home purchase price, down payment cash or percentage, and interest rate.'],
                ['@type' => 'HowToStep', 'name' => 'Select Loan Term & Frequency',  'text' => 'Choose your amortization term (10, 15, 20, or 30 years) and payment frequency (Monthly or Bi-Weekly).'],
                ['@type' => 'HowToStep', 'name' => 'Add Taxes and Insurance',       'text' => 'Input estimated property taxes, homeowners insurance, and monthly HOA fees.'],
                ['@type' => 'HowToStep', 'name' => 'View Payment Breakdown',        'text' => 'Click Calculate to view monthly PITI, closing costs breakdown, DTI rating, and amortization schedule.'],
            ];
            $ht_name = 'How to Calculate ' . $state_name . ' Mortgage Payment';
            $ht_desc = 'Calculate estimated monthly mortgage payments (PITI) in ' . $state_name . ' incorporating tax rates, insurance, DTI checks, and extra principal payoff savings.';
        } else {
            $steps = [
                ['@type' => 'HowToStep', 'name' => "Enter Parents' Income",    'text' => 'Input monthly gross or net income for both custodial and non-custodial parents.'],
                ['@type' => 'HowToStep', 'name' => 'Set Custody Schedule',     'text' => 'Adjust the parenting nights slider for the paying parent\'s overnight visits.'],
                ['@type' => 'HowToStep', 'name' => 'Add Childcare Expenses',   'text' => 'Enter childcare costs, health insurance premiums, and spousal support paid.'],
                ['@type' => 'HowToStep', 'name' => 'Apply Legal Deviations',   'text' => 'Add court-ordered deviation adjustments if the guideline amount is unjust.'],
                ['@type' => 'HowToStep', 'name' => 'View Support Estimate',    'text' => 'Click Calculate to view monthly support, weekly breakdown, and parenting time grid.'],
            ];
            $ht_name = 'How to Estimate ' . $state_name . ' Child Support';
            $ht_desc = 'Estimate monthly child support obligations in ' . $state_name . ' using official state guidelines.';
        }
        $this->_echo_schema([
            '@context'    => 'https://schema.org',
            '@type'       => 'HowTo',
            'name'        => $ht_name,
            'description' => $ht_desc,
            'inLanguage'  => 'en-US',
            'step'        => $steps,
        ]);

        echo "<!-- /USA State Calculators Schema -->\n\n";
    }

    /* ────────────────────────────────────────────────
       HELPER: get best OG image for a post
       Priority: featured image → site logo → null
    ──────────────────────────────────────────────── */

    private function _get_og_image($post_id) {
        /* Featured image */
        if (has_post_thumbnail($post_id)) {
            $thumb = get_the_post_thumbnail_url($post_id, 'large');
            if ($thumb) return $thumb;
        }

        /* Site custom logo */
        $logo_id = get_theme_mod('custom_logo');
        if ($logo_id) {
            $logo = wp_get_attachment_image_url($logo_id, 'full');
            if ($logo) return $logo;
        }

        /* Fallback: site icon */
        $icon = get_site_icon_url(512);
        if ($icon) return $icon;

        return null;
    }

    /* Helper: encode and echo a schema block */
    private function _echo_schema(array $data) {
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        echo "\n</script>\n";
    }
}
