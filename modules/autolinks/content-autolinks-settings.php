<?php
/**
 * Content Deeplink Juggernaut Settings Module
 * 
 * @since 2.2
 */

if (class_exists('SEO_Module')) {

class WSW_ContentAutolinksSettings extends SEO_Module {
	
	static function get_parent_module() { return 'autolinks'; }
	static function get_child_order() { return 20; }
	static function is_independent_module() { return false; }
	
	static function get_module_title() { return __('Content Deeplink Juggernaut Settings', 'seo-wizard'); }
	function get_module_subtitle() { return __('Content Link Settings', 'seo-wizard'); }
	
	function get_default_settings() {
		
		$defaults = array(
			  'dampen_sitewide_lpa_value' => 50
			, 'enable_perlink_dampen_sitewide_lpa' => ($this->get_setting('enable_link_limits') !== null)
			, 'enable_self_links' => false
			, 'enable_current_url_links' => $this->get_setting('enable_self_links', false)
			, 'limit_lpp_value' => 5
			, 'limit_lpa_value' => 2
			, 'limit_lpu_value' => 1
			, 'linkfree_tags' => 'code,pre,kbd,h1,h2,h3,h4,h5,h6'
		);
		
		$defaults = array_merge($defaults, array_fill_keys(suarr::aprintf(false, 'autolink_posttype_%s', get_post_types(array('public' => true), 'names')), true));
		
		return $defaults;
	}
	
	function admin_page_contents() {
		$this->admin_form_table_start();
		
		$this->checkboxes(
			suarr::aprintf('autolink_posttype_%s', false, suarr::simplify(get_post_types(array('public' => true), 'objects'), 'name', array('labels', 'name')))
		, __('Add Autolinks to...', 'seo-wizard'));
		
		$this->checkboxes(array(
			  'enable_self_links' => __('Allow posts to link to themselves', 'seo-wizard')
			, 'enable_current_url_links' => __('Allow posts to link to the URL by which the visitor is accessing the post', 'seo-wizard')
		), __('Self-Linking', 'seo-wizard'));
		
		$this->checkboxes(array(
			  'limit_lpp' => __('Don&#8217;t add any more than %d autolinks per post/page/etc.', 'seo-wizard')
			, 'limit_lpa' => __('Don&#8217;t link the same anchor text any more than %d times per post/page/etc.', 'seo-wizard')
			, 'limit_lpu' => __('Don&#8217;t link to the same destination any more than %d times per post/page/etc.', 'seo-wizard')
		), __('Quantity Restrictions', 'seo-wizard'));
		
		$legacy_sitewide_lpa_in_use = $this->plugin->get_module_var('content-autolinks', 'legacy_sitewide_lpa_in_use', false);
		$this->checkboxes(array(
			  'dampen_sitewide_lpa' => __('Globally decrease autolinking frequency by %d%', 'seo-wizard')
			, 'enable_perlink_dampen_sitewide_lpa' => array(
				  'description' => __('Add a &#8220;Dampener&#8221; column to the Content Links editor to let me customize frequency dampening on a per-link basis', 'seo-wizard')
				, 'disabled' => $legacy_sitewide_lpa_in_use
				, 'checked' => $legacy_sitewide_lpa_in_use ? true : null
			)
		), __('Additional Dampening Effect', 'seo-wizard'));
		
		$this->textbox('linkfree_tags', __('Tag Restrictions', 'seo-wizard'), $this->get_default_setting('linkfree_tags'), false, array('help_text' => __('Don&#8217;t add autolinks to text within these HTML tags <em>(separate with commas)</em>:', 'seo-wizard')));
		
		$siloing_checkboxes = array();
		$post_types = get_post_types(array('public' => true), 'objects');
		foreach ($post_types as $post_type) {
			$taxonomies = suwp::get_object_taxonomies($post_type->name);
			if (count($taxonomies)) {
				$siloing_checkboxes['dest_limit_' . $post_type->name] = sprintf(
					  __('%s can only link to internal destinations that share at least one...', 'seo-wizard')
					, $post_type->labels->name
				);
				
				foreach ($taxonomies as $taxonomy) {
					$siloing_checkboxes['dest_limit_' . $post_type->name . '_within_' . $taxonomy->name] = array(
						  'description' => $taxonomy->labels->singular_name
						, 'indent' => true
					);
				}
			}
		}
		
		$this->checkboxes($siloing_checkboxes, __('Siloing', 'seo-wizard'));
		
		$this->textbox('autolink_class', __('CSS Class for Autolinks', 'seo-wizard'));
		
		$this->admin_form_table_end();
	}
}

}
?>