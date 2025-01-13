<?php

namespace TestTheme\Classes;

class Header_Nav_Walker extends \Walker_Nav_Menu
{
    /**
    * Array of the posts that should be looped on the sidebar.
    *
    * @var array[]
    */
	private $sidebar_sections = [
		'learning_center' => [
			'post_type' => 'learning_center',
			'title' => 'Learning Center',
			'icon' => '<svg width="20" height="24" viewBox="0 0 20 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M1 1H19V23H1V1Z" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M15 14H10 M6 14H5 M12 18H10 M6 18H5 M13 10H10 M6 10H5 M15 6H10 M6 6H5" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>'
		],
		'post' => [
			'post_type' => 'post',
			'title' => 'Blog',
			'icon' => '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 19H5V17.687C5 16.474 5.725 15.38 6.846 14.915C7.981 14.445 9.67 14 12 14C14.33 14 16.019 14.445 17.154 14.916C18.275 15.38 19 16.474 19 17.687V19Z M12 11C14.2091 11 16 9.20914 16 7C16 4.79086 14.2091 3 12 3C9.79086 3 8 4.79086 8 7C8 9.20914 9.79086 11 12 11Z M1 5V1H5 M23 5V1H19 M1 19V23H5 M23 19V23H19" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>'
		]
	];
 
	public function start_lvl(&$output, $depth = 0, $args = null)
	{
		$indent = str_repeat("\t", $depth);
		$classes = ['sub-menu'];
		
		if ($depth === 0) {
			$classes[] = 'mega-menu';
		}
		
		$class_names = join(' ', apply_filters('nav_menu_submenu_css_class', $classes, $args, $depth));
		$class_attr = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
		
		// Start buffering output
		ob_start();
		
		if ($depth === 0) {
			?>
            <div<?php $class_attr ?>>
            <div class="mega-menu-wrapper">
            <div class="mega-menu-sidebar">
	            <?php
	            foreach ($this->sidebar_sections as $section) {
		            $this->render_sidebar_section($section);
	            }
	            ?>
            </div>
            <div class="mega-menu-content">
                <div class="content-head">
                    <svg width="20" height="24" viewBox="0 0 20 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1 1H19V23H1V1Z" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M15 14H10 M6 14H5 M12 18H10 M6 18H5 M13 10H10 M6 10H5 M15 6H10 M6 6H5" stroke="#FF2E51" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <h6>Events</h6>
                </div>
                <ul>
			<?php
		} else {
			?>
        <ul<?php $class_attr ?>>
			<?php
		}
		
		$output .= ob_get_clean();
	}
    
    /**
    * Render the sidebar learning center and blog posts.
    *
    * @param $section
    * @return void
    */
    private function render_sidebar_section($section)
    {
        $query = new \WP_Query([
            'post_type' => $section['post_type'],
            'posts_per_page' => 5,
            'order' => 'DESC',
            'orderby' => 'date',
            'no_found_rows' => true,
        ]);
        
        if ($query->have_posts()) : ?>
            <div class="side-posts">
                <div class="side-head">
                    <?php
                    echo $section['icon'];
                    echo '<h6>' . esc_html($section['title']) . '</h6>';
                    ?>
                </div>
                <ul class="side-nav">
                    <?php while ($query->have_posts()) : $query->the_post(); ?>
                        <li><a href="<?php echo esc_url(get_permalink()) ?>"><?php echo esc_html(get_the_title())
                        ?></a></li>
                    <?php endwhile; ?>
                </ul>
            </div>
        <?php
        endif;
        wp_reset_postdata();
    }
    
    
    /**
    * Displays a list of the post types under the menu items
    *
    * @param $menu_items
    * @return string
    */
    private function display_menu_post_types($menu_items): string {
        $post_types = [];
    
        foreach ($menu_items as $menu_item) {
            $post_type = get_post_type($menu_item->object_id);
            if ($post_type && !in_array($post_type, $post_types)) {
                $post_types[] = $post_type;
            }
        }
        
        echo '<pre>';
        var_dump($menu_items);
        echo '</pre>';
        
        $pt_as_string = '';
        foreach($post_types as $post_type) {
            echo '<pre>';
            var_dump($post_type);
            echo '</pre>';
            
            if ( isset($pt_as_string) ) {
                $pt_as_string .= $post_type;
            } else {
                $pt_as_string .= ', ' . $post_type;
            }
        }
        
        return $pt_as_string;
    }
	
	public function end_lvl(&$output, $depth = 0, $args = null)
	{
		$indent = str_repeat("\t", $depth);
		
		if ($depth === 0) {
			$output .= "$indent\t\t\t</ul>\n";
			$output .= "$indent\t\t</div>\n";
            $output .= $this->render_mega_menu_footer();
			$output .= "$indent\t</div>\n";
			$output .= "$indent</div>\n";
		} else {
			$output .= "$indent</ul>\n";
		}
	}
    
    private function render_mega_menu_footer() {
        ob_start();
    ?>
        <div class="mega-menu-footer">
            <div>
                <h6><?php echo esc_html__('Ready to get started?', 'wordpress-test'); ?></h6>
                <p><?php echo esc_html__('See how our application works, how easy it is', 'wordpress-test'); ?></p>
            </div>
            <a href="<?php echo esc_url(home_url('/#')); ?>" class="button"><?php echo esc_html__('Watch demo', 'wordpress-test'); ?></a>
        </div>
    <?php
    return ob_get_clean();
    }
	
	public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
	{
		$indent = ($depth) ? str_repeat("\t", $depth) : '';
		$classes = empty($item->classes) ? [] : (array)$item->classes;
		$classes[] = 'menu-item-' . $item->ID;
		
		if (in_array('menu-item-has-children', $classes)) {
			$classes[] = 'has-mega-menu';
		}
		
		$class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args, $depth));
		$class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';
		
		$id = apply_filters('nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args, $depth);
		$id = $id ? ' id="' . esc_attr($id) . '"' : '';
        
        /**
        * Getting the excerpt of the post.
        */
		$description = get_the_excerpt($item->object_id);
		
		$output .= $indent . '<li' . $id . $class_names . '>';
		$atts = [];
		$atts['title'] = !empty($item->attr_title) ? $item->attr_title : '';
		$atts['target'] = !empty($item->target) ? $item->target : '';
		$atts['rel'] = !empty($item->xfn) ? $item->xfn : '';
		$atts['href'] = !empty($item->url) ? $item->url : '';
		$atts = apply_filters('nav_menu_link_attributes', $atts, $item, $args, $depth);
		
        $attributes = '';
		foreach ($atts as $attr => $value) {
			if (!empty($value)) {
				$value = esc_attr($value);
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}
		
		$item_output = $args->before;
		$item_output .= '<a' . $attributes . '>';
		$item_output .= $args->link_before . apply_filters('the_title', $item->title, $item->ID) . $args->link_after;
        
		if (!empty($description) && $depth > 0) {
			$item_output .= '<span class="menu-item-description">' . esc_html($description) . '</span>';
		}
		
		$item_output .= '</a>';
		$item_output .= $args->after;
		
		$output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
	}
	
	public function end_el(&$output, $item, $depth = 0, $args = null)
	{
		$output .= "</li>\n";
	}
}