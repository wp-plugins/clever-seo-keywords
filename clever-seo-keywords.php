<?php
/*
Plugin Name: Clever SEO Keywords
Plugin URI: http://wordpress.org/extend/plugins/clever-seo-keywords/
Description: A wordpress plugin that allows you to auto create meta keywords and description based on the headers within your pages.

Installation:

1) Install WordPress 3.6 or higher

2) Download the latest from:

http://wordpress.org/extend/plugins/tom-m8te 

http://wordpress.org/extend/plugins/clever-seo-keywords

3) Login to WordPress admin, click on Plugins / Add New / Upload, then upload the zip file you just downloaded.

4) Activate the plugin.

Version: 4.1
License: GPL2
*/


if (!class_exists("simple_html_dom_node")) {
 require_once(dirname(__FILE__).'/simple_html_dom.php');
}

function are_clever_seo_keywords_dependencies_installed() {
  return is_plugin_active("tom-m8te/tom-m8te.php");
}

add_action( 'admin_notices', 'clever_seo_keywords_notice_notice' );
function clever_seo_keywords_notice_notice(){
  $activate_nonce = wp_create_nonce( "activate-clever-seo-keywords-dependencies" );
  $tom_active = is_plugin_active("tom-m8te/tom-m8te.php");
  if (!($tom_active)) { ?>
    <div class='updated below-h2'><p>Before you can use Clever SEO Keywords, please install/activate the following plugin:</p>
    <ul>
      <?php if (!$tom_active) { ?>
        <li>
          <a target="_blank" href="http://wordpress.org/extend/plugins/tom-m8te/">Tom M8te</a> 
           &#8211; 
          <?php if (file_exists(ABSPATH."/wp-content/plugins/tom-m8te/tom-m8te.php")) { ?>
            <a href="<?php echo(get_option("siteurl")); ?>/wp-admin/?clever_seo_keywords_install_dependency=tom-m8te&_wpnonce=<?php echo($activate_nonce); ?>">Activate</a>
          <?php } else { ?>
            <a href="<?php echo(get_option("siteurl")); ?>/wp-admin/plugin-install.php?tab=plugin-information&plugin=tom-m8te&_wpnonce=<?php echo($activate_nonce); ?>&TB_iframe=true&width=640&height=876">Install</a> 
          <?php } ?>
        </li>
      <?php } ?>
    </ul>
    </div>
    <?php
  }

}

add_action( 'admin_init', 'register_clever_seo_keywords_install_dependency_settings' );
function register_clever_seo_keywords_install_dependency_settings() {
  if (isset($_GET["clever_seo_keywords_install_dependency"])) {
    if (wp_verify_nonce($_REQUEST['_wpnonce'], "activate-clever-seo-keywords-dependencies")) {
      switch ($_GET["clever_seo_keywords_install_dependency"]) {
        case 'tom-m8te':  
          activate_plugin('tom-m8te/tom-m8te.php', 'plugins.php?error=false&plugin=tom-m8te.php');
          wp_redirect(get_option("siteurl")."/wp-admin/");
          exit();
          break;   
        default:
          throw new Exception("Sorry unable to install plugin.");
          break;
      }
    } else {
      die("Security Check Failed.");
    }
  }
}

add_action("init", "clever_seo_keywords_start_parsing_keywords_site");
function clever_seo_keywords_start_parsing_keywords_site() {
  ob_start();
}
add_action("init", "clever_seo_keywords_start_parsing_description_site");
function clever_seo_keywords_start_parsing_description_site() {
  ob_start();
}

