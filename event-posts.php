<?php
/*
Plugin Name: LTP Productions
Plugin URI: https://github.com/msinkula/ltp-productions
Description: Creates a custom post type for Latino Thetre Projects Productions with associated metaboxes. 
Version: 1.0
Author: Mike Sinkula 
Author URI: http://www.premiumdw.com/
License: GPLv2 or later
*/

/**
 * Created from Devin Price's Event Plugin. 
 * 
 * @link http://wptheming.com/2011/11/event-posts-in-wordpress/
 * @link https://github.com/devinsays/event-posts
 *
 */

/**
 * Flushes rewrite rules on plugin activation to ensure event posts don't 404
 * 
 * @link http://codex.wordpress.org/Function_Reference/flush_rewrite_rules
 */

function ep_eventposts_activation() {
	ep_eventposts();
	// flush_rewrite_rules();
}


/**
 * Register the Plugin Activation Hook
 *
 * @link https://codex.wordpress.org/Function_Reference/register_activation_hook
 **/ 

register_activation_hook( __FILE__, 'ep_eventposts_activation' );


/**
 * Register the Productions Custonm Post Type
 *
 * @link http://codex.wordpress.org/Function_Reference/register_post_type
 **/ 

function ep_eventposts() {

	$labels = array(
		'name' => __( 'Productions', 'eventposttype' ),
		'singular_name' => __( 'Production', 'eventposttype' ),
		'add_new' => __( 'Add New', 'eventposttype' ),
		'add_new_item' => __( 'Add New', 'eventposttype' ),
		'edit_item' => __( 'Edit Production', 'eventposttype' ),
		'new_item' => __( 'Add New Production', 'eventposttype' ),
		'view_item' => __( 'View Production', 'eventposttype' ),
        'all_items' => __( 'All Productions', 'eventposttype' ),
		'search_items' => __( 'Search Productions', 'eventposttype' ),
		'not_found' => __( 'No Productions found', 'eventposttype' ),
		'not_found_in_trash' => __( 'No Productions found in trash', 'eventposttype' )
	);

	$args = array(
    	'labels' => $labels,
    	'public' => true,
		'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'capability_type' => 'post',
		'rewrite' => array("slug" => "production"), // Permalinks format
		'menu_position' => 5,
        'menu_icon' => 'dashicons-calendar', // https://www.kevinleary.net/wordpress-dashicons-list-custom-post-type-icons/
		'has_archive' => true,
	); 

	register_post_type( 'event', $args );
}

add_action( 'init', 'ep_eventposts' );


/**
 * Adds event post metaboxes for start time and end time
 *
 * @link http://codex.wordpress.org/Function_Reference/add_meta_box
 *
 * We want two time event metaboxes, one for the start time and one for the end time.
 * Two avoid repeating code, we'll just pass the $identifier in a callback.
 * If you wanted to add this to regular posts instead, just swap 'event' for 'post' in add_meta_box.
 **/

function ep_eventposts_metaboxes() {
	add_meta_box( 'ept_event_date_start', 'Start Date and Time', 'ept_event_date', 'event', 'side', 'default', array( 'id' => '_start') );
	add_meta_box( 'ept_event_date_end', 'End Date and Time', 'ept_event_date', 'event', 'side', 'default', array('id'=>'_end') );
	add_meta_box( 'ept_event_location', 'Event Location', 'ept_event_location', 'event', 'side', 'default', array('id'=>'_end') );
}
add_action( 'admin_init', 'ep_eventposts_metaboxes' );

