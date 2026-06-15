<?php
/**
 * Custom Editor Metaboxes for State Calculators
 */

if (!defined('ABSPATH')) exit;

class USC_Metaboxes {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_calculators_metaboxes']);
        add_action('save_post', [$this, 'save_calculators_meta']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_code_editor_assets']);
    }

    /**
     * Enqueue CodeMirror for HTML, CSS, JS editing
     */
    public function enqueue_code_editor_assets($hook) {
        global $post;
        if (($hook === 'post.php' || $hook === 'post-new.php') && get_post_type($post) === USC_CPT) {
            // Enqueue code editor for html
            $settings_html = wp_enqueue_code_editor(['type' => 'text/html']);
            // Enqueue code editor for css
            $settings_css = wp_enqueue_code_editor(['type' => 'text/css']);
            // Enqueue code editor for js
            $settings_js = wp_enqueue_code_editor(['type' => 'application/javascript']);

            wp_add_inline_script('code-editor', sprintf(
                'jQuery(document).ready(function($) {
                    if ($("#usc_calc_html").length) { wp.codeEditor.initialize("usc_calc_html", %s); }
                    if ($("#usc_calc_css").length) { wp.codeEditor.initialize("usc_calc_css", %s); }
                    if ($("#usc_calc_js").length) { wp.codeEditor.initialize("usc_calc_js", %s); }
                });',
                wp_json_encode($settings_html),
                wp_json_encode($settings_css),
                wp_json_encode($settings_js)
            ));
        }
    }

    /**
     * Add Metaboxes to CPT
     */
    public function add_calculators_metaboxes() {
        add_meta_box(
            'usc_settings_metabox',
            __('Calculator Configurations & Code Editor', 'usa-state-calculators'),
            [$this, 'render_settings_metabox'],
            USC_CPT,
            'normal',
            'high'
        );
        add_meta_box(
            'usc_seo_metabox',
            __('SEO & Schema Settings', 'usa-state-calculators'),
            [$this, 'render_seo_metabox'],
            USC_CPT,
            'normal',
            'default'
        );
    }

    /**
     * Render Settings Metabox
     */
    public function render_settings_metabox($post) {
        wp_nonce_field('usc_save_settings', 'usc_settings_nonce');

        $calc_type = get_post_meta($post->ID, '_usc_calc_type', true);
        $state_slug = get_post_meta($post->ID, '_usc_state_slug', true);
        $calc_html = get_post_meta($post->ID, '_usc_calc_html', true);
        $calc_css = get_post_meta($post->ID, '_usc_calc_css', true);
        $calc_js = get_post_meta($post->ID, '_usc_calc_js', true);

        $states = usc_get_states_data();
        ?>
        <div class="usc-meta-group">
            <div class="usc-meta-group-title">
                <span class="dashicons dashicons-admin-generic"></span> Basic Calculator Settings
            </div>
            
            <div class="usc-meta-row">
                <label for="usc_calc_type"><?php _e('Calculator Type', 'usa-state-calculators'); ?></label>
                <select name="usc_calc_type" id="usc_calc_type">
                    <option value="paycheck" <?php selected($calc_type, 'paycheck'); ?>>Paycheck Calculator</option>
                    <option value="child-support" <?php selected($calc_type, 'child-support'); ?>>Child Support Calculator</option>
                    <option value="alimony" <?php selected($calc_type, 'alimony'); ?>>Alimony Calculator</option>
                    <option value="mortgage" <?php selected($calc_type, 'mortgage'); ?>>Mortgage Calculator</option>
                </select>
            </div>

            <div class="usc-meta-row">
                <label for="usc_state_slug"><?php _e('US State Context', 'usa-state-calculators'); ?></label>
                <select name="usc_state_slug" id="usc_state_slug">
                    <option value=""><?php _e('-- Select State --', 'usa-state-calculators'); ?></option>
                    <?php foreach ($states as $slug => $state) : ?>
                        <option value="<?php echo esc_attr($slug); ?>" <?php selected($state_slug, $slug); ?>><?php echo esc_html($state['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="usc-meta-group">
            <div class="usc-meta-group-title">
                <span class="dashicons dashicons-megaphone"></span> Advertising & Lead Capture
            </div>

            <div class="usc-meta-row">
                <label for="usc_ads_code"><?php _e('Native Ads Code (AdSense / Custom Ads)', 'usa-state-calculators'); ?></label>
                <textarea name="usc_ads_code" id="usc_ads_code" style="width:100%; height:80px; font-family: monospace; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;" placeholder="<?php _e('Paste AdSense or custom ad script code here...', 'usa-state-calculators'); ?>"><?php echo esc_textarea(get_post_meta($post->ID, '_usc_ads_code', true)); ?></textarea>
            </div>

            <div class="usc-meta-row">
                <label for="usc_enable_lead_capture" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="usc_enable_lead_capture" id="usc_enable_lead_capture" value="1" <?php checked(get_post_meta($post->ID, '_usc_enable_lead_capture', true), '1'); ?> style="margin: 0;" />
                    <strong><?php _e('Enable Lead Capture Form', 'usa-state-calculators'); ?></strong>
                </label>
                <div class="usc-meta-info-alert" style="margin-top: 8px;">
                    <p><?php _e('When checked, users will be prompted to enter their Name and Email address to unlock full calculation reports. The information will be stored in your Captured Leads registry.', 'usa-state-calculators'); ?></p>
                </div>
            </div>
        </div>

        <div class="usc-meta-group">
            <div class="usc-meta-group-title">
                <span class="dashicons dashicons-editor-code"></span> Layout & Code Editors
            </div>
            <div class="usc-meta-info-alert">
                <p><strong><?php _e('Note:', 'usa-state-calculators'); ?></strong> <?php _e('These editors allow scoping state-specific HTML markup, CSS styling, and JavaScript calculation routines directly into the single page template.', 'usa-state-calculators'); ?></p>
            </div>

            <div class="usc-meta-row">
                <label><?php _e('Custom HTML / Layout Markup', 'usa-state-calculators'); ?></label>
                <div class="usc-editor-container">
                    <div class="usc-editor-header">
                        <span><?php _e('Markup for Calculator Wrapper', 'usa-state-calculators'); ?></span>
                        <span class="lang-tag">HTML</span>
                    </div>
                    <textarea name="usc_calc_html" id="usc_calc_html" class="usc-meta-textarea"><?php echo esc_textarea($calc_html); ?></textarea>
                </div>
            </div>

            <div class="usc-meta-row">
                <label><?php _e('Custom Scoped CSS (Styles)', 'usa-state-calculators'); ?></label>
                <div class="usc-editor-container">
                    <div class="usc-editor-header">
                        <span><?php _e('Scoped styles for this calculator page only', 'usa-state-calculators'); ?></span>
                        <span class="lang-tag">CSS</span>
                    </div>
                    <textarea name="usc_calc_css" id="usc_calc_css" class="usc-meta-textarea"><?php echo esc_textarea($calc_css); ?></textarea>
                </div>
            </div>

            <div class="usc-meta-row">
                <label><?php _e('Custom Scoped JS (Calculation Engine)', 'usa-state-calculators'); ?></label>
                <div class="usc-editor-container">
                    <div class="usc-editor-header">
                        <span><?php _e('Routines, formulas, and visual rendering scripts', 'usa-state-calculators'); ?></span>
                        <span class="lang-tag">JavaScript</span>
                    </div>
                    <textarea name="usc_calc_js" id="usc_calc_js" class="usc-meta-textarea"><?php echo esc_textarea($calc_js); ?></textarea>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render SEO Metabox
     */
    public function render_seo_metabox($post) {
        $seo_title = get_post_meta($post->ID, '_usc_seo_title', true);
        $seo_desc = get_post_meta($post->ID, '_usc_seo_desc', true);
        $faqs = get_post_meta($post->ID, '_usc_faqs', true);

        if (empty($faqs) || !is_array($faqs)) {
            $faqs = [
                ['q' => '', 'a' => ''],
                ['q' => '', 'a' => '']
            ];
        }
        ?>
        <div class="usc-meta-group">
            <div class="usc-meta-group-title">
                <span class="dashicons dashicons-share"></span> Search Engine Optimization (SEO)
            </div>

            <div class="usc-meta-row">
                <label for="usc_seo_title"><?php _e('Custom Meta Title', 'usa-state-calculators'); ?></label>
                <input type="text" name="usc_seo_title" id="usc_seo_title" value="<?php echo esc_attr($seo_title); ?>" placeholder="E.g., California Paycheck Calculator | Take-Home Pay" />
            </div>

            <div class="usc-meta-row">
                <label for="usc_seo_desc"><?php _e('Custom Meta Description', 'usa-state-calculators'); ?></label>
                <textarea name="usc_seo_desc" id="usc_seo_desc" style="width:100%; height:80px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; box-sizing: border-box;" placeholder="Brief description to display in Google search results..."><?php echo esc_textarea($seo_desc); ?></textarea>
            </div>
        </div>

        <div class="usc-meta-group">
            <div class="usc-meta-group-title">
                <span class="dashicons dashicons-welcome-learn-more"></span> FAQs Schema Markup Builder
            </div>
            <p class="description" style="margin-bottom: 15px;"><?php _e('Create FAQ questions and answers. These will automatically render on the frontend accordion and inject schema markup to rank with FAQ rich snippets on search results.', 'usa-state-calculators'); ?></p>
            
            <div id="usc-faq-wrapper">
                <?php foreach ($faqs as $index => $faq) : ?>
                    <div class="usc-faq-row">
                        <div class="usc-faq-row-header">
                            <span class="usc-faq-row-title">Question #<?php echo $index + 1; ?></span>
                            <button type="button" class="usc-faq-delete-btn"><?php _e('Delete', 'usa-state-calculators'); ?></button>
                        </div>
                        <input type="text" name="usc_faqs[<?php echo $index; ?>][q]" value="<?php echo esc_attr($faq['q']); ?>" placeholder="Enter FAQ Question" />
                        <textarea name="usc_faqs[<?php echo $index; ?>][a]" placeholder="Enter FAQ Answer"><?php echo esc_textarea($faq['a']); ?></textarea>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="button button-large usc-btn usc-btn-white" id="usc-add-faq" style="border:1px solid var(--usc-border) !important; font-size:13px; font-weight:600;">
                <span class="dashicons dashicons-plus" style="margin-top: 4px;"></span> <?php _e('Add FAQ Item', 'usa-state-calculators'); ?>
            </button>
        </div>

        <script>
            jQuery(document).ready(function($) {
                var index = <?php echo count($faqs); ?>;

                function reorderFaqTitles() {
                    $('#usc-faq-wrapper .usc-faq-row').each(function(idx) {
                        $(this).find('.usc-faq-row-title').text('Question #' + (idx + 1));
                    });
                }

                $('#usc-add-faq').on('click', function(e) {
                    e.preventDefault();
                    var html = '<div class="usc-faq-row">' +
                        '<div class="usc-faq-row-header">' +
                        '<span class="usc-faq-row-title">Question #' + (index + 1) + '</span>' +
                        '<button type="button" class="usc-faq-delete-btn">Delete</button>' +
                        '</div>' +
                        '<input type="text" name="usc_faqs[' + index + '][q]" value="" placeholder="Enter FAQ Question" />' +
                        '<textarea name="usc_faqs[' + index + '][a]" placeholder="Enter FAQ Answer"></textarea>' +
                        '</div>';
                    $('#usc-faq-wrapper').append(html);
                    index++;
                    reorderFaqTitles();
                });

                $(document).on('click', '.usc-faq-delete-btn', function(e) {
                    e.preventDefault();
                    $(this).closest('.usc-faq-row').remove();
                    reorderFaqTitles();
                });
            });
        </script>
        <?php
    }

    /**
     * Save Metaboxes fields
     */
    public function save_calculators_meta($post_id) {
        if (!isset($_POST['usc_settings_nonce']) || !wp_verify_nonce($_POST['usc_settings_nonce'], 'usc_save_settings')) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save normal options
        if (isset($_POST['usc_calc_type'])) {
            update_post_meta($post_id, '_usc_calc_type', sanitize_text_field($_POST['usc_calc_type']));
        }
        if (isset($_POST['usc_state_slug'])) {
            update_post_meta($post_id, '_usc_state_slug', sanitize_text_field($_POST['usc_state_slug']));
        }
        $enable_lead = isset($_POST['usc_enable_lead_capture']) ? '1' : '0';
        update_post_meta($post_id, '_usc_enable_lead_capture', $enable_lead);
        
        // Save code files (respecting unfiltered_html capability)
        if (current_user_can('unfiltered_html')) {
            if (isset($_POST['usc_calc_html'])) {
                update_post_meta($post_id, '_usc_calc_html', wp_unslash($_POST['usc_calc_html']));
            }
            if (isset($_POST['usc_calc_css'])) {
                update_post_meta($post_id, '_usc_calc_css', wp_unslash($_POST['usc_calc_css']));
            }
            if (isset($_POST['usc_calc_js'])) {
                update_post_meta($post_id, '_usc_calc_js', wp_unslash($_POST['usc_calc_js']));
            }
            if (isset($_POST['usc_ads_code'])) {
                update_post_meta($post_id, '_usc_ads_code', wp_unslash($_POST['usc_ads_code']));
            }
        } else {
            if (isset($_POST['usc_calc_html'])) {
                update_post_meta($post_id, '_usc_calc_html', wp_kses_post(wp_unslash($_POST['usc_calc_html'])));
            }
            if (isset($_POST['usc_calc_css'])) {
                // Strip tags from CSS block to avoid layout/markup breakout
                update_post_meta($post_id, '_usc_calc_css', wp_strip_all_tags(wp_unslash($_POST['usc_calc_css'])));
            }
            // Note: Custom JS and Native Ads Code are skipped/ignored for users without unfiltered_html
        }

        // Save SEO fields
        if (isset($_POST['usc_seo_title'])) {
            update_post_meta($post_id, '_usc_seo_title', sanitize_text_field($_POST['usc_seo_title']));
        }
        if (isset($_POST['usc_seo_desc'])) {
            update_post_meta($post_id, '_usc_seo_desc', sanitize_textarea_field($_POST['usc_seo_desc']));
        }

        // Save FAQs
        if (isset($_POST['usc_faqs']) && is_array($_POST['usc_faqs'])) {
            $cleaned_faqs = [];
            foreach ($_POST['usc_faqs'] as $faq) {
                if (!empty($faq['q']) && !empty($faq['a'])) {
                    $cleaned_faqs[] = [
                        'q' => sanitize_text_field($faq['q']),
                        'a' => sanitize_textarea_field($faq['a']),
                    ];
                }
            }
            update_post_meta($post_id, '_usc_faqs', $cleaned_faqs);
        }
    }
}
