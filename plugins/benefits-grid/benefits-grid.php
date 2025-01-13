<?php
/**
 * Plugin Name:       Benefits Grid
 * Description:       Example block scaffolded with Create Block tool.
 * Version:           1.0
 * Requires at least: 6.7
 * Requires PHP:      7.4
 * Author:            The WordPress Contributors
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       benefits-grid
 *
 * @package WordpressTest
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

class BenefitsGrid
{
	/**
	 * Initializing the hooks for the block.
	 */
	public function __construct()
	{
		add_action('init', [$this, 'wordpress_test_benefits_grid_block_init']);
		add_action('wp_ajax_filter_posts', [$this, 'filter_posts_callback']);
		add_action('wp_ajax_nopriv_filter_posts', [$this, 'filter_posts_callback']);
	}
	
	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * Localizes the JavaScript so that we can access wpAjax.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 * @see https://developer.wordpress.org/reference/functions/wp_localize_script/
	 */
	public function wordpress_test_benefits_grid_block_init()
	{
		require_once __DIR__ . '/build/benefits-grid/render.php';
		register_block_type(__DIR__ . '/build/benefits-grid', array(
			'render_callback' => 'render_benefits_block'
		));
		
		wp_localize_script('wordpress-test-benefits-grid-view-script', 'wpAjax', [
			'ajaxurl' => admin_url('admin-ajax.php'),
			'nonce' => wp_create_nonce('filter_posts_nonce'),
		]);
	}
	
	/**
	 * Callback to filter posts using AJAX
	 */
	public function filter_posts_callback()
	{
		if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'filter_posts_nonce')) {
			check_ajax_referer('filter_posts_nonce', '_ajax_nonce');
			wp_send_json_error('Invalid nonce.');
			return;
		}
		
		$term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : 6;
		$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
		$posts_per_page = 6;
		$args = [
			'post_type' => 'benefit',
			'posts_per_page' => $posts_per_page,
			'paged' => $page,
			'tax_query' => [
				[
					'taxonomy' => 'benefit_category',
					'field' => 'term_id',
					'terms' => $term_id,
				],
			],
		];
		
		$benefits = new WP_Query($args);
		ob_start();
		if ($benefits->have_posts()) {
			while ($benefits->have_posts()) {
				$benefits->the_post();
				
				$white_image = get_post_meta( get_the_ID(), '_benefits_white_image', true );
				$color_image = get_post_meta( get_the_ID(), '_benefits_color_image', true );
				$textarea    = get_post_meta( get_the_ID(), '_benefits_textarea', true );
				
				
				echo '<div class="benefit-item">';
				if ( $white_image ) {
					printf('<img src="%s" class="white-icon">', esc_url($white_image));
				}
				
				if ( $color_image ) {
					printf('<img src="%s" class="color-icon">', esc_url($color_image));
				}
				
				if( $textarea ) {
					printf('<p>%s</p>', esc_html($textarea));
				}
				
				echo '</div>';
			}
			wp_reset_postdata();
			
			wp_send_json_success([
				'html' => ob_get_clean(),
				'hasMore' => $benefits->max_num_pages > $page,
				'totalPages' => $benefits->max_num_pages
			]);
		} else {
			wp_send_json_error('No benefits found.');
		}
	}
}

new BenefitsGrid();