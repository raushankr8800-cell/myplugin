<?php
/**
 * Single post template fallback for USA State Calculators
 */

get_header();

$post_id = get_the_ID();
$calc_type = get_post_meta($post_id, '_usc_calc_type', true);
$state_slug = get_post_meta($post_id, '_usc_state_slug', true);

// Always detect the correct type from the slug to auto-heal any corrupted meta values
$post_slug = get_post_field('post_name', $post_id);
$detected_type = 'paycheck';
if (strpos($post_slug, 'child-support') !== false) {
    $detected_type = 'child-support';
} elseif (strpos($post_slug, 'alimony') !== false) {
    $detected_type = 'alimony';
} elseif (strpos($post_slug, 'mortgage') !== false) {
    $detected_type = 'mortgage';
}

if ($calc_type !== $detected_type) {
    $calc_type = $detected_type;
    update_post_meta($post_id, '_usc_calc_type', $calc_type);
}

if (empty($state_slug)) {
    $post_slug = get_post_field('post_name', $post_id);
    $state_slug = $post_slug;
    $prefixes = ['paycheck-calculator-', 'child-support-calculator-', 'alimony-calculator-', 'mortgage-calculator-'];
    $suffixes = ['-paycheck-calculator', '-child-support-calculator', '-alimony-calculator', '-mortgage-calculator'];
    $state_slug = str_replace($prefixes, '', $state_slug);
    $state_slug = str_replace($suffixes, '', $state_slug);
    update_post_meta($post_id, '_usc_state_slug', $state_slug);
}

$calc_html  = get_post_meta($post_id, '_usc_calc_html', true);
$calc_css   = get_post_meta($post_id, '_usc_calc_css', true);
$calc_js    = get_post_meta($post_id, '_usc_calc_js', true);

$faqs       = get_post_meta($post_id, '_usc_faqs', true);
$states     = usc_get_states_data();
$state_info = isset($states[$state_slug]) ? $states[$state_slug] : null;

if ($state_info && (empty($faqs) || !is_array($faqs) || count($faqs) < 10)) {
    if ($calc_type === 'paycheck') {
        $faqs = usc_get_default_paycheck_faqs($state_info);
    } elseif ($calc_type === 'alimony') {
        $faqs = usc_get_default_alimony_faqs($state_info);
    } elseif ($calc_type === 'mortgage') {
        $faqs = usc_get_default_mortgage_faqs($state_info);
    } else {
        $faqs = usc_get_default_child_support_faqs($state_info);
    }
    update_post_meta($post_id, '_usc_faqs', $faqs);
}

$seo_title = get_post_meta($post_id, '_usc_seo_title', true);
if ($state_info && (empty($seo_title) || preg_match('/\b202\d\b/', $seo_title) || strpos($seo_title, '| Take-Home Pay') !== false || strpos($seo_title, '| Estimates') !== false)) {
    if ($calc_type === 'alimony') {
        $new_seo_title = usc_get_default_alimony_seo_title($state_info['name']);
    } elseif ($calc_type === 'mortgage') {
        $new_seo_title = usc_get_default_mortgage_seo_title($state_info['name']);
    } else {
        $new_seo_title = usc_get_default_seo_title($calc_type, $state_info['name']);
    }
    update_post_meta($post_id, '_usc_seo_title', $new_seo_title);
}

$seo_desc = get_post_meta($post_id, '_usc_seo_desc', true);
if ($state_info && (empty($seo_desc) || strpos($seo_desc, 'federal, state, FICA, and local tax') !== false || strpos($seo_desc, 'Accurate calculations based on') !== false || strpos($seo_desc, 'Calculate spousal support') !== false)) {
    if ($calc_type === 'alimony') {
        $new_seo_desc = usc_get_default_alimony_seo_desc($state_info);
    } elseif ($calc_type === 'mortgage') {
        $new_seo_desc = usc_get_default_mortgage_seo_desc($state_info);
    } else {
        $new_seo_desc = usc_get_default_seo_desc($calc_type, $state_info);
    }
    update_post_meta($post_id, '_usc_seo_desc', $new_seo_desc);
}

$post_content   = get_post_field('post_content', $post_id);
$content_outdated = false;
if (empty($post_content) || strpos($post_content, '<!-- usc-v5-article -->') === false || strpos($post_content, '<h2>13. Frequently Asked Questions') !== false) {
    $content_outdated = true;
}
if ($state_info && $content_outdated) {
    if ($calc_type === 'paycheck') {
        $new_content = usc_get_default_paycheck_article_content($state_info);
    } elseif ($calc_type === 'alimony') {
        $new_content = usc_get_default_alimony_article_content($state_info);
    } elseif ($calc_type === 'mortgage') {
        $new_content = usc_get_default_mortgage_article_content($state_info);
    } else {
        $new_content = usc_get_default_child_support_article_content($state_info);
    }
    wp_update_post(['ID' => $post_id, 'post_content' => $new_content]);
}

