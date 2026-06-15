<?php
/**
 * Custom Metaboxes for USA State-Wise Tax Calculators
 */

if (!defined('ABSPATH')) exit;

class UST_Metaboxes {

    public function __construct() {
        add_action('add_meta_boxes', [$this, 'add_calculator_metaboxes']);
        add_action('save_post', [$this, 'save_calculator_metadata']);
    }

    /**
     * Add metaboxes to CPT edit screen
     */
    public function add_calculator_metaboxes() {
        add_meta_box(
            'ust_settings_metabox',
            __('Calculator Configurations & Code Editors', 'usa-state-tax-calculators'),
            [$this, 'render_settings_metabox'],
            'ust_calculator',
            'normal',
            'high'
        );
        add_meta_box(
            'ust_seo_metabox',
            __('SEO & Schema Settings', 'usa-state-tax-calculators'),
            [$this, 'render_seo_metabox'],
            'ust_calculator',
            'normal',
            'default'
        );
    }

    /**
     * Render Settings Metabox
     */
    public function render_settings_metabox($post) {
        wp_nonce_field('ust_save_settings', 'ust_settings_nonce');

        $calc_type = get_post_meta($post->ID, '_ust_calc_type', true);
        $state_slug = get_post_meta($post->ID, '_ust_state_slug', true);
        $calc_html = get_post_meta($post->ID, '_ust_calc_html', true);
        $calc_css = get_post_meta($post->ID, '_ust_calc_css', true);
        $calc_js = get_post_meta($post->ID, '_ust_calc_js', true);

        $states = ust_get_states_data();
        ?>
        <div class="usc-meta-group">
            <div class="usc-meta-group-title">
                <span class="dashicons dashicons-admin-generic"></span> Basic Calculator Settings
            </div>
            
            <div class="usc-meta-row">
                <label for="ust_calc_type"><?php _e('Calculator Type', 'usa-state-tax-calculators'); ?></label>
                <select name="ust_calc_type" id="ust_calc_type">
                    <option value="income-tax" <?php selected($calc_type, 'income-tax'); ?>>Income Tax Calculator</option>
                    <option value="property-tax" <?php selected($calc_type, 'property-tax'); ?>>Property Tax Calculator</option>
                    <option value="sales-tax" <?php selected($calc_type, 'sales-tax'); ?>>Sales Tax Calculator</option>
                    <option value="other" <?php selected($calc_type, 'other'); ?>>Other General Calculator</option>
                </select>
            </div>

            <div class="usc-meta-row">
                <label for="ust_state_slug"><?php _e('US State Context', 'usa-state-tax-calculators'); ?></label>
                <select name="ust_state_slug" id="ust_state_slug">
                    <option value=""><?php _e('-- Select State --', 'usa-state-tax-calculators'); ?></option>
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
                <label for="ust_ads_code"><?php _e('Native Ads Code (AdSense / Custom Ads)', 'usa-state-tax-calculators'); ?></label>
                <textarea name="ust_ads_code" id="ust_ads_code" style="width:100%; height:80px; font-family: monospace; padding: 10px; border-radius: 6px; border: 1px solid #cbd5e1;" placeholder="<?php _e('Paste AdSense or custom ad script code here...', 'usa-state-tax-calculators'); ?>"><?php echo esc_textarea(get_post_meta($post->ID, '_ust_ads_code', true)); ?></textarea>
            </div>

            <div class="usc-meta-row">
                <label for="ust_enable_lead_capture" style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" name="ust_enable_lead_capture" id="ust_enable_lead_capture" value="1" <?php checked(get_post_meta($post->ID, '_ust_enable_lead_capture', true), '1'); ?> style="margin: 0;" />
                    <strong><?php _e('Enable Lead Capture Form', 'usa-state-tax-calculators'); ?></strong>
                </label>
                <div class="usc-meta-info-alert" style="margin-top: 8px;">
                    💡 Enforce visitors to submit their name and email address to view detailed calculations. Submissions are saved securely under <strong>Calculators Hub > Captured Leads</strong>.
                </div>
            </div>
        </div>

        <div class="usc-meta-group">
            <div class="usc-meta-group-title">
                <span class="dashicons dashicons-editor-code"></span> HTML, CSS & JavaScript Editors
            </div>
            
            <div class="usc-meta-row">
                <label for="ust_calc_html"><?php _e('Calculator HTML Template', 'usa-state-tax-calculators'); ?></label>
                <textarea name="ust_calc_html" id="ust_calc_html" class="code-editor-box" style="height: 180px;"><?php echo esc_textarea($calc_html); ?></textarea>
            </div>

            <div class="usc-meta-row">
                <label for="ust_calc_css"><?php _e('Calculator Custom CSS Stylesheet', 'usa-state-tax-calculators'); ?></label>
                <textarea name="ust_calc_css" id="ust_calc_css" class="code-editor-box" style="height: 140px;"><?php echo esc_textarea($calc_css); ?></textarea>
            </div>

            <div class="usc-meta-row">
                <label for="ust_calc_js"><?php _e('Calculator JavaScript Logic', 'usa-state-tax-calculators'); ?></label>
                <textarea name="ust_calc_js" id="ust_calc_js" class="code-editor-box" style="height: 200px;"><?php echo esc_textarea($calc_js); ?></textarea>
            </div>
        </div>

        <div class="usc-meta-group">
            <div class="usc-meta-group-title">
                <span class="dashicons dashicons-editor-help"></span> Frequently Asked Questions (FAQ Builder)
            </div>
            
            <div class="usc-meta-row" id="ust-faq-builder-container">
                <div class="hint" style="margin-bottom:12px;">Manage state-specific FAQ items. Accompanying structured JSON Schema is injected automatically.</div>
                
                <div id="ust-faq-items-list">
                    <?php
                    $faqs = get_post_meta($post->ID, '_ust_faqs', true);
                    if (empty($faqs) || !is_array($faqs)) {
                        $faqs = [
                            ['q' => '', 'a' => '']
                        ];
                    }
                    foreach ($faqs as $index => $faq) :
                        ?>
                        <div class="ust-faq-item-row" style="display:flex; gap:10px; margin-bottom:10px; align-items:flex-start; border: 1px solid #e2e8f0; padding:12px; border-radius:6px; background:#f8fafc;">
                            <span class="dashicons dashicons-sort" style="cursor:move; margin-top:8px; color:#94a3b8;"></span>
                            <div style="flex:1;">
                                <input type="text" name="ust_faqs[<?php echo $index; ?>][q]" value="<?php echo esc_attr($faq['q']); ?>" style="width:100%; margin-bottom:6px; font-weight:600;" placeholder="Question..." />
                                <textarea name="ust_faqs[<?php echo $index; ?>][a]" style="width:100%; height:55px;" placeholder="Answer..."><?php echo esc_textarea($faq['a']); ?></textarea>
                            </div>
                            <button type="button" class="button button-link-delete" onclick="this.parentElement.remove()" style="margin-top:5px;"><span class="dashicons dashicons-trash"></span></button>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" class="button button-primary" onclick="addUstFaqRow()" style="margin-top:10px;"><span class="dashicons dashicons-plus"></span> Add FAQ Item</button>
            </div>
        </div>

        <script>
            function addUstFaqRow() {
                var container = document.getElementById("ust-faq-items-list");
                var index = container.getElementsByClassName("ust-faq-item-row").length;
                var html = `
                    <div class="ust-faq-item-row" style="display:flex; gap:10px; margin-bottom:10px; align-items:flex-start; border: 1px solid #e2e8f0; padding:12px; border-radius:6px; background:#f8fafc;">
                        <span class="dashicons dashicons-sort" style="cursor:move; margin-top:8px; color:#94a3b8;"></span>
                        <div style="flex:1;">
                            <input type="text" name="ust_faqs[${index}][q]" value="" style="width:100%; margin-bottom:6px; font-weight:600;" placeholder="Question..." />
                            <textarea name="ust_faqs[${index}][a]" style="width:100%; height:55px;" placeholder="Answer..."></textarea>
                        </div>
                        <button type="button" class="button button-link-delete" onclick="this.parentElement.remove()" style="margin-top:5px;"><span class="dashicons dashicons-trash"></span></button>
                    </div>
                `;
                var tempDiv = document.createElement("div");
                tempDiv.innerHTML = html;
                container.appendChild(tempDiv.firstElementChild);
            }
        </script>
        <?php
    }

