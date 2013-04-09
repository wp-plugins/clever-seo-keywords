<?php
/*
Plugin Name: Clever SEO Keywords
Plugin URI: http://wordpress.org/extend/plugins/clever-seo-keywords/
Description: A wordpress plugin that allows you to auto create keywords based on the headers within your pages.

Installation:

1) Install WordPress 3.5.2 or higher

2) Download the following file:

http://downloads.wordpress.org/plugin/clever-seo-keywords.1.0.zip

3) Login to WordPress admin, click on Plugins / Add New / Upload, then upload the zip file you just downloaded.

4) Activate the plugin.

Version: 1.0
Author: TheOnlineHero - Tom Skroza
License: GPL2
*/


if (!class_exists("simple_html_dom_node")) {
 require_once(dirname(__FILE__).'/simple_html_dom.php');
}

add_action('admin_menu', 'register_clever_seo_keywords_page');
function register_clever_seo_keywords_page() {
  add_menu_page('Clever Keywords', 'Clever Keywords', 'manage_options', 'clever-seo-keywords/clever-seo-keywords.php', 'clever_seo_keywords_initial_page');
}

//call register settings function
add_action( 'admin_init', 'register_clever_seo_redirect_settings' );
function register_clever_seo_redirect_settings() {

  @check_clever_seo_keywords_dependencies_are_active(
    "Clever SEO Keywords", 
    array(
      "Tom M8te" => array("plugin"=>"tom-m8te/tom-m8te.php", "url" => "http://downloads.wordpress.org/plugin/tom-m8te.zip", "version" => "1.4.2"))
  );

}