$template_ver = get_post_meta($post_id, '_usc_template_version', true);
$expected_ver = '16';

$is_outdated = false;
if ($template_ver !== $expected_ver) {
    $is_outdated = true;
} elseif (!empty($calc_html)) {
    if ($calc_type === 'paycheck' && (strpos($calc_html, 'pre-tax-med') === false || strpos($calc_html, 'btnW4New') === false)) {
        $is_outdated = true;
    } elseif ($calc_type === 'child-support' && (strpos($calc_html, 'alimony-paid') === false || strpos($calc_html, 'other-children-supported') === false)) {
        $is_outdated = true;
    } elseif ($calc_type === 'alimony' && (strpos($calc_html, 'payor-filing-status') === false || strpos($calc_css, '#8b5cf6') !== false)) {
        $is_outdated = true;
    } elseif ($calc_type === 'mortgage' && (strpos($calc_html, 'mortgage-v1') === false || strpos($calc_js, 'mortgageStateDictionary') === false)) {
        $is_outdated = true;
    }
}
if (empty($calc_html) || $is_outdated) {
    $defaults  = usc_get_default_templates($calc_type, $state_slug);
    $calc_html = $defaults['html'];
    $calc_css  = $defaults['css'];
    $calc_js   = $defaults['js'];
    update_post_meta($post_id, '_usc_calc_html', $calc_html);
    update_post_meta($post_id, '_usc_calc_css', $calc_css);
    update_post_meta($post_id, '_usc_calc_js', $calc_js);
    update_post_meta($post_id, '_usc_template_version', $expected_ver);
}

$states     = usc_get_states_data();
$state_name = isset($states[$state_slug]) ? $states[$state_slug]['name'] : 'USA';
$post_title = get_the_title();

