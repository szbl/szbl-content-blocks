<?php
/*
Plugin Name: Sizeable Content Blocks
Author: Sizeable Labs
Author URI: http://sizeablelabs.com
Description: Basic content blocks using custom post types with wrapper functions to pull these blocks for inclustion in any general theme/plugin output. Great for home page callouts, WYSIWYG HTML widgets and any form of theme integration.
Version: 1.0
License: GPLv2 or later
*/

class Szbl_Content_Blocks
{
	protected static $instance;
	
	const POST_TYPE_SLUG = 'szbl-content-block';
	
	public static function init()
	{
		if ( !isset( self::$instance ) )
			self::$instance = new Szbl_Content_Blocks();
		return self::$instance;
	}
	
	public static function getInstance()
	{
		return self::init();
	}
	
	public function __construct()
	{
		add_action( 'init', array( $this, 'register' ) );
		
		if ( is_admin() )
		{
			add_action( 'admin_head', array( $this, 'admin_head' ) );
		}
		
		add_shortcode( 'szbl_content_block', array( $this, 'shortcode_content_block' ) );
		add_shortcode( 'szbl_content_blocks', array( $this, 'shortcode_content_blocks' ) );
		
		add_filter( 'szbl_content_blocks_content', 'wptexturize' );
		add_filter( 'szbl_content_blocks_content', 'wpautop' );
	}
	
	public function sanitize_view( $path = false )
	{
		$return = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'views'. DIRECTORY_SEPARATOR;
		
		if ( $path )
		{
			$path = trim( $path, '\\/' );
			if ( substr( $path, -4 ) !== '.php' )
				$path .= '.php';
			$return .= $path;
		}
		if ( !file_exists( $return ) )
			return false;
		
		return $return;
	}
	
	private function render( $path, $output = false, $local_vars = null )
	{
		$path = $this->sanitize_view( $path );
		
		if ( !$path )
			return false;
		
		$local_template = get_template_directory() . DIRECTORY_SEPARATOR . 'szbl' . DIRECTORY_SEPARATOR . basename( $path );
		if ( file_exists( $local_template ) )
			$path = $local_template;

		if ( is_array( $local_vars ) && count( $local_vars ) > 0 )
			extract( $local_vars );
		
		if ( $output )
			include $path;
		
		ob_start();
		include $path;
		$output = ob_get_contents();
		ob_end_clean();
		
		return $output;
	}
	
	public function get_post_type_slug()
	{
		return apply_filters( 'szbl_content_blocks_slug', self::POST_TYPE_SLUG );
	}
	
	public function admin_head()
	{
		$this->file = __FILE__;
		echo $this->render( 'admin-css.php' );
	}
	
	public function get_labels()
	{
		$labels = array(
			'name' => __( 'Content Blocks' ),
			'singular_name' => __( 'Content Block' ),
			'add_new' => __( 'Add New' ),
			'add_new_item' => __( 'Add New Content Block' ),
			'edit_item' => __( 'Edit Content Block' ),
			'mew_item' => __( 'New Content Block' ),
			'view_item' => __( 'View Content Block' ),
			'search_items' => __( 'Search Content Blocks' ),
			'not_found' => __( 'No content blocks found.' ),
			'not_found_in_trash' => __( 'No content blocks found in trash.' ),
		);
		return apply_filters( 'szbl_content_blocks-setting-labels', $labels );
	}
	
	public function register()
	{
		if ( apply_filters( 'szbl_content_block-setting-post_thumbnails', true ) )
		{
			add_theme_support( 'post-thumbnails' );
			
			// use this action to add your image sizes
			do_action( 'szbl_content_blocks-setting-thumbnail_sizes' );
		}
		
		$args = array(
			'labels' => $this->get_labels(),
			'description' => 'Manage and extend re-usable locations with address, phone, website, email and more fields attached to the location name, description and image.',
			'public' => true,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'show_iu' => true,
			'show_in_menu' => true,
			'show_in_nav_menus' => false,
			'show_in_admin_bar' => true,
			'menu_position' => 10,
			'capability_type' => 'post',
			'hierarchical' => true,
			'can_export' => true,
			'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields' ),
			'register_meta_box_cb' => array( $this, 'add_meta_boxes' ),
			'has_archive' => false,
			'rewrite' => array()
		);
		