// Metabox HTML
function ept_event_date($post, $args) {
	$metabox_id = $args['args']['id'];
	global $post, $wp_locale;
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'ep_eventposts_nonce' );
	$time_adj = current_time( 'timestamp' );
	$month = get_post_meta( $post->ID, $metabox_id . '_month', true );
	if ( empty( $month ) ) {
		$month = gmdate( 'm', $time_adj );
	}
	$day = get_post_meta( $post->ID, $metabox_id . '_day', true );
	if ( empty( $day ) ) {
		$day = gmdate( 'd', $time_adj );
	}
	$year = get_post_meta( $post->ID, $metabox_id . '_year', true );
	if ( empty( $year ) ) {
		$year = gmdate( 'Y', $time_adj );
	}
	
	$hour = get_post_meta($post->ID, $metabox_id . '_hour', true);
 
    if ( empty($hour) ) {
        $hour = gmdate( 'H', $time_adj );
    }
 
    $min = get_post_meta($post->ID, $metabox_id . '_minute', true);
 
    if ( empty($min) ) {
        $min = '00';
    }
	$month_s = '<select name="' . $metabox_id . '_month">';
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$month_s .= "\t\t\t" . '<option value="' . zeroise( $i, 2 ) . '"';
		if ( $i == $month )
			$month_s .= ' selected="selected"';
		$month_s .= '>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
	}
	$month_s .= '</select>';
	echo $month_s;
	echo '<input type="text" name="' . $metabox_id . '_day" value="' . $day  . '" size="2" maxlength="2" />';
    echo '<input type="text" name="' . $metabox_id . '_year" value="' . $year . '" size="4" maxlength="4" /> @ ';
    echo '<input type="text" name="' . $metabox_id . '_hour" value="' . $hour . '" size="2" maxlength="2"/>:';
    echo '<input type="text" name="' . $metabox_id . '_minute" value="' . $min . '" size="2" maxlength="2" />';
 
}

function ept_event_location() {
	global $post;
	// Use nonce for verification
	wp_nonce_field( plugin_basename( __FILE__ ), 'ep_eventposts_nonce' );
	// The metabox HTML
	$event_location = get_post_meta( $post->ID, '_event_location', true );
	echo '<label for="_event_location">Location:&nbsp;</label>';
	echo '<input type="text" name="_event_location" value="' . $event_location  . '" />';
}

// Save the Metabox Data
function ep_eventposts_save_meta( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return;
	if ( !isset( $_POST['ep_eventposts_nonce'] ) )
		return;
	if ( !wp_verify_nonce( $_POST['ep_eventposts_nonce'], plugin_basename( __FILE__ ) ) )
		return;
	// Is the user allowed to edit the post or page?
	if ( !current_user_can( 'edit_post', $post->ID ) )
		return;
	// OK, we're authenticated: we need to find and save the data
	// We'll put it into an array to make it easier to loop though
	
	$metabox_ids = array( '_start', '_end' );
	foreach ($metabox_ids as $key ) {
	    
	    $aa = $_POST[$key . '_year'];
		$mm = $_POST[$key . '_month'];
		$jj = $_POST[$key . '_day'];
		$hh = $_POST[$key . '_hour'];
		$mn = $_POST[$key . '_minute'];
		
		$aa = ($aa <= 0 ) ? date('Y') : $aa;
		$mm = ($mm <= 0 ) ? date('n') : $mm;
		$jj = sprintf('%02d',$jj);
		$jj = ($jj > 31 ) ? 31 : $jj;
		$jj = ($jj <= 0 ) ? date('j') : $jj;
		$hh = sprintf('%02d',$hh);
		$hh = ($hh > 23 ) ? 23 : $hh;
		$mn = sprintf('%02d',$mn);
		$mn = ($mn > 59 ) ? 59 : $mn;
		
		$events_meta[$key . '_year'] = $aa;
		$events_meta[$key . '_month'] = $mm;
		$events_meta[$key . '_day'] = $jj;
		$events_meta[$key . '_hour'] = $hh;
		$events_meta[$key . '_minute'] = $mn;
	    $events_meta[$key . '_eventtimestamp'] = $aa . $mm . $jj . $hh . $mn;
	    
    }
    
    // Save Locations Meta

    $events_meta['_event_location'] = $_POST['_event_location'];	
 
	// Add values of $events_meta as custom fields
	foreach ( $events_meta as $key => $value ) { // Cycle through the $events_meta array!
		if ( $post->post_type == 'revision' ) return; // Don't store custom data twice
		$value = implode( ',', (array)$value ); // If $value is an array, make it a CSV (unlikely)
		if ( get_post_meta( $post->ID, $key, FALSE ) ) { // If the custom field already has a value
			update_post_meta( $post->ID, $key, $value );
		} else { // If the custom field doesn't have a value
			add_post_meta( $post->ID, $key, $value );
		}
		if ( !$value ) delete_post_meta( $post->ID, $key ); // Delete if blank
	}
}
add_action( 'save_post', 'ep_eventposts_save_meta', 1, 2 );