add_action( 'save_post', 'clever_seo_keywords_save_post' );
function clever_seo_keywords_save_post( $postid ) {

	$my_revision = tom_get_row("posts", "*", "
		post_type='revision' AND 
		ID=".$postid);

	if ($my_revision->post_parent == "") {
	  $my_post = tom_get_row("posts", "*", "
	  	post_type IN ('page', 'post') AND 
	  	ID=".$postid);
	} else {
	  $my_post = tom_get_row("posts", "*", "
	  	post_type IN ('page', 'post') AND 
	  	ID=".$my_revision->post_parent);
  }

  create_or_update_the_clever_seo_keyword($my_post);

}

function create_or_update_the_clever_seo_keyword($my_post) {
  if ($my_post != null && $my_post->ID != 0) {
	  // Check if this is a new record.\
	  $postmeta_row = tom_get_row("postmeta", "*", "
	  	meta_key = '_clever_seo_keywords_words' AND 
	  	post_id =".$my_post->ID);
	 	if ($postmeta_row != null) {
	 		// This is an existing record.
	 		// Update statement - Update keywords.
	 		update_the_clever_seo_keywords($my_post);
	 	} else {

	 		// This is a new record.
	 		// Insert Statement - Insert keywords.
	 		tom_insert_record("postmeta", array("post_id" => $my_post->ID, "meta_key" => "_clever_seo_keywords_words", "meta_value" => ""));
	 		update_the_clever_seo_keywords($my_post);
	 	}
  }
}

function clever_seo_keywords_initial_page() {
	if ($_POST["action"] == "Update Keywords Across All Pages") {
		$all_posts = tom_get_results("posts", "*", "post_type IN ('page', 'post')");
		foreach ($all_posts as $my_post) {
			create_or_update_the_clever_seo_keyword($my_post);
		}
		$_GET["message"] = "Your keywords have been updated across the site.";
	}
	?>

	<div class="wrap a-form">
  <h2>Clever SEO Keywords</h2>
  <p>Make sure that you have the following line within the head tag of your header.php template file:</p>
  <p>
  	<textarea cols="100"><?php echo("<meta name=\"keywords\" content=\"<?php if (function_exists('print_clever_seo_keywords')) {print_clever_seo_keywords();} ?>\" />");?></textarea>
  </p>
  <?php
	  if (isset($_GET["message"]) && $_GET["message"] != "") {
	    echo("<div class='updated below-h2'><p>".$_GET["message"]."</p></div>");
	  }
  ?>
  <div class="postbox " style="display: block; ">
  <div class="inside">
    <form action="" method="post">
      <input type="submit" name="action" value="Update Keywords Across All Pages" />
    </form>
  </div>
  </div>
  </div>

  <?php
}

function update_the_clever_seo_keywords($my_post) {
	if ($my_post != null) {

		$ignore_word_regex = "(A|About|As|Of|Our|The|This|Is|Are|With|And|All|&|>|<)";

		if ($html = @file_get_html(get_permalink($my_post->ID))) {
			$keywords_list = array();
			foreach($html->find("h1,h2,h3,h4,h5,h6,h7,h8,h9,h1 a,h2 a,h3 a,h4 a,h5 a,h6 a,h7 a,h8 a,h9 a") as $e) {
				if (strlen($e->outertext) >= 2) {
			    $keywords_list = array_merge($keywords_list, preg_split("/(,|-|:| )/", trim(preg_replace("/".$ignore_word_regex."/", "", strip_tags($e->outertext)))), (array)preg_split("/(,|-|:)/", trim(str_replace(">", "", strip_tags($e->outertext)))));
				}
		  }

			$keywords_list = array_merge($keywords_list, preg_split("/(,|-|:|\|| )/", trim(preg_replace("/".$ignore_word_regex."/", "", strip_tags(get_option("blogname"))))), (array)preg_split("/(,|-|:|\|)/", trim(str_replace(">", "", strip_tags(get_option("blogname"))))));

			$keywords_list = array_merge($keywords_list, preg_split("/(,|-|:|\|| )/", trim(preg_replace("/".$ignore_word_regex."/", "", strip_tags(get_option("blogdescription"))))), (array)preg_split("/(,|-|:|\|)/", trim(str_replace(">", "", strip_tags(get_option("blogdescription"))))));

			$index = 0;
			foreach ($keywords_list as $value) {
				$keywords_list[$index] = scrub_clever_seo_keyword(tom_titlize_str($keywords_list[$index]));
				$index++;
			}
			$keywords_list = array_unique(array_filter( $keywords_list, 'strlen' ));

			tom_update_record(
				"postmeta", 
				array("meta_value" => implode(",", $keywords_list)), 
				array(
					"meta_key" => "_clever_seo_keywords_words",
					"post_id" => $my_post->ID
				)
			);
		}
	}
}

function print_clever_seo_keywords() {
	$postmeta_row = tom_get_row("postmeta", "*", "
	  	meta_key = '_clever_seo_keywords_words' AND 
	  	post_id =".get_The_ID());
	echo($postmeta_row->meta_value);
}

function scrub_clever_seo_keyword($keyword) {
	$keyword = preg_replace("/^( )*|( )*$/", "", $keyword);
	$keyword = preg_replace("/(&#039;|#039;)/", "'", $keyword);
	return $keyword;
}

function check_clever_seo_keywords_dependencies_are_active($plugin_name, $dependencies) {
  $msg_content = "<div class='updated'><p>Sorry for the confusion but you must install and activate ";
  $plugins_array = array();
  $upgrades_array = array();
  define('PLUGINPATH', ABSPATH.'wp-content/plugins');
  foreach ($dependencies as $key => $value) {
    $plugin = get_plugin_data(PLUGINPATH."/".$value["plugin"],true,true);
    $url = $value["url"];
    if (!is_plugin_active($value["plugin"])) {
      array_push($plugins_array, $key);
    } else {
      if (isset($value["version"]) && str_replace(".", "", $plugin["Version"]) < str_replace(".", "", $value["version"])) {
        array_push($upgrades_array, $key);
      }
    }
  }
  $msg_content .= implode(", ", $plugins_array) . " before you can use $plugin_name. Please go to Plugins/Add New and search/install the following plugin(s): ";
  $download_plugins_array = array();
  foreach ($dependencies as $key => $value) {
    if (!is_plugin_active($value["plugin"])) {
      $url = $value["url"];
      array_push($download_plugins_array, $key);
    }
  }
  $msg_content .= implode(", ", $download_plugins_array)."</p></div>";
  if (count($plugins_array) > 0) {
    deactivate_plugins( __FILE__, true);
    echo($msg_content);
  } 

  if (count($upgrades_array) > 0) {
    deactivate_plugins( __FILE__,true);
    echo "<div class='updated'><p>$plugin_name requires the following plugins to be updated: ".implode(", ", $upgrades_array).".</p></div>";
  }
}

?>