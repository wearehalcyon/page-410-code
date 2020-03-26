<?php
/*
* Plugin Name: Page 410 Code
* Plugin URI: https://artroman.net/
* Description: Plugin for organizing 410 server code. Create your redirects for 410 with the help of this plugin is quick and easy.
* Author: ArtRoman
* Version: 1.0.0
* Author URI: https://artroman.net/
*/

add_action('init', 'redirects_410');
function redirects_410(){
	register_post_type('redirects-410', array(
		'labels'             => array(
			'name'               => 'Redirects 410',
			'singular_name'      => 'Redirects 410',
			'add_new'            => 'Add New Redirect',
			'add_new_item'       => 'Add New Redirect',
			'edit_item'          => 'Edit Redirect',
			'new_item'           => 'New Redirect',
			'view_item'          => 'View Redirect',
			'search_items'       => 'Find Redirects',
			'not_found'          => 'Redirects Not Found',
			'not_found_in_trash' => 'Trash Is Empty',
			'parent_item_colon'  => '',
			'menu_name'          => 'Redirects 410'
		  ),
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
        'menu_icon'          => 'dashicons-image-rotate',
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => false,
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 10000,
		'supports'           => false,
		'rewrite'   		 => array(
			'slug' 		 => false,
			'with_front' => false
		),
	) );
}


/**
 * Revisions etc
 */
if ($_GET['post_type'] == 'redirects-410') {
	define('WP_POST_REVISIONS', false);
	define('AUTOSAVE_INTERVAL', 9999 );
}

add_action( 'admin_menu', 'redirects410_remove_slug_metabox' );
function redirects410_remove_slug_metabox(){
	remove_meta_box( 'slugdiv', 'redirects-410', 'normal' );
}

/**
 * Add fields metabox
 */
function redirects410_create() {
	add_meta_box(
		'_namespace_metabox',
		'Redirect Options',
		'redirects410_render_metabox',
		'redirects-410',
		'normal',
		'default'
	);
	add_meta_box( '_namespace_metabox', 'Redirect Options', 'redirects410_render_metabox', 'page', 'normal', 'default' );
}
add_action( 'add_meta_boxes', 'redirects410_create' );

/**
 * Rendering fields
 */
function redirects410_render_metabox() {
	global $post;
    $sourceUrl = str_replace(home_url(), '', get_post_meta( $post->ID, 'source_url', true ));
    $fullUrl = home_url() . $sourceUrl;
    //$redirectUrl = get_post_meta( $post->ID, 'redirect_url', true );
    echo '<div class="redirectSource">';
        echo '<h4 style="margin:20px 0 0 0;">' . esc_html__( 'Redirect source and values', 'redirects410' ) . '</h4>';
        echo '<span style="display:block;margin-top:15px;margin-bottom:5px;font-style:italic;">' . esc_html__( 'Source URL', 'redirects410' ) . '</span>';
        echo '<input type="text" name="redirects410-source" id="redirects410-source" value="' . esc_attr( $sourceUrl ) . '" style="width:100%;">';
        echo '<p><strong>' . esc_html__( 'This URL was redirected to 410 code: ', 'redirects410' ) . '</strong><a href="' . $fullUrl . '" target="_blank">' . $fullUrl . '</a></p>';
        //echo '<span style="display:block;margin-top:15px;margin-bottom:5px;">' . esc_html__( 'Redirect URL', 'redirects410' ) . '</span>';
        //echo '<input type="text" name="redirects410-redirect" id="redirects410-redirect" value="' . esc_attr( $redirectUrl ) . '" style="width:100%;">';
    echo '</div>';
	wp_nonce_field( 'redirects410-source-nonce', 'redirects410-source-process' );
}

/**
 * Save data
 */
function redirects410_save( $post_id, $post ) {
	if ( !isset( $_POST['redirects410-source-process'] ) ) return;
	if ( !wp_verify_nonce( $_POST['redirects410-source-process'], 'redirects410-source-nonce' ) ) {
		return $post->ID;
	}
	if ( !current_user_can( 'edit_post', $post->ID )) {
		return $post->ID;
	}
	if ( !isset( $_POST['redirects410-source'] ) && !isset( $_POST['redirects410-redirect'] ) ) {
		return $post->ID;
	}
	$sanitizedSrc = str_replace(home_url(), '', wp_filter_post_kses( $_POST['redirects410-source'] ));
    //$sanitizedVal = wp_filter_post_kses( $_POST['redirects410-redirect'] );
	update_post_meta( $post->ID, 'source_url', $sanitizedSrc );
    //update_post_meta( $post->ID, 'redirect_url', $sanitizedVal );
}
add_action( 'save_post', 'redirects410_save', 1, 2 );

/**
 * Dashboard Edit Page Modification
 */
if ($_GET['post_type'] == 'redirects-410') {
    add_action( 'admin_head-edit.php', 'redirects410_dashboard_item_title' );
    function redirects410_dashboard_item_title() {
        add_filter( 'the_title', 'redirects410_new_title', 100, 2 );
        function redirects410_new_title( $title, $id ) {
            global $wpdb, $post;
            $table_name = $wpdb->prefix . "postmeta";
            $postID = $post->ID;

            $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE post_id = $postID AND meta_key = 'source_url'" );
            foreach ($results as $result) {
                return home_url() . $result->meta_value;
            }
        }
    }
}

/**
 * Add Admin Table List Column
 */
if ($_GET['post_type'] == 'redirects-410') {
	add_filter( 'manage_redirects-410_posts_columns', 'redirects410_filter_posts_columns' );
	function redirects410_filter_posts_columns( $columns ) {
	    $columns = array(
	      'cb' => $columns['cb'],
	      'title' => esc_html__( 'Title', 'redirects410' ),
	      'redirected_slug' => esc_html__( 'Redirected Slug', 'redirects410' ),
	      'date' => esc_html__( 'Date', 'redirects410' ),
	    );
	    return $columns;
	}

	add_action( 'manage_posts_custom_column', 'redirects410_redirected_slug', 10 );
	function redirects410_redirected_slug(){
	    global $post;
	    $value = get_post_meta( $post->ID, 'source_url' );
	    echo '<span style="display:block;font-style:italic;">' . $value[0] . '</span>';
	    echo '<span>' . esc_html__( 'Status code: 410', 'redirects410' ) . '</span>';
	}

	add_filter('display_post_states', '__return_false');
}

/**
 * Publish metabox
 *
 * While under consideration.
 * It is planned to modify this part of the plugin in the future.
 * If you have any ideas - feel free to bring them here.
 */
add_action('add_meta_boxes', 'change_meta_box_titles');
function change_meta_box_titles() {
	global $wp_meta_boxes;
	$wp_meta_boxes['redirects-410']['side']['core']['submitdiv']['title'] = esc_html__('Submit Redirect', 'redirects410');
}



// Hook to wp_insert_post_data
add_filter( 'wp_insert_post_data', 'wpse_36118_force_published' );


/**
 * Init Redirect
 */
add_action( 'plugin_loaded', 'redirects410_init', 10 );
function redirects410_init(){
    global $wpdb;
    $table_name = $wpdb->prefix . "postmeta";

    $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE meta_key = 'source_url'" );

    foreach ($results as $result) {
        $status = get_post_status( $result->post_id );
        if($_SERVER['REQUEST_URI'] === $result->meta_value && ($status === 'publish' || $status === 'draft')) {
            header( "HTTP/1.1 410 Gone" );
            exit;
        }
    }
}
