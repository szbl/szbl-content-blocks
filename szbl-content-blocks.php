<?php
/*
Plugin Name: Content Blocks
Author: Sizeable Interactive
Author URI: http://sizeableinteractive.com/labs/
Description: Basic content blocks using custom post types with wrapper functions to pull these blocks for inclustion in any general theme/plugin output. Great for home page callouts, WYSIWYG HTML widgets and any form of theme integration.
*/

class Szbl_Content_Blocks
{
	const POST_TYPE_SLUG = 'szbl-content-block';
	protected static $instance;
	
	public static function getInstance()
	{
		if ( !isset( self::$instance ) )
			self::$instance = new Szbl_Content_Blocks();
		return self::$instance;
	}
	
	private function __construct()
	{
		add_action( 'init', array( $this, 'register_post_types' ) );
		if ( is_admin() )
		{
			add_action( 'admin_head', array( $this, 'admin_head' ) );
		}
	}
	
	public function admin_head()
	{
?>
<style type="text/css">
.wp-menu-image { overflow: hidden; }
#adminmenu #menu-posts-szbl-content-block .wp-menu-image {
	background: url('<?php echo plugins_url( 'images/szbl-content-block-icon.png', __FILE__ ); ?>') no-repeat 6px -17px !important;
	background-position: 6px -17px !important;
}
#adminmenu #menu-posts-szbl-content-block:hover .wp-menu-image,
#adminmenu #menu-posts-szbl-content-block.wp-has-current-submenu .wp-menu-image {
	background-position: 6px 7px !important;
}
</style>
<?php
	}
	
	public function register_post_types()
	{
		global $_wp_theme_features;
		if ( !$_wp_theme_features['post-thumbnails'] )
		{
			add_theme_support( 'post-thumbnails' );
		}
		
		register_post_type( self::POST_TYPE_SLUG, array(
			'labels' => array(
				'name' => 'Content Blocks',
				'singular_name' => 'Content Block',
				'add_new' => 'Add New',
				'add_new_item' => 'Add New Content Block',
				'edit_item' => 'Edit Content Block',
				'mew_item' => 'New Content Block',
				'view_item' => 'View Content Block',
				'search_items' => 'Search Content Blocks',
				'not_found' => 'No content blocks found.',
				'not_found_in_trash' => 'No content blocks found in trash.',
			),
			'public' => false,
			'show_in_menu' => true,
			'show_ui' => true,
			'hierarchical' => true,
			'supports' => array( 'title', 'editor', 'page-attributes', 'thumbnail', 'custom-fields' )
		));
	}

	public static function get_content_blocks( $args, $return_single = false )
	{
		$args = shortcode_atts( array(
			'posts_per_page' => get_query_var( 'posts_per_page' ) ? get_query_var( 'posts_per_page' ) : -1,
			'post_status' => 'publish',
			'post_parent' => null,
			'meta_query' => array(),
			'tax_query' => array(),
			'szbl_content_tags' => array(),
			'szbl_content_tag_field' => array(),
			'orderby' => 'menu_order',
			'order' => 'asc'
		), $args );
		
		if ( isset( $args['szbl_content_tags'] ) && taxonomy_exists( 'szbl-content-tag' ) )
		{
			$tags = $args['szbl_content_tags'];
			
			if ( !is_array( $tags ) )
				$tags = array( $tags );
			
			if ( !isset( $args['tax_query'] ) || !is_array( $args['tax_query'] ) )
				$args['tax_query'] = array();
				
			$args['tax_query'][] = array(
				'taxonomy' => 'szbl-content-tag',
				'field' => isset( $args['szbl_content_tag_field'] ) && $args['szbl_content_tag_field'] == 'id' ? 'id' : 'slug',
				'terms' => $tags
			);
		}
		
		$args['post_type'] = self::POST_TYPE_SLUG;
		
		apply_filters( 'szbl_pre_get_content_blocks_args', $args );
		
		$posts = get_posts( $args );
		
		if ( $return_single )
			return apply_filters( 'szbl_get_content_blocks_post', $posts[0] );
		else
			return apply_filters( 'szbl_get_content_blocks_posts', $posts );
	}
	
	public static function get_content_blocks_dropdown( $dropdown_args, $get_args = array() )
	{
		extract( shortcode_atts( array(
			'selected' => '',
			'posts_per_page' => -1,
			'show_option_none' => '- Select Content Block -',
			'name'=> 'szbl-content-blocks-dropdown',
			'id' => 'szbl-content-blocks-dropdown'
		), $dropdown_args ) );
		if ( !$get_args )
			$get_args = $dropdown_args;
		$posts = self::get_content_blocks( $get_args );
		include dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'dropdown.php';
	}
	
}
Szbl_Content_Blocks::getInstance();

function szbl_get_content_block( $args = array() )
{
	$args['posts_per_page'] = 1;
	return Szbl_Content_Blocks::get_content_blocks( $args, true );
}

function szbl_get_content_blocks( $args = array() )
{
	return Szbl_Content_Blocks::get_content_blocks( $args );
}

function szbl_get_content_blocks_dropdown( $dropdown_args, $get_args = array() )
{
	return Szbl_Content_Blocks::get_content_blocks_dropdown($dropdown_args, $get_args );
}