    /**
     * Render SEO Metabox
     */
    public function render_seo_metabox($post) {
        $seo_title = get_post_meta($post->ID, '_ust_seo_title', true);
        $seo_desc = get_post_meta($post->ID, '_ust_seo_desc', true);
        ?>
        <div class="usc-meta-group">
            <div class="usc-meta-row">
                <label for="ust_seo_title"><?php _e('Custom SEO Meta Title', 'usa-state-tax-calculators'); ?></label>
                <input type="text" class="regular-text" style="width:100%;" name="ust_seo_title" id="ust_seo_title" value="<?php echo esc_attr($seo_title); ?>" placeholder="<?php _e('Leave blank to use default template title...', 'usa-state-tax-calculators'); ?>" />
                <div class="hint">Optimal length: 50-60 characters.</div>
            </div>
            
            <div class="usc-meta-row">
                <label for="ust_seo_desc"><?php _e('Custom Meta Description', 'usa-state-tax-calculators'); ?></label>
                <textarea name="ust_seo_desc" id="ust_seo_desc" style="width:100%; height:70px;" placeholder="<?php _e('Leave blank to use default template description...', 'usa-state-tax-calculators'); ?>"><?php echo esc_textarea($seo_desc); ?></textarea>
                <div class="hint">Optimal length: 150-160 characters. Recommended to summarize key state tax attributes.</div>
            </div>
        </div>
        <?php
    }