// Get the Month Abbreviation
 
function eventposttype_get_the_month_abbr($month) {
    global $wp_locale;
    for ( $i = 1; $i < 13; $i = $i +1 ) {
        if ( $i == $month )
            $monthabbr = $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) );
        }
    return $monthabbr;
}
 
// Display the date
function eventposttype_get_the_event_date() {
    global $post;
    $eventdate = '';
    $month = get_post_meta($post->ID, '_month', true);
    $eventdate = eventposttype_get_the_month_abbr($month);
    $eventdate .= ' ' . get_post_meta($post->ID, '_day', true) . ',';
    $eventdate .= ' ' . get_post_meta($post->ID, '_year', true);
    $eventdate .= ' at ' . get_post_meta($post->ID, '_hour', true);
    $eventdate .= ':' . get_post_meta($post->ID, '_minute', true);
    echo $eventdate;
}

// Add custom CSS to style the metabox
add_action('admin_print_styles-post.php', 'ep_eventposts_css');
add_action('admin_print_styles-post-new.php', 'ep_eventposts_css');
function ep_eventposts_css() {
	wp_enqueue_style('your-meta-box', plugin_dir_url( __FILE__ ) . '/event-post-metabox.css');
}


/**
 * Customize Event Query using Post Meta
 * 
 * @link http://www.billerickson.net/customize-the-wordpress-query/
 * @param object $query data
 **/

function ep_event_query( $query ) {
	// http://codex.wordpress.org/Function_Reference/current_time
	$current_time = current_time('mysql'); 
	list( $today_year, $today_month, $today_day, $hour, $minute, $second ) = preg_split( '([^0-9])', $current_time );
	$current_timestamp = $today_year . $today_month . $today_day . $hour . $minute;
	global $wp_the_query;
	
	if ( $wp_the_query === $query && !is_admin() && is_post_type_archive( 'event' ) ) {
		$meta_query = array(
			array(
				'key' => '_start_eventtimestamp',
				'value' => $current_timestamp,
				'compare' => '>'
			)
		);
		$query->set( 'meta_query', $meta_query );
		$query->set( 'orderby', 'meta_value_num' );
		$query->set( 'meta_key', '_start_eventtimestamp' );
		$query->set( 'order', 'ASC' );
		$query->set( 'posts_per_page', '2' );
	}
}
add_action( 'pre_get_posts', 'ep_event_query' );


/* Add the Columns to the Admin
 *
 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/manage_$post_type_posts_custom_column
 * @param object $columns data
 **/

// Set the Labels for the Columns
function set_event_columns($columns) {
    return array(
        'cb' => '<input type="checkbox" />',
        'title' => __('Title'),
        'featured_image' => __('Featured Image'),
        'start_date' => __('Start Date' ),
        'end_date' => __('End Date'),
        'location' => __('Location'),
        'date' => __('Date'),
    );
}

add_filter('manage_event_posts_columns' , 'set_event_columns');


// Pull the Values for the Columns
function custom_event_column($column) {

    global $post;
    
    switch ($column) {

    case 'featured_image' :
    echo the_post_thumbnail('thumbnail');
    break;

    case 'start_date' :
    echo get_post_meta($post->ID, '_start_month', true) . '/';
    echo get_post_meta($post->ID, '_start_day', true) . '/';
    echo get_post_meta($post->ID, '_start_year', true);
    break;

    case 'end_date' :
    echo get_post_meta($post->ID, '_end_month', true) . '/';
    echo get_post_meta($post->ID, '_end_day', true) . '/';
    echo get_post_meta($post->ID, '_end_year', true); 
    break;
            
    case 'location' :
    echo get_post_meta($post->ID, '_event_location', true); 
    break;

    }
}

add_action('manage_event_posts_custom_column' , 'custom_event_column');

?>