if (!empty($calc_css)) {
    echo '<style>' . $calc_css . '</style>';
}
?>
<div class="usc-calculator-page-wrapper">
    <div class="page">
        <!-- Banner Header -->
        <div class="banner">
            <div class="banner-title">
                <?php echo esc_html(strtoupper($state_name)); ?><br>
                <?php 
                if ($calc_type === 'paycheck') {
                    echo 'PAYCHECK CALCULATOR';
                } elseif ($calc_type === 'alimony') {
                    echo 'ALIMONY CALCULATOR';
                } elseif ($calc_type === 'mortgage') {
                    echo 'MORTGAGE CALCULATOR';
                } else {
                    echo 'CHILD SUPPORT CALCULATOR';
                }
                ?>
            </div>
            <div class="banner-sub">
                <?php if ($calc_type === 'paycheck') : ?>
                    FEDERAL TAXES · STATE TAXES · FICA DEDUCTIONS · TAKE-HOME PAY
                <?php elseif ($calc_type === 'alimony') : ?>
                    SPOUSAL SUPPORT · MAINTENANCE GUIDELINES · INCOME SPLIT · ESTIMATES
                <?php elseif ($calc_type === 'mortgage') : ?>
                    PITI BREAKDOWN · CLOSING COSTS · AMORTIZATION SCHEDULE · PAYOFF SIMULATOR
                <?php else : ?>
                    CUSTODY SCHEDULES · BASIC OBLIGATIONS · PROPORTIONAL SHARE · ESTIMATES
                <?php endif; ?>
            </div>
            <div class="badge">
                <?php 
                if ($calc_type === 'paycheck') {
                    echo '💵 ESTIMATE NET SALARY';
                } elseif ($calc_type === 'alimony') {
                    echo '⚖️ ESTIMATE SPOUSAL SUPPORT';
                } elseif ($calc_type === 'mortgage') {
                    echo '🏡 ESTIMATE MORTGAGE PAYMENT';
                } else {
                    echo '👪 ESTIMATE MONTHLY OBLIGATION';
                }
                ?> — FREE
            </div>
        </div>

        <!-- Calculator -->
        <div class="inner">
            <div class="usc-calculator-container">
                <?php echo $calc_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>

            <?php
            $global_enabled = get_option('usc_global_ads_enabled', '1');
            if ($global_enabled === '1') :
                $ads_code = get_post_meta($post_id, '_usc_ads_code', true);
                if (empty($ads_code)) {
                    $ads_code = get_option('usc_global_ads_code', '');
                }
                if (!empty($ads_code)) : ?>
                    <div class="usc-ads-container" style="margin:20px auto;text-align:center;max-width:100%;">
                        <?php
                        // SECURITY: Allow only safe ad-related tags; strip anything dangerous
                        $allowed_ad_tags = [
                            'ins'    => ['class' => [], 'style' => [], 'data-ad-client' => [], 'data-ad-slot' => [], 'data-ad-format' => [], 'data-full-width-responsive' => []],
                            'script' => ['async' => [], 'src' => [], 'crossorigin' => []],
                            'div'    => ['class' => [], 'style' => [], 'id' => []],
                            'iframe' => ['src' => [], 'width' => [], 'height' => [], 'frameborder' => [], 'scrolling' => [], 'style' => []],
                        ];
                        echo wp_kses($ads_code, $allowed_ad_tags);
                        ?>
                    </div>
                <?php endif;
            endif; ?>



            <?php if (get_post_meta($post_id, '_usc_enable_lead_capture', true) === '1') : ?>
                <div id="usc-lead-capture-box" class="det-card" style="display:none;margin-top:20px;background:var(--soft);border-color:#fca5a5;text-align:center;">
                    <div class="det-title">🔒 UNLOCK YOUR ESTIMATE REPORT</div>
                    <p style="font-size:12.5px;color:#b91c1c;margin-bottom:15px;">Enter your name and email below to instantly view your full take-home pay or child support breakdown.</p>
                    <div class="field" style="max-width:320px;margin:0 auto 12px;">
                        <input type="text" id="usc-lead-name" class="inp" placeholder="Your Full Name" required>
                    </div>
                    <div class="field" style="max-width:320px;margin:0 auto 15px;">
                        <input type="email" id="usc-lead-email" class="inp" placeholder="Your Email Address" required>
                    </div>
                    <button class="calc-btn" onclick="submitUscLead()" style="max-width:320px;margin:0 auto;">UNLOCK RESULTS</button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Article Section with Read Full Article Button -->
    <div class="usc-article-wrapper">
        <div class="usc-article-container">
            <h1 class="usc-article-title"><?php echo esc_html($post_title); ?></h1>

            <!-- Short preview always visible -->
            <div class="usc-article-preview" id="usc-article-preview">
                <?php
                $raw   = get_post_field('post_content', $post_id);
                $plain = wp_strip_all_tags($raw);
                echo '<p style="font-size:13.5px;line-height:1.65;color:#6b7280;margin:0;">' . esc_html(wp_trim_words($plain, 30, '...')) . '</p>';
                ?>
            </div>

            <!-- Full article, hidden by default -->
            <div class="usc-article-full" id="usc-article-full">
                <div class="usc-article-content">
                    <?php
                    while (have_posts()) :
                        the_post();
                        the_content();
                    endwhile;
                    ?>
                </div>
            </div>

            <!-- Read Full Article Button -->
            <div class="usc-read-full-wrap">
                <button class="usc-read-full-btn" id="usc-read-full-btn" onclick="uscToggleArticle(this)">
                    Read Full Article
                </button>
            </div>
        </div>
    </div>

    <!-- FAQ Section — Calfy Style -->
    <?php if (!empty($faqs) && is_array($faqs)) : ?>
        <div class="usc-faq-section-wrapper">
            <div class="usc-faq-container">
                <h2 class="usc-faq-section-title">
                    Faq About <?php echo esc_html($post_title); ?>
                </h2>
                <div class="usc-faq-accordion">
                    <?php foreach ($faqs as $index => $faq) :
                        if (empty($faq['q']) || empty($faq['a'])) continue;
                        $is_first = ($index === 0);
                        ?>
                        <div class="usc-faq-item<?php echo $is_first ? ' active' : ''; ?>">
                            <div class="usc-faq-question" onclick="toggleFaqAccordion(this)">
                                <span><?php echo esc_html($faq['q']); ?></span>
                                <span class="usc-faq-icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                </span>
                            </div>
                            <div class="usc-faq-answer"<?php echo $is_first ? ' style="max-height:600px;border-top-width:1px;"' : ''; ?>>
                                <p><?php echo esc_html($faq['a']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Related Calculators — Exact Calfy fxtool-module-card style -->
    <div class="usc-related-calculators-wrapper">
        <div class="usc-related-calculators-container">
            <h2 class="usc-related-section-title">RELATED TOOLS</h2>
            <div class="usc-related-grid usc-reveal">
                <?php
                // Icon colors for variety — same soft palette as Calfy
                $icon_palettes = [
                    ['bg' => '#f5f3ff', 'emoji' => '⚖️'],
                    ['bg' => '#f0fdf4', 'emoji' => '💵'],
                    ['bg' => '#f0f9ff', 'emoji' => '👪'],
                    ['bg' => '#fff7ed', 'emoji' => '🔢'],
                ];

                $all_types = ['paycheck', 'child-support', 'alimony'];
                $opposite_types = array_filter($all_types, function($t) use ($calc_type) {
                    return $t !== $calc_type;
                });
                $card_index    = 0;

                foreach ($opposite_types as $opp_type) {
                    $opp_slug = $state_slug . '-' . $opp_type . '-calculator';
                    $opp_post = get_page_by_path($opp_slug, OBJECT, USC_CPT);
                    if ($opp_post) {
                        $opp_url = get_permalink($opp_post->ID);
                        if ($opp_type === 'paycheck') {
                            $opp_title = strtoupper($state_name) . ' PAYCHECK CALCULATOR';
                            $opp_desc = 'SALARY · FEDERAL TAX · STATE TAX · FICA · TAKE-HOME PAY';
                            $emoji = '💵';
                            $bg_col = '#f0fdf4';
                        } elseif ($opp_type === 'alimony') {
                            $opp_title = strtoupper($state_name) . ' ALIMONY CALCULATOR';
                            $opp_desc = 'SPOUSAL SUPPORT · DURATION · MAINTENANCE · INCOME SPLIT';
                            $emoji = '⚖️';
                            $bg_col = '#f5f3ff';
                        } else {
                            $opp_title = strtoupper($state_name) . ' CHILD SUPPORT CALCULATOR';
                            $opp_desc = 'CUSTODY SCHEDULE · INCOME SHARE · SUPPORT OBLIGATIONS · ESTIMATES';
                            $emoji = '👪';
                            $bg_col = '#f0f9ff';
                        }
                        $pal = $icon_palettes[$card_index % 4];
                        $card_index++;
                        ?>
                        <a href="<?php echo esc_url($opp_url); ?>" class="usc-related-card">
                            <div class="usc-related-card-icon" style="background:<?php echo esc_attr($bg_col); ?>">
                                <span aria-hidden="true"><?php echo $emoji; ?></span>
                            </div>
                            <div class="usc-related-card-content">
                                <h3><?php echo esc_html($opp_title); ?></h3>
                                <p><?php echo esc_html($opp_desc); ?></p>
                            </div>
                            <span class="usc-related-arrow" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </span>
                        </a>
                        <?php
                    }
                }

                $popular_states  = ['california', 'texas', 'florida', 'new-york'];
                $displayed_count = 0;
                foreach ($popular_states as $p_slug) {
                    if ($p_slug === $state_slug || $displayed_count >= 2) continue;
                    $p_target = $p_slug . '-' . $calc_type . '-calculator';
                    $p_post   = get_page_by_path($p_target, OBJECT, USC_CPT);
                    if ($p_post) {
                        $p_info  = $states[$p_slug];
                        $p_url   = get_permalink($p_post->ID);
                        if ($calc_type === 'paycheck') {
                            $p_title = strtoupper($p_info['name']) . ' PAYCHECK CALCULATOR';
                            $p_desc  = 'FEDERAL · STATE TAX · FICA · NET PAY ESTIMATE';
                            $emoji = '💵';
                            $bg_col = '#f0fdf4';
                        } elseif ($calc_type === 'alimony') {
                            $p_title = strtoupper($p_info['name']) . ' ALIMONY CALCULATOR';
                            $p_desc  = 'SPOUSAL SUPPORT ESTIMATES · STATUTORY GUIDELINES';
                            $emoji = '⚖️';
                            $bg_col = '#f5f3ff';
                        } else {
                            $p_title = strtoupper($p_info['name']) . ' CHILD SUPPORT CALCULATOR';
                            $p_desc  = 'INCOME SHARES · CUSTODY NIGHTS · MONTHLY OBLIGATION';
                            $emoji = '👪';
                            $bg_col = '#f0f9ff';
                        }
                        $card_index++;
                        ?>
                        <a href="<?php echo esc_url($p_url); ?>" class="usc-related-card">
                            <div class="usc-related-card-icon" style="background:<?php echo esc_attr($bg_col); ?>">
                                <span aria-hidden="true"><?php echo $emoji; ?></span>
                            </div>
                            <div class="usc-related-card-content">
                                <h3><?php echo esc_html($p_title); ?></h3>
                                <p><?php echo esc_html($p_desc); ?></p>
                            </div>
                            <span class="usc-related-arrow" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </span>
                        </a>
                        <?php
                        $displayed_count++;
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php
if (!empty($calc_js)) {
    echo '<script data-cfasync="false">' . $calc_js . '</script>';
}
?>
<script data-cfasync="false">
function toggleFaqAccordion(element) {
    var item = element.parentElement;
    var isActive = item.classList.contains('active');
    document.querySelectorAll('.usc-faq-item').forEach(function(el) {
        el.classList.remove('active');
        var ans = el.querySelector('.usc-faq-answer');
        ans.style.maxHeight = '0';
        ans.style.borderTopWidth = '0';
    });
    if (!isActive) {
        item.classList.add('active');
        var ans = item.querySelector('.usc-faq-answer');
        ans.style.maxHeight = '600px';
        ans.style.borderTopWidth = '1px';
    }
}

function uscToggleArticle(btn) {
    var full    = document.getElementById('usc-article-full');
    var preview = document.getElementById('usc-article-preview');
    var isOpen  = full.classList.contains('usc-article-open');
    if (!isOpen) {
        full.classList.add('usc-article-open');
        preview.style.display = 'none';
        btn.textContent = 'Show Less';
    } else {
        full.classList.remove('usc-article-open');
        preview.style.display = 'block';
        btn.textContent = 'Read Full Article';
    }
}

var leadCaptureActive = <?php echo (get_post_meta($post_id, '_usc_enable_lead_capture', true) === '1') ? 'true' : 'false'; ?>;
var leadUnlocked = false;
var originalCalculate = null;

function checkLeadBeforeCalculate(calcFunc, force) {
    if (leadCaptureActive && !leadUnlocked) {
        if (force === true) {
            var resEl = document.getElementById("results");
            if (resEl) resEl.style.display = "none";
            var leadBox = document.getElementById("usc-lead-capture-box");
            if (leadBox) { leadBox.style.display = "block"; leadBox.scrollIntoView({behavior:"smooth",block:"start"}); }
        }
    } else {
        calcFunc(force);
        trackUscUsage(<?php echo $post_id; ?>);
        if (typeof showFooterActions === 'function') { setTimeout(showFooterActions, 300); }
    }
}

function submitUscLead() {
    var name  = document.getElementById("usc-lead-name").value.trim();
    var email = document.getElementById("usc-lead-email").value.trim();
    if (!name || !email) { alert("Please enter both your name and email address."); return; }
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "<?php echo admin_url('admin-ajax.php'); ?>", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4 && xhr.status === 200) {
            try {
                var r = JSON.parse(xhr.responseText);
                if (r.success) {
                    leadUnlocked = true;
                    var lb = document.getElementById("usc-lead-capture-box");
                    if (lb) lb.style.display = "none";
                    if (originalCalculate) originalCalculate(true);
                } else { alert("Error saving your details. Please try again."); }
            } catch(e) {
                leadUnlocked = true;
                var lb = document.getElementById("usc-lead-capture-box");
                if (lb) lb.style.display = "none";
                if (originalCalculate) originalCalculate(true);
            }
        }
    };
    var nonce = (typeof uscAjax !== 'undefined') ? uscAjax.nonce : '';
    xhr.send("action=usc_submit_lead&post_id=<?php echo $post_id; ?>&nonce=" + encodeURIComponent(nonce) + "&name=" + encodeURIComponent(name) + "&email=" + encodeURIComponent(email));
}

function trackUscUsage(postId) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "<?php echo admin_url('admin-ajax.php'); ?>", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    var nonce = (typeof uscAjax !== 'undefined') ? uscAjax.nonce : '';
    xhr.send("action=usc_track_usage&post_id=" + postId + "&nonce=" + encodeURIComponent(nonce));
}

// Standalone usage tracking
trackUscUsage(<?php echo $post_id; ?>);



/* ── Related Cards staggered reveal (IntersectionObserver) ── */
(function() {
    var grid = document.querySelector('.usc-related-grid');
    if (!grid) return;
    var cards = grid.querySelectorAll('.usc-related-card');
    if (!cards.length) return;

    function revealCards() {
        cards.forEach(function(card, i) {
            setTimeout(function() {
                card.classList.add('usc-visible');
            }, i * 60);
        });
    }

    if ('IntersectionObserver' in window) {
        var observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    revealCards();
                    observer.disconnect();
                }
            });
        }, { threshold: 0.1 });
        observer.observe(grid);
    } else {
        revealCards();
    }
})();
</script>
<?php
get_footer();
