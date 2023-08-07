<?php
function outOfStockToHidden(){
	$products = new WP_Query( array(
		   'post_type' => 'product',
		   'posts_per_page' => -1,
		   'post_status' => 'publish',
		   'meta_query' =>array(
			   array(
					'key' => '_stock_status',
					'value' => 'outofstock'
				)),
			'tax_query'      => array( 
			   'relation' => 'AND',
				array(
					'taxonomy' => 'product_tag',
					'field' => 'slug',
					'terms' => 'coming-soon',
					'operator' => 'NOT IN',
				),
			   array(
				'taxonomy'        => 'product_visibility',
				'field'    => 'name',
				'terms'           =>  array('exclude-from-search', 'exclude-from-catalog'),
				'operator'        => 'NOT IN',
			))
		
		) );
	
	if ( $products->have_posts() ){
			while ( $products->have_posts() ){
				$products->the_post();
				$terms = array( 'exclude-from-catalog', 'exclude-from-search' );
				wp_set_object_terms( $products->post->ID, $terms, 'product_visibility' );
			}
			wp_reset_postdata();
		}
}

function isa_add_every_hour( $schedules ) {
    $schedules['every_hour'] = array(
            'interval'  => 60 * 3,
            'display'   =>'Every Hour'
    );
    return $schedules;
}
add_filter( 'cron_schedules', 'isa_add_every_hour' );

// Schedule an action if it's not already scheduled
if ( ! wp_next_scheduled( 'isa_add_every_hour' ) ) {
    wp_schedule_event( time(), 'every_hour', 'isa_add_every_hour' );
}
add_action( 'isa_add_every_hour', 'outOfStockToHidden' );