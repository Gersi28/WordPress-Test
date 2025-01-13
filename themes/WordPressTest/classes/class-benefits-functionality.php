<?php

namespace TestTheme\Classes;

class Benefits_Functionality
{
	/**
	 * Initializing everything.
	 *
	 * @return void
	 */
	public function __construct()
	{
		add_action('init', [self::class, 'benefit_category_taxonomy']);
		add_action('template_redirect', [self::class, 'redirect_single_benefit_post']);
		add_action('save_post', [self::class, 'benefits_save_meta_box']);
		add_action('add_meta_boxes', [self::class, 'benefits_meta_box']);
	}
	
	/**
     * Registering the custom taxonomy needed for the benefits post type.
     *
	 * @return void
     * @see https://developer.wordpress.org/reference/functions/register_taxonomy/
	 */
	public static function benefit_category_taxonomy()
	{
		register_taxonomy('benefit_category', 'benefit',
			array(
				'hierarchical' => true,
				'label' => 'Categories',
				'query_var' => true,
				'show_ui' => true,
				'show_in_rest' => true,
				'rewrite' => array(
					'slug' => 'benefits_category',
					'with_front' => false
				),
				'labels' => array(
					'name' => __('Categories', 'wordpress-test'),
					'singular_name' => __('Category', 'wordpress-test'),
					'search_items' => __('Search Categories', 'wordpress-test'),
					'all_items' => __('All Categories', 'wordpress-test'),
					'parent_item' => null,
					'parent_item_colon' => null,
					'edit_item' => __('Edit Category', 'wordpress-test'),
					'update_item' => __('Update Category', 'wordpress-test'),
					'add_new_item' => __('Add New Category', 'wordpress-test'),
					'new_item_name' => __('New Category Name', 'wordpress-test'),
					'menu_name' => __('Categories', 'wordpress-test'),
					'not_found' => __('No categories found', 'wordpress-test'),
				),
			)
		);
	}
	
 
	/**
	 * Redirecting the single benefit page to the home page.
	 *
	 * @return void;
     * @see https://developer.wordpress.org/reference/functions/wp_redirect/
	 */
	public static function redirect_single_benefit_post()
	{
		if (is_singular('benefits')) {
			wp_redirect(home_url(), 302);
			exit;
		}
	}

	
	/**
	 * Adding the custom meta fields to the Benefit post type.
	 *
	 * @return void
	 * @see https://developer.wordpress.org/reference/functions/add_meta_box/
	 */
	public static function benefits_meta_box()
	{
		add_meta_box(
			'custom_benefits_meta_box',
			'Additional Fields',
			[self::class, 'benefits_meta_box_callback'],
			'benefit',
			'side',
			'default'
		);
	}
	
 
	/**
	 * The callback method used to generate the content for meta box fields.
	 * Called on when adding meta boxes.
	 *
	 * @param $post
	 * @return void
	 */
	public static function benefits_meta_box_callback($post)
	{
		// Retrieve existing values
		$textarea = get_post_meta($post->ID, '_benefits_textarea', true);
		$color_image = get_post_meta($post->ID, '_benefits_color_image', true);
		$white_image = get_post_meta($post->ID, '_benefits_white_image', true);
		
		// Add a nonce for security
		wp_nonce_field('benefits_meta_box_nonce', 'benefits_nonce');
		?>
        <p>
            <label for="benefits_textarea"><strong>Text Area</strong></label>
            <textarea id="benefits_textarea" name="benefits_textarea" rows="5" style="width:100%;"><?php echo esc_textarea($textarea); ?></textarea>
        </p>
        <p>
            <label for="benefits_color_image"><strong>Color Icon</strong></label><br>
            <input type="text" id="benefits_color_image" name="benefits_color_image"
                   value="<?php echo esc_url($color_image); ?>" style="width:70%;"/>
            <button class="button benefits_upload_button" data-target="benefits_color_image">Upload</button>
        </p>
        <p>
            <label for="benefits_white_image"><strong>White Icon</strong></label><br>
            <input type="text" id="benefits_white_image" name="benefits_white_image"
                   value="<?php echo esc_url($white_image); ?>" style="width:70%;"/>
            <button class="button benefits_upload_button" data-target="benefits_white_image">Upload</button>
        </p>
        <script>
          jQuery(document).ready(function ($) {
            $('.benefits_upload_button').click(function (e) {
              e.preventDefault();

              let inputField = $('#' + $(this).data('target'));
              let customUploader = wp.media({
                title: 'Select Image',
                button: {text: 'Use this image'},
                multiple: false
              }).on('select', function () {
                let attachment = customUploader.state().get('selection').first().toJSON();
                inputField.val(attachment.url);
              }).open();
            });
          });
        </script>
		<?php
	}
	
	/**
	 * Updating the meta field values.
	 *
	 * @param $post_id
	 * @return void
	 * @see https://developer.wordpress.org/reference/functions/update_post_meta/
	 */
	public static function benefits_save_meta_box($post_id)
	{
		/**
		 * Verifying the nonce for security purposes.
		 */
		if (!isset($_POST['benefits_nonce']) || !wp_verify_nonce($_POST['benefits_nonce'], 'benefits_meta_box_nonce')) {
			return;
		}
		
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}
		
		/**
		 * Saving the textarea.
		 */
		if (isset($_POST['benefits_textarea'])) {
			update_post_meta($post_id, '_benefits_textarea', sanitize_textarea_field($_POST['benefits_textarea']));
		}
		
		/**
		 * Saving the image fields
		 */
		if (isset($_POST['benefits_color_image'])) {
			update_post_meta($post_id, '_benefits_color_image', esc_url_raw($_POST['benefits_color_image']));
		}
		if (isset($_POST['benefits_white_image'])) {
			update_post_meta($post_id, '_benefits_white_image', esc_url_raw($_POST['benefits_white_image']));
		}
	}
}