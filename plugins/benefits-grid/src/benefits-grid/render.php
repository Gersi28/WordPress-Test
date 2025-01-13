<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */

if (!function_exists('render_benefits_block')) {
	function render_benefits_block($attributes)
	{
		$font_size = isset($attributes['fontSize']) ? $attributes['fontSize'] : 16;
		$background_color = isset($attributes['backgroundColor']) ? $attributes['backgroundColor'] : '#ffffff';
		$gradient = isset($attributes['style']['color']['gradient']) ? $attributes['style']['color']['gradient'] : '';
		
		// Create inline styles
		$background = $gradient ? $gradient : $background_color;
		$styles = sprintf(
			'font-size: %spx; background: %s;',
			esc_attr($font_size),
			esc_attr($background)
		);
		
		
		ob_start();
		?>
        <div class="benefits-block">
			<?php
			$terms = get_terms([
				'taxonomy' => 'benefit_category',
				'hide_empty' => false,
			]);
			
			if (!empty($terms) && !is_wp_error($terms)) {
				echo '<div id="taxonomy-filter">';
				foreach ($terms as $index => $term) {
					printf(
						'<button data-term-id="%s" class="%s">%s</button>',
						esc_attr($term->term_id),
						$index === 0 ? 'active' : '',
						esc_html($term->name)
					);
				}
				echo '</div>';
			}
			?>
            <div id="posts-container"></div>
            <div class="load-more-container">
                <button id="load-more" style="display: none;">Load more...</button>
            </div>
        </div>

        <style>
            #posts-container .benefit-item {
                background: <?php echo $background ?> !important;
            }

            #posts-container .benefit-item p {
                font-size: <?php echo $font_size ?>px !important;
            }
        </style>
		<?php
		return ob_get_clean();
	}
}