		register_post_type( $this->get_post_type_slug(), apply_filters( 'szbl_content_blocks-args', $args ) );
	}

	public function add_meta_boxes()
	{
		do_action( 'szbl_content_blocks_add_meta_boxes' );
	}
	
	/* 
	 * Merges a set of terms (single, comma-separated or array)
	 * into a tax_query array
	 */
	private function merge_tax_query( $terms, $tax_query, $taxonomy = 'szbl-content-tag', $term_field = 'slug', $operator = 'AND' )
	{
		if ( !is_array( $terms ) )
			$terms = explode( ',', $terms );
		
		$terms = array_map( 'trim', $terms );
		
		if ( !is_array( $tax_query ) )
			$tax_query = array();
		
		$tax_query[] = array(
			'taxonomy' => $taxonomy,
			'field' => $term_field,
			'terms' => $terms,
			'operator' => $operator
		);
		
		return apply_filters( 'szbl_content_blocks-merge_tax_query', $tax_query );
	}
	
	/*
	 * Supports one or more Content Tags via non-core variable named "szbl_content_tags"
	 */
	public function get_content_blocks( $args, $return_single = false )
	{
		$args = shortcode_atts( array(
			'posts_per_page' => get_query_var( 'posts_per_page' ) ? get_query_var( 'posts_per_page' ) : -1,
			'post_status' => 'publish',
			'post_parent' => null,
			'meta_query' => array(),
			'tax_query' => array(),
			'post__in' => '',
			'orderby' => 'menu_order',
			'order' => 'asc',
			'szbl_content_tags' => '',
			'szbl_content_tags_field' => 'slug',
			'szbl_content_tags_operator' => 'AND'
		), $args );
		
		$args['post_type'] = self::POST_TYPE_SLUG;
		
		if ( empty( $args['post__in'] ) )
			unset( $args['post__in'] );
		elseif ( !is_array( $args['post__in'] ) )
			$args['post__in'] = explode( ',', $args['post__in'] );
		
		if ( !empty( $args['szbl_content_tags'] ) )
			$args['tax_query'] = $this->merge_tax_query( $args['szbl_content_tags'], $args['tax_query'], 'szbl-content-tag', $args['szbl_content_tags_field'], $args['szbl_content_tags_operator'] );
		
		unset( $args['szbl_content_tags'] );
		unset( $args['szbl_content_tags_field'] );
		unset( $args['szbl_content_tags_operator'] );
		
		apply_filters( 'szbl_content_blocks-get_content_blocks_args', $args );
		
		$posts = get_posts( $args );
		
		if ( $return_single )
			return apply_filters( 'szbl_get_content_blocks-get_post', $posts[0] );
		else
			return apply_filters( 'szbl_get_content_blocks-get_posts', $posts );
	}
	
	public function get_content_blocks_dropdown( $dropdown_args, $get_args = array() )
	{
		shortcode_atts( array(
			'selected' => '',
			'posts_per_page' => -1,
			'show_option_none' => '- Select Content Block -',
			'name'=> 'szbl-content-blocks-dropdown',
			'id' => 'szbl-content-blocks-dropdown'
		), $dropdown_args );
		
		$posts = $this->get_content_blocks( $get_args );
		
		$render_args = $dropdown_args;
		$render_args['posts'] = $posts;
		
		$this->render( 'dropdown.php', true, $render_args );
	}
	
	public function shortcode_content_block( $atts, $content = '' )
	{
		extract(shortcode_atts(array(
			'post_id' => null,
			'post_parent' => '',
			'orderby' => 'menu_order',
			'order' => 'asc',
			'title' => 'true',
			'image' => 'true',
			'image_size' => 'thumbnail',
			'image_class' => '',
			'title_tag' => 'h3',
			'tags' => ''
		), $atts));
		
		if ( strtolower( $title ) != 'true' )
			$title = false;
		else
			$title = true;
		
		if ( strtolower( $image ) != 'true' )
			$image = false;
		else
			$image = true;
		
		$args = array(
			'orderby' => $orderby,
			'order' => $order,
			'post_parent' => $post_parent,
			'posts_per_page' => 1,
			'szbl_content_tags' => $tags,
		);
		
		if ( !empty( $post_id ) )
		{
			$args['post__in'] = (int) $post_id;
			// post_id overrides tags
			unset( $args['szbl_content_tags'] );
		}
		
		$block = $this->get_content_blocks( $args, true );

		if ( !$block->ID )
			return;
		
		return $this->render( 'szbl_content_block.php', false, array(
			'block' => $block,
			'title' => $title,
			'image' => $image,
			'image_size' => $image_size,
			'image_class' => $image_class,
			'title_tag' => $title_tag
		) );
	}
	
	public function shortcode_content_blocks( $atts, $content = '' )
	{
		extract(shortcode_atts(array(
			'tags' => '',
			'post_ids' => '',
			'post_parent' => '',
			'orderby' => 'menu_order',
			'order' => 'asc',
			'title' => 'true',
			'image' => 'true',
			'image_size' => 'thumbnail',
			'image_class' => '',
			'title_tag' => 'h3',
			'tags' => '',
			'posts_per_page' => -1
		), $atts));
		
		if ( strtolower( $title ) != 'true' )
			$title = false;
		else
			$title = true;
		
		if ( strtolower( $image ) != 'true' )
			$image = false;
		else
			$image = true;
		
		$args = array(
			'posts_per_page' => (int) $posts_per_page,
			'szbl_content_tags' => $tags,
			'post_parent' => $post_parent,
			'orderby' => $orderby,
			'order' => $order,
		);
		
		if ( !empty( $post_id ) )
		{
			$args['post__in'] = (int) $post_id;
			// post_id overrides tags
			unset( $args['szbl_content_tags'] );
		}
		
		$blocks = $this->get_content_blocks( $args );
		
		if ( count( $blocks ) <= 0 )
			return;
		
		return $this->render( 'szbl_content_blocks.php', false, array(
			'blocks' => $blocks,
			'title' => $title,
			'image' => $image,
			'image_size' => $image_size,
			'image_class' => $image_class,
			'title_tag' => $title_tag
		));
	}
	
}

Szbl_Content_Blocks::init();

include dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'szbl-content-blocks-widget.php';

function szbl_get_content_block( $args = array() )
{
	$args['posts_per_page'] = 1;
	return Szbl_Content_Blocks::init()->get_content_blocks( $args, true );
}

function szbl_get_content_blocks( $args = array() )
{
	return Szbl_Content_Blocks::init()->get_content_blocks( $args );
}

function szbl_content_blocks_dropdown( $dropdown_args, $get_args = array() )
{
	return Szbl_Content_Blocks::init()->get_content_blocks_dropdown( $dropdown_args, $get_args );
}