add_action("wp_footer", "clever_seo_keywords_end_parsing_keywords_site");
function clever_seo_keywords_end_parsing_keywords_site() {
  $content = ob_get_contents();
  ob_end_clean();
  $postmeta_row = tom_get_row("postmeta", "*", "
        meta_key = '_clever_seo_keywords_words' AND 
        post_id =".get_The_ID());
  $html = str_get_html($content);
  if ($postmeta_row->meta_value != "") {
    if ($html->find("meta[name=keywords]", 0)) {
      $temp = $html->find("meta[name=keywords]", 0)->getAttribute('content');
      if ($temp != "" && !preg_match("/,|, $/", $temp)) {
        $temp .= ", ";
      }
      $html->find("meta[name=keywords]", 0)->setAttribute('content', $temp.$postmeta_row->meta_value);
    } else {
      $e = $html->find("head", 0);
      $e->outertext = $e->makeup().$e->innertext."<meta name='keywords' content='".$postmeta_row->meta_value."' />";
    }
  }
  echo $html;
}

add_action("wp_footer", "clever_seo_keywords_end_parsing_description_site");
function clever_seo_keywords_end_parsing_description_site() {
  $content = ob_get_contents();
  ob_end_clean();
  $postmeta_row = tom_get_row("postmeta", "*", "
        meta_key = '_clever_seo_keywords_words' AND 
        post_id =".get_The_ID());
  $html = str_get_html($content);
  if ($postmeta_row->meta_value != "") {
    if ($html->find("meta[name=description]", 0)) {
      $temp = $html->find("meta[name=description]", 0)->getAttribute('content');
      if (!preg_match("/\.|\. $/", $temp)) {
        $temp .= ". ";
      }
      $html->find("meta[name=description]", 0)->setAttribute('content', $temp." Keywords: ".$postmeta_row->meta_value);
    } else {
      $e = $html->find("head", 0);
      $e->outertext = $e->makeup().$e->innertext."<meta name='description' content='Keywords: ".$postmeta_row->meta_value.".' />";
    }
  }
  echo $html;
}


add_action('admin_enqueue_scripts', 'clever_seo_keywords_admin_theme_style');
function clever_seo_keywords_admin_theme_style() {
  if (are_clever_seo_keywords_dependencies_installed()) {
    wp_enqueue_style('clever_seo_keywords', plugins_url('/css/style.css', __FILE__));
    wp_enqueue_script('clever_seo_keywords', plugins_url('/js/application.js', __FILE__));
  }
}

function update_the_clever_seo_keywords($my_post) {
  if (are_clever_seo_keywords_dependencies_installed()) {
    if ($my_post != null) {

      $ignore_word_regex = "(A|About|As|Of|Our|The|This|Is|Are|With|And|All|&|>|<)";

      if ($html = @file_get_html(get_permalink($my_post->ID))) {
        $keywords_list = array();
        foreach($html->find("h1,h2,h3,h4,h5,h6,h7,h8,h9,h1 a,h2 a,h3 a,h4 a,h5 a,h6 a,h7 a,h8 a,h9 a") as $e) {
          if (strlen($e->outertext) >= 2) {
            $keywords_list = array_merge($keywords_list, preg_split("/(,|-|:| )/", trim(preg_replace("/".$ignore_word_regex." /", "", strip_tags($e->outertext)))), (array)preg_split("/(,|-|:)/", trim(str_replace(">", "", strip_tags($e->outertext)))));
          }
        }

        $keywords_list = array_merge($keywords_list, preg_split("/(,|-|:|\|| )/", trim(preg_replace("/".$ignore_word_regex." /", "", strip_tags(get_option("blogname"))))), (array)preg_split("/(,|-|:|\|)/", trim(str_replace(">", "", strip_tags(get_option("blogname"))))));

        $keywords_list = array_merge($keywords_list, preg_split("/(,|-|:|\|| )/", trim(preg_replace("/".$ignore_word_regex." /", "", strip_tags(get_option("blogdescription"))))), (array)preg_split("/(,|-|:|\|)/", trim(str_replace(">", "", strip_tags(get_option("blogdescription"))))));

        $index = 0;
        foreach ($keywords_list as $value) {
          if (strlen($keywords_list[$index]) > 2) {
            $keywords_list[$index] = scrub_clever_seo_keyword(tom_titlize_str($keywords_list[$index]));         
          } else {
            $keywords_list[$index] = null;
          } 
          $index++;
        }
        $keywords_list = array_unique(array_filter( $keywords_list, 'strlen' ));
        return implode(",", $keywords_list);
      }
    }
  }
}

function print_clever_seo_keywords() {
  if (are_clever_seo_keywords_dependencies_installed()) {
    $postmeta_row = tom_get_row("postmeta", "*", "
        meta_key = '_clever_seo_keywords_words' AND 
        post_id =".get_The_ID());
    echo($postmeta_row->meta_value);
  }
}

function scrub_clever_seo_keyword($keyword) {
  if (are_clever_seo_keywords_dependencies_installed()) {
    $keyword = preg_replace("/^( )*|( )*$|&nbsp;|Nbsp;|Amp;|^&amp;$|^&#038;$/", "", $keyword);
    $keyword = preg_replace("/(&#039;|#039;|#8217;|&#8217;)/", "'", $keyword);
    return $keyword;
  }
}

/* Define the custom box */

add_action( 'add_meta_boxes', 'clever_keywords_add_custom_box' );

// backwards compatible (before WP 3.0)
// add_action( 'admin_init', 'clever_keywords_add_custom_box', 1 );

/* Do something with the data entered */
add_action( 'save_post', 'clever_keywords_save_postdata' );

/* Adds a box to the main column on the Post and Page edit screens */
function clever_keywords_add_custom_box() {
    $screens = array( 'post', 'page' );
    foreach ($screens as $screen) {
        add_meta_box(
            'clever_keywords_sectionid',
            __( 'Clever SEO Keywords', 'clever_keywords_textdomain' ),
            'clever_keywords_inner_custom_box',
            $screen
        );
    }
}

/* Prints the box content */
function clever_keywords_inner_custom_box( $post ) {

  // Use nonce for verification
  wp_nonce_field( plugin_basename( __FILE__ ), 'clever_keywords_noncename' );

  $possible_keywords = explode(",", update_the_clever_seo_keywords($post));

  // The actual fields for data entry
  // Use get_post_meta to retrieve an existing value from the database and use the value for the form
  $value = get_post_meta( $post->ID, '_clever_seo_keywords_words', true );

  $current_keywords = explode(",", $value);

  ?>
  <div id="clever_keywords_controls">
    <p>Select the keywords you want by clicking on them and then save the page/post. The green keywords are currently being used, while the grey ones are not.</p>
    <ul id="possible_clever_keywords">
      <?php
      foreach ($possible_keywords as $keyword) {
        ?>
        <li><a <?php if (in_array($keyword, $current_keywords)) { echo("class='active'"); } ?> href="#"><?php echo($keyword); ?></a></li>
        <?php
      }
      ?>
    </ul>
    <?php
    echo '<input type="hidden" id="clever_keywords_new_field" name="clever_keywords_new_field" value="'.esc_attr($value).'" size="25" />';
  ?>
  </div>
  <?php
}

/* When the post is saved, saves our custom data */
function clever_keywords_save_postdata( $post_id ) {

  // First we need to check if the current user is authorised to do this action. 
  if ( 'page' == $_REQUEST['post_type'] ) {
    if ( ! current_user_can( 'edit_page', $post_id ) )
        return;
  } else {
    if ( ! current_user_can( 'edit_post', $post_id ) )
        return;
  }

  // Secondly we need to check if the user intended to change this value.
  if ( ! isset( $_POST['clever_keywords_noncename'] ) || ! wp_verify_nonce( $_POST['clever_keywords_noncename'], plugin_basename( __FILE__ ) ) )
      return;

  // Thirdly we can save the value to the database

  //if saving in a custom table, get post_ID
  $post_ID = $_POST['post_ID'];
  //sanitize user input
  $mydata = sanitize_text_field( $_POST['clever_keywords_new_field'] );

  // Do something with $mydata 
  // either using 
  add_post_meta($post_ID, '_clever_seo_keywords_words', $mydata, true) or
    update_post_meta($post_ID, '_clever_seo_keywords_words', $mydata);
  // or a custom table (see Further Reading section below)
}

?>