    /**
     * Saves metabox metadata values on post edit save
     */
    public function save_calculator_metadata($post_id) {
        // Security checks
        if (!isset($_POST['ust_settings_nonce']) || !wp_verify_nonce($_POST['ust_settings_nonce'], 'ust_save_settings')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;

        // Save fields
        if (isset($_POST['ust_calc_type'])) {
            update_post_meta($post_id, '_ust_calc_type', sanitize_key($_POST['ust_calc_type']));
        }
        if (isset($_POST['ust_state_slug'])) {
            update_post_meta($post_id, '_ust_state_slug', sanitize_key($_POST['ust_state_slug']));
        }
        if (isset($_POST['ust_calc_html'])) {
            update_post_meta($post_id, '_ust_calc_html', wp_unslash($_POST['ust_calc_html']));
        }
        if (isset($_POST['ust_calc_css'])) {
            update_post_meta($post_id, '_ust_calc_css', wp_unslash($_POST['ust_calc_css']));
        }
        if (isset($_POST['ust_calc_js'])) {
            update_post_meta($post_id, '_ust_calc_js', wp_unslash($_POST['ust_calc_js']));
        }
        if (isset($_POST['ust_seo_title'])) {
            update_post_meta($post_id, '_ust_seo_title', sanitize_text_field(wp_unslash($_POST['ust_seo_title'])));
        }
        if (isset($_POST['ust_seo_desc'])) {
            update_post_meta($post_id, '_ust_seo_desc', sanitize_textarea_field(wp_unslash($_POST['ust_seo_desc'])));
        }
        if (isset($_POST['ust_ads_code'])) {
            update_post_meta($post_id, '_ust_ads_code', wp_unslash($_POST['ust_ads_code']));
        }

        $lead_val = isset($_POST['ust_enable_lead_capture']) ? '1' : '0';
        update_post_meta($post_id, '_ust_enable_lead_capture', $lead_val);

        // Sanitize FAQs
        $faqs_saved = [];
        if (isset($_POST['ust_faqs']) && is_array($_POST['ust_faqs'])) {
            foreach ($_POST['ust_faqs'] as $faq) {
                $q = isset($faq['q']) ? sanitize_text_field(wp_unslash($faq['q'])) : '';
                $a = isset($faq['a']) ? sanitize_textarea_field(wp_unslash($faq['a'])) : '';
                if (!empty($q) && !empty($a)) {
                    $faqs_saved[] = ['q' => $q, 'a' => $a];
                }
            }
        }
        update_post_meta($post_id, '_ust_faqs', $faqs_saved);
    }
}
