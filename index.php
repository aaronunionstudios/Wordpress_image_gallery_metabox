<?php
/*
Plugin Name: WordPress Image Gallery Metabox
Plugin URI: https://unionstudios.com/
Description: This plugin adds a image gallery metabox to post, page and custom post types admin page.
Version: 1.0.0
Author: Union Studios
Author URI: https://unionstudios.com/
 */

/*
In your template inside a loop, grab the IDs of all the images with the following:
$images = get_post_meta($post->ID, 'vdw_gallery_id', true);
Then you can loop through the IDs and call wp_get_attachment_link or wp_get_attachment_image to display the images with or without a link respectively:
 */

function gallery_metabox_enqueue($hook)
{
    if ('post.php' == $hook || 'post-new.php' == $hook) {
        wp_enqueue_script('gallery-metabox', plugin_dir_url(__FILE__) . 'js/wordpress-image-gallery-metabox.js', array('jquery', 'jquery-ui-sortable'));
        wp_enqueue_style('gallery-metabox', plugin_dir_url(__FILE__) . 'css/wordpress-image-gallery-metabox.css');
    }
}
add_action('admin_enqueue_scripts', 'gallery_metabox_enqueue');

function add_gallery_metabox($post_type)
{
    $gmb_posttypes = get_option('post_types_post');
    add_meta_box(
        'gallery-metabox',
        'Gallery',
        'gallery_meta_callback',
        $gmb_posttypes,
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_gallery_metabox');

function gallery_meta_callback($post)
{
    wp_nonce_field(basename(__FILE__), 'gallery_meta_nonce');
    $ids = get_post_meta($post->ID, 'vdw_gallery_id', true);

    ?>
    <table class="form-table">
      <tr><td>
        <a class="gallery-add button" href="#" data-uploader-title="Add image(s) to gallery" data-uploader-button-text="Add image(s)">Add image(s)</a>

        <ul id="gallery-metabox-list">
        <?php if ($ids): foreach ($ids as $key => $value): $image = wp_get_attachment_image_src($value);?>

		          <li>
		            <input type="hidden" name="vdw_gallery_id[<?php echo $key; ?>]" value="<?php echo $value; ?>">
		            <img class="image-preview" src="<?php echo $image[0]; ?>">
		            <a class="change-image button button-small" href="#" data-uploader-title="Change image" data-uploader-button-text="Change image">Change image</a><br>
		            <small><a class="remove-image" href="#">Remove image</a></small>
		          </li>

		        <?php endforeach;endif;?>
        </ul>

      </td></tr>
    </table>
  <?php }

add_action('admin_menu', 'gallery_metabox_menu');

function gallery_metabox_menu()
{
    add_options_page('Gallery Metabox', 'Gallery Metabox', 'manage_options', 'gallery_metabox_identifier', 'gallery_metabox_options');
}

function gallery_metabox_options()
{

    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    $hidden_field_name = 'cpt_name_hidden';
    $opt_val = get_option($opt_name);
    $post_type_post_val = get_option('gmb_posttype');

    if (isset($_POST[$hidden_field_name]) && $_POST[$hidden_field_name] == 'Y') {
        $post_type_post_val = $_POST['gmb_posttype'];
        update_option('post_types_post', $post_type_post_val);
        ?>
  <div class="updated"><p><strong><?php _e('settings saved.', 'menu-test');?></strong></p></div>
  <?php
}

    echo '<div class="wrap">';
    echo "<h2>" . __('Gallery Metabox Settings', 'menu-test') . "</h2>";

    ?>
<p>
  Choose the post types you would like the Gallery Meta Box to show on.
</p>
<form name="form1" method="post" action="">
  <input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">

  <?php
  $args = array(
          'public' => true,
          '_builtin' => false,
      );
      $post_types = get_post_types($args);
      array_unshift($post_types, 'post', 'page');
      $gmb_posttypes = get_option('post_types_post');
      foreach ($post_types as $value) {

          if (!empty($gmb_posttypes)) {
              if (in_array($value, $gmb_posttypes)) {
                  $checked = 'checked';
              } else {
                  $checked = '';
              }
          }
          echo ('<input type="checkbox" name="gmb_posttype[]" value="' . $value . '"' . $checked . ' />');
          echo ('post type = ' . $value . '<br>');
      }
      ?>

  <p class="submit">
    <input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save Changes')?>" />
  </p>

</form>
<?php
}

function gallery_meta_save($post_id)
{
    if (!isset($_POST['gallery_meta_nonce']) || !wp_verify_nonce($_POST['gallery_meta_nonce'], basename(__FILE__))) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['vdw_gallery_id'])) {
        update_post_meta($post_id, 'vdw_gallery_id', $_POST['vdw_gallery_id']);
    } else {
        delete_post_meta($post_id, 'vdw_gallery_id');
    }
}
add_action('save_post', 'gallery_meta_save');

?>