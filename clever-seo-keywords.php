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

Version: 5.0
License: GPL2

*/

function are_clever_seo_keywords_dependencies_installed() {
  return is_plugin_active("tom-m8te/tom-m8te.php");
}

add_action( 'admin_notices', 'clever_seo_keywords_notice_notice' );
function clever_seo_keywords_notice_notice(){
  $activate_nonce = wp_create_nonce( "activate-clever-seo-keywords-dependencies" );
  $tom_active = is_plugin_active("tom-m8te/tom-m8te.php");

  if (!class_exists("simple_html_dom_node")) {
    ?>
    <div class='updated below-h2'><p>Please install/upgrade your Tom M8te plugin. Your Clever SEO Keywords plugin now uses Tom M8te's new library for parsing the HTML code for keywords. Just go to <a href="<?php echo(get_option("siteurl")); ?>/wp-admin/plugins.php">plugins area and upgrade</a>.</p>
    </div>
    <?php
  }

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

function clever_seo_keywords_get_ID_by_slug($page_slug) {
  $page_slug = preg_replace("/\?(.+)*$/", "", $page_slug);
  if ($page_slug == "/") {
    return get_option("page_on_front");
  }
  $page = get_page_by_path($page_slug);
  if ($page) {
      return $page->ID;
  } else {
      return null;
  }
}

add_action("init", "clever_seo_keywords_start_parsing_keywords_site");
function clever_seo_keywords_start_parsing_keywords_site() {
  if (function_exists("tom_get_current_url")) {
    if (!isset($_SESSION)) {
      session_start();
    }
    $slug_to_get = str_replace(get_option("siteurl"), "", tom_get_current_url());
    $cpostid = clever_seo_keywords_get_ID_by_slug($slug_to_get);
    if ($cpostid != null) {
      ob_start();
    }
  }
}

add_action("wp_footer", "clever_seo_keywords_end_parsing_keywords_site");
function clever_seo_keywords_end_parsing_keywords_site() {
  if (function_exists("tom_get_current_url")) {
    $slug_to_get = str_replace(get_option("siteurl"), "", tom_get_current_url());
    $cpostid = clever_seo_keywords_get_ID_by_slug($slug_to_get);
    if ($cpostid != null) {
      $content = ob_get_contents();
      ob_end_clean();
      $keywords_content = "";
      $description_content = "";
      $html = $content;
      if ($postmeta_row = tom_get_row("postmeta", "*", "
            meta_key = '_clever_seo_keywords_words' AND 
            post_id =".$cpostid)) {
        $html = str_get_html($content);
        if ($html != "") {
          if ($postmeta_row->meta_value != "") {
            if ($html->find("meta[name=keywords]", 0)) {
              $temp = $html->find("meta[name=keywords]", 0)->getAttribute("content");
              if ($temp != "" && !preg_match("/,|, $/", $temp)) {
                $temp .= ", ";
              }
              $html->find("meta[name=keywords]", 0)->setAttribute("content", $temp.$postmeta_row->meta_value);
            } else {
              $keywords_content = "<meta name=\"keywords\" content=\"".$postmeta_row->meta_value."\" />";
            }

            if ($postmeta_row->meta_value != "") {
              if ($html->find("meta[name=description]", 0)) {
                $temp = $html->find("meta[name=description]", 0)->getAttribute("content");
                if (!preg_match("/\.|\. $/", $temp)) {
                  $temp .= ". ";
                }
                $html->find("meta[name=description]", 0)->setAttribute("content", $temp." Keywords: ".$postmeta_row->meta_value);
              } else {
                $description_content = "<meta name=\"description\" content=\"Keywords: ".$postmeta_row->meta_value.".\" />";
              }
            }
          }
        }

        if ($keywords_content != "" || $description_content != "") {
          $e = $html->find("head", 0);
          $e->outertext = $e->makeup().$e->innertext.$keywords_content.$description_content;
        }
      }

      echo $html;
    }
  }
}

add_action( 'widgets_init', 'clever_seo_keywords_register_form_widget' );

/**
 * Adds CleverKeywordsFormWidget widget.
 */
class CleverKeyWordsFormWidget extends WP_Widget {

  /**
   * Register widget with WordPress.
   */
  function __construct() {
    parent::__construct(
      'clever_seo_keywords_widget', // Base ID
      __('Clever SEO Keywords', 'clever_seo_keywords_widget'), // Name
      array( 'description' => __( "A widget that allows you to add an invisible header containing the page's keywords that will boost your SEO score.', 'clever_seo_keywords_widget" ), ) // Args
    );
  }

  /**
   * Front-end display of widget.
   *
   * @see WP_Widget::widget()
   *
   * @param array $args     Widget arguments.
   * @param array $instance Saved values from database.
   */
  public function widget( $args, $instance ) {
    if (function_exists("tom_get_current_url")) {
      $slug_to_get = str_replace(get_option("siteurl"), "", tom_get_current_url());
      $cpostid = clever_seo_keywords_get_ID_by_slug($slug_to_get);
      $seo_content = "";
      if ($cpostid != null) {
        if ($postmeta_row = tom_get_row("postmeta", "*", "
              meta_key = '_clever_seo_keywords_words' AND 
              post_id =".$cpostid)) {
          echo "<div style='display: none;'><h2>".get_option("blogname")." - ".get_the_title()." contains information about: </h2>";
          foreach(explode(",", $postmeta_row->meta_value) as $heading) {
            echo "<h3>".$heading."</h3>";
          }
          echo "<p>If this is not what your looking for, please <a href='".tom_get_current_url()."#'>scroll back to the top</a> and use the navigation links to find your way.</p></div>";
        }
      }
    }
  }

  /**
   * Back-end widget form.
   *
   * @see WP_Widget::form()
   *
   * @param array $instance Previously saved values from database.
   */
  public function form( $instance ) {
  }

  /**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   *
   * @return array Updated safe values to be saved.
   */
  public function update( $new_instance, $old_instance ) {
  }

} // class Foo_Widget

function clever_seo_keywords_register_form_widget() {
  register_widget( 'CleverKeywordsFormWidget' );
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

      // Get content on the actual page.
      if ($html = @file_get_html(get_permalink($my_post->ID))) {
        $keywords_list = array();
        // Travse the pages DOM and look at all readings, dt, dd, anchors, etc.
        foreach($html->find("h1,h2,h3,h4,h5,h6,h7,h8,h9,a,dt,dd,li,strong,em,th,span") as $e) {
          if (strlen($e->outertext) >= 2) {
            // Create first keyword list.
            $keywords_list = get_keyword_list_from_text($keywords_list, $e->outertext);
          }
        }

        // Create keyword list from blog name.
        $keywords_list = get_keyword_list_from_text($keywords_list, get_option("blogname"));

        // Create keyword list from blog description.
        $keywords_list = get_keyword_list_from_text($keywords_list, get_option("blogdescription"));

        $index = 0;
        foreach ($keywords_list as $value) {
          // Only accept keywords between 2 and 20 characters long.
          if (strlen($keywords_list[$index]) > 2 && strlen($keywords_list[$index]) < 20) {
            $keywords_list[$index] = scrub_clever_seo_keyword(tom_titlize_str($keywords_list[$index]));         
          } else {
            $keywords_list[$index] = null;
          } 
          $index++;
        }
        $keywords_list = array_unique(array_filter($keywords_list, 'strlen' ));
        return implode(",", $keywords_list);
      }
    }
  }
}

function get_keyword_list_from_text($keywords_list, $text) {
  $ignore_word_regex = "(&|>|<)";
  return array_merge($keywords_list, preg_split("/(,|-|:| )/", trim(preg_replace("/".$ignore_word_regex."/", "", trim(str_replace(">", "", strip_tags($text)))))), (array)preg_split("/(,|-|:)/", trim(str_replace(">", "", strip_tags($text)))));
}


function print_clever_seo_keywords() {
  if (are_clever_seo_keywords_dependencies_installed()) {
    $slug_to_get = str_replace(get_option("siteurl"), "", tom_get_current_url());
    $cpostid = clever_seo_keywords_get_ID_by_slug($slug_to_get);
    if ($cpostid != "") {
      if ($postmeta_row = tom_get_row("postmeta", "*", "
          meta_key = '_clever_seo_keywords_words' AND 
          post_id =".$cpostid)) {
        echo($postmeta_row->meta_value);
      }
    }
  }
}

function scrub_clever_seo_keyword($keyword) {
  if (are_clever_seo_keywords_dependencies_installed()) {

    // If keyword is one of the following, ignore keyword.
    if (preg_match("/^A$|^I$|^About$|^As$|^Of$|^Our$|^The$|^This$|^That$|^Is$|^Are$|^With$|^And$|^All$|^For$|^Your$|^Skip$|^To$|^Content$/i", $keyword)) {
      return null;
    } else {
      // This keyword may potentially be useful.
      $keyword = preg_replace("/^( )*|( )*$|&nbsp;|Nbsp;|Amp;|&amp;|&#038;/", "", $keyword);
      $keyword = preg_replace("/(&#039;|#039;|#8217;|&#8217;)/", "'", $keyword);
      $scubbed_keyword = "";
      for($i=0; $i<strlen($keyword);$i++) {
        // Check to see if next character is a capital.
        if (preg_match("/[A-Z]/", $keyword{$i})) {
          if ((($i+1) < strlen($keyword)) && preg_match("/[a-z]/", $keyword{($i+1)})) {
            $scubbed_keyword .= " "; // If next letter is a Capital letter and one after is lower case, add a space.
          }
        }

        // Inspect next character, if its a letter or number or quote, accept. Reject everything else.
        if (preg_match("/[a-z|A-Z|0-9| |']/i", $keyword{$i})) {
          $scubbed_keyword .= $keyword{$i};
        }

        // Check to see if next character is lower case.
        if (preg_match("/[a-z]/", $keyword{$i})) {
          if ((($i+1) < strlen($keyword)) && preg_match("/[A-Z]/", $keyword{($i+1)})) {
            $scubbed_keyword .= " "; // If next letter is a Capital letter and one after is lower case, add a space.
          }
        }

      }
      return trim($scubbed_keyword);
    }
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
    <p>Select the keywords you want by clicking on them and then save the page/post. The green keywords are currently being used, while the grey ones are not. Don't be a smarty bum and select all, that approach will harm your SEO score, please take your time per page and select the words that best represent each page.</p>
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
    echo '<input type="hidden" id="clever_keywords_new_field" name="clever_keywords_new_field" value="'.esc_attr($value).'" size="25" />
    <p>If you are having any issues deselecting a keyword or any other issue, try and click the reset button.</p>
    <p><input type="button" name="action" value="Reset" id="reset_keywords"/> <input type="button" name="action" value="Save Changes" id="save_keywords" class="button button-primary button-large" /></p>
    ';
  ?>
  </div>
  <?php
}

/* When the post is saved, saves our custom data */
function clever_keywords_save_postdata( $post_id ) {

  // First we need to check if the current user is authorised to do this action. 
  if (!current_user_can( 'edit_post', $post_id)) {
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

  // Create Tags for each selected Keyword.
  $words = explode(",", $mydata);
  foreach ($words as $word) {
    wp_set_post_tags( $post_ID, $word, true );
  }
  
  // Create the parent page as a category if page has parent.
  $post = get_post($post_ID);

  // Check to see if page has parent.
  if ($post->post_parent) {
    // Page has parent, so create parent category first.
    $id = wp_create_category(get_the_title($post->post_parent));
    // Then create category of current page title.
    wp_create_category(get_the_title($post_ID), $id);
  } else {
    // Then create category of current page title.
    wp_create_category(get_the_title($post_ID));
  }

  // Do something with $mydata 
  // either using 
  add_post_meta($post_ID, '_clever_seo_keywords_words', $mydata, true) or
    update_post_meta($post_ID, '_clever_seo_keywords_words', $mydata);
  // or a custom table (see Further Reading section below)
}

// Setup tags for pages. Set keywords as Tags.
add_action("admin_init", "clever_seo_keywords_register_tags");
function clever_seo_keywords_register_tags() {
  register_taxonomy_for_object_type('post_tag', 'page');
  register_taxonomy_for_object_type('category', 'page');
}

add_filter('request', 'clever_seo_keywords_expanded_request');  
function clever_seo_keywords_expanded_request($q) {
    if (isset($q['tag']) || isset($q['category_name'])) 
                $q['post_type'] = array('post', 'page');
    return $q;
}

?>