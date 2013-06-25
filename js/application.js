jQuery(function() {
	jQuery("#ignore_clever_seo_keywords_warning").click(function() {
		jQuery.post("admin.php?page=clever-seo-keywords/clever-seo-keywords.php&action=ignore_clever_seo_keywords_warning", {},
			function(data) {
				jQuery("#update_clever_seo_keywords_msg").remove();
			}
		);
		return false;
	});
});