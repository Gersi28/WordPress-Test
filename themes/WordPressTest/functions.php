<?php
/**
 * Functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package wordpress-test
 * @since 1.0.0
 */

namespace TestTheme;

require_once get_template_directory() . '/classes/class-header-nav-walker.php';
require_once get_template_directory() . '/classes/class-benefits-functionality.php';

use TestTheme\Classes\Header_Nav_Walker;
use TestTheme\Classes\Benefits_Functionality;

class ThemeFunctions
{
	public function __construct()
	{
		add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
		add_action('init', [$this, 'register_custom_post_types']);
		add_action('init', [$this, 'register_navigation']);
		add_shortcode('main_menu', [$this, 'render_main_menu_shortcode']);
		add_action('init', [$this, 'register_blocks']);
		
		new Benefits_Functionality();
	}
	
	
	/**
	 * Enqueue the CSS and JS files.
	 *
	 * @return void
	 * @see https://developer.wordpress.org/reference/functions/wp_enqueue_style/
	 * @see https://developer.wordpress.org/reference/functions/wp_enqueue_script/
	 */
	public function enqueue_styles()
	{
		wp_enqueue_style(
			'wordpress-test-style',
			get_stylesheet_uri(),
			[],
			wp_get_theme()->get('Version')
		);
		wp_enqueue_style(
			'barlow-font',
			'https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;700&display=swap',
			[]
		);
		wp_enqueue_style(
			'global-custom-styles',
			get_stylesheet_directory_uri() . '/dist/global.css',
			[]
		);
		
		wp_enqueue_script(
			'theme-scripts',
			get_stylesheet_directory_uri() . '/dist/main.js',
			[], '', true
		);
	}
	
	
	/**
	 * Register all custom post types.
	 *
	 * @return void
	 * @see https://developer.wordpress.org/reference/functions/register_post_type/
	 */
	public function register_custom_post_types()
	{
		$post_types = [
			'event' => [
				'menu_name' => 'Events',
				'singular_name' => 'Event',
				'slug' => 'events',
			],
			'learning_center' => [
				'menu_name' => 'Learning Center',
				'singular_name' => 'Learning Material',
				'slug' => 'learning-center',
			],
			'benefit' => [
				'menu_name' => 'Benefits',
				'singular_name' => 'Benefit',
				'slug' => 'benefits',
				'tax' => ['benefit_category'],
			],
		];
		
		foreach ($post_types as $key => $args) {
			$labels = [
				'name' => __($args['menu_name'], 'wordpress-test'),
				'singular_name' => __($args['singular_name'], 'wordpress-test'),
				'menu_name' => $args['menu_name'],
				'name_admin_bar' => $args['singular_name'],
				'add_new' => __('Add New', 'wordpress-test'),
				'add_new_item' => __('Add New ' . $args['singular_name'], 'wordpress-test'),
				'edit_item' => __('Edit ' . $args['singular_name'], 'wordpress-test'),
				'new_item' => __('New ' . $args['singular_name'], 'wordpress-test'),
				'view_item' => __('View ' . $args['singular_name'], 'wordpress-test'),
				'search_items' => __('Search ' . $args['menu_name'], 'wordpress-test'),
				'not_found' => __('No ' . strtolower($args['menu_name']) . ' found', 'wordpress-test'),
				'not_found_in_trash' => __('No ' . strtolower($args['menu_name']) . ' found in trash', 'wordpress-test'),
			];
			
			if ($key === 'learning_center') {
				$labels['not_found'] = __('No learning material found', 'wordpress-test');
				$labels['not_found_in_trash'] = __('No learning material found in trash', 'wordpress-test');
			}
			
			$post_type_args = [
				'labels' => $labels,
				'public' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'show_in_nav_menus' => true,
				'query_var' => true,
				'rewrite' => ['slug' => $args['slug']],
				'capability_type' => 'post',
				'has_archive' => true,
				'hierarchical' => false,
				'supports' => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
				'show_in_rest' => true,
			];
			
			if ($key === 'benefit') {
				$post_type_args['exclude_from_search'] = false;
				$post_type_args['taxonomy'] = ['benefit_category'];
				add_filter('single_template', function ($template) {
					if (is_singular('benefit')) {
						return get_404_template();
					}
					return $template;
				});
			}
			
			register_post_type($key, $post_type_args);
		}
	}
	
	
	/**
	 * Register navigation menus and apply custom Walker class.
	 *
	 * @return void
	 * @see https://developer.wordpress.org/reference/functions/register_nav_menus/
	 * @see https://developer.wordpress.org/reference/hooks/wp_nav_menu_args/
	 */
	public function register_navigation()
	{
		register_nav_menus([
			'main-menu' => __('Main Menu', 'wordpress-test'),
		]);
		
		add_filter('wp_nav_menu_args', function ($args) {
			if (isset($args['block_name']) && $args['block_name'] === 'core/navigation') {
				$args['walker'] = new Header_Nav_Walker();
			}
			return $args;
		});
	}
	
	
	/**
	 * Creating the main menu shortcode, which uses the new walker.
	 *
	 * @return string
	 * @see https://developer.wordpress.org/reference/functions/wp_nav_menu/
	 */
	public function render_main_menu_shortcode()
	{
		if (has_nav_menu('main-menu')) {
			return wp_nav_menu([
				'theme_location' => 'main-menu',
				'container' => 'nav',
				'container_class' => 'main-menu',
				'menu_class' => 'menu-items',
				'echo' => false,
				'walker' => new Header_Nav_Walker(),
			]);
		}
		
		return '<p>' . esc_html__('Please assign a menu to the Main Menu location.', 'wordpress-test') . '</p>';
	}
	
	
	/**
	 * Register block patterns and styles.
	 *
	 * @return void
	 * @see https://developer.wordpress.org/reference/functions/register_block_pattern/
	 * @see https://developer.wordpress.org/reference/functions/register_block_style/
	 */
	public function register_blocks()
	{
		/**
		 * Pattern needed for the custom nav menu with the new walker.
		 */
		register_block_pattern(
			'wordpress-test/menu-pattern',
			[
				'title' => __('Main Menu', 'wordpress-test'),
				'content' => '<!-- wp:shortcode -->[main_menu]<!-- /wp:shortcode -->',
			]
		);
		
		/**
		 * Registering a new block style, needed to extend the existing preset styles.
		 */
		register_block_style('core/button', [
			'name' => 'secondary',
			'label' => __('Secondary Button', 'wordpress-test'),
		]);
	}
}

/**
 * Initializing the above class.
 */
new ThemeFunctions();