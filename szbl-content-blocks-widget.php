<?php
/*
	Basic Content Block widget.
*/
class Szbl_Content_Block_Widget extends WP_Widget
{
	const CLASSNAME = 'szbl-content-block-widget';

	public function __construct()
	{
		parent::__construct(
			'szbl_content_block_widget',
			'Content Block',
			array(
				'description' => __( "Place a Sizeable Content Block in your theme's sidebars." ),
				'classname' => self::CLASSNAME
			)
		);
	}

	public function widget( $args, $instance )
	{
		extract( $args );

		if ( !isset( $instance['post_id'] ) )
			return;

		$block = get_post( $instance['post_id'] );

		if ( !$block )
			return;

		if ( $instance['title'] ) 
			$title = apply_filters( 'widget_title', $instance['title'] );

		// make sure our class name exists
		if ( false === strpos( self::CLASSNAME, $before_widget ) )
			$before_widget = str_replace( 'class="widget', 'class="widget ' . $this->class_name, $before_widget );

		echo $before_widget;

		if ( isset( $title ) && !empty( $title ) )
			echo $before_title . $title . $after_title;

		if ( has_post_thumbnail( $block->ID ) )
		{
			if ( isset( $instance['image_before'] ) && isset( $instance['image_before'] ) )
			{
				echo '<div class="szbl-content-block-featured-image szbl-content-block-image-before">';
				echo get_the_post_thumbnail( $block->ID, $image_size );
				echo '</div>';
			}
		}

		if ( isset( $instance['show_post_title'] ) && $instance['show_post_title'] )
		{
			echo '<' . apply_filters( 'szbl_content_block_widget_post_title_tag', 'h3' ) . '>';
			$post_title = apply_filters( 'szbl_content_block_widget_post_title', $block->post_title, $block );
			echo $post_title . '</' . apply_filters( 'szbl_content_block_widget_post_title_tag', 'h3' ) . '>';
		}

		do_action( 'szbl_content_block_widget_before_content' );

		echo apply_filters( 'szbl_content_block_widget_content', $block->post_content, $block );

		do_action( 'szbl_content_block_widget_after_content' );

		if ( has_post_thumbnail( $block->ID ) )
		{
			if ( isset( $instance['image_after'] ) && isset( $instance['image_after'] ) )
			{
				echo '<div class="szbl-content-block-featured-image szbl-content-block-image-after">';
				echo get_the_post_thumbnail( $block->ID, $image_size );
				echo '</div>';
			}
		}

		echo $after_widget;
	}

	public function update( $new, $old )
	{
		$instance = array();

		$keys = array( 'title', 'post_id', 'image_before', 'image_after', 'image_size', 'show_post_title' );

		foreach ( $keys as $key )
		{
			if ( isset( $new[ $key ] ) )
				$instance[ $key ] = strip_tags( $new[ $key ] );
		}

		return $instance;
	}

	public function form( $data )
	{
		extract( $data );
?>
<p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Widget Title:' ); ?></label> 
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'post_id' ); ?>"><?php _e( 'Content Block:' ); ?></label> 
	<select class="widefat" id="<?php echo $this->get_field_id( 'post_id' ); ?>" name="<?php echo $this->get_field_name( 'post_id' ); ?>">
		<option value="">- Select a Content Block -</option>
	<?php
		$args = array(
			'orderby' => 'menu_order',
			'order' => 'asc',
			'posts_per_page' => 999
		);
		$posts = szbl_get_content_blocks( apply_filters( 'szbl_content_block_widget_', $args ) );
		if ( count( $posts ) > 0 ) : foreach ( $posts as $post ) : 
	?>
		<option value="<?php echo (int) $post->ID; ?>"<?php selected( $post->ID, $post_id ); ?>><?php
			echo esc_html( $post->post_title );
		?></option>
	<?php endforeach; endif; ?>

	</select>
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'image_before' ); ?>">
		<input
			id="<?php echo $this->get_field_id( 'image_before' ); ?>"
			name="<?php echo $this->get_field_name( 'image_before' ); ?>"
			type="checkbox"
			value="1"
			<?php checked( 1, $image_before ); ?>
		>
		<?php _e( 'Show Featured Image Before' ); ?>
	</label> 
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'show_post_title' ); ?>">
		<input
			id="<?php echo $this->get_field_id( 'show_post_title' ); ?>"
			name="<?php echo $this->get_field_name( 'show_post_title' ); ?>"
			type="checkbox"
			value="1"
			<?php checked( 1, $show_post_title ); ?>
		>
		<?php _e( 'Show Content Block Title' ); ?>
	</label> 
</p>
<p>
	<label for="<?php echo $this->get_field_id( 'image_after' ); ?>">
		<input
			id="<?php echo $this->get_field_id( 'image_after' ); ?>"
			name="<?php echo $this->get_field_name( 'image_after' ); ?>"
			type="checkbox"
			value="1"
			<?php checked( 1, $image_after ); ?>
		>
		<?php _e( 'Show Featured Image After' ); ?>
	</label> 
</p>
<p>
	<?php
		$sizes = get_intermediate_image_sizes();
		$sizes[] = 'full';
	?>
	<label for="<?php echo $this->get_field_id( 'image_size' ); ?>"><?php _e( 'Image Size:' ); ?></label> 
	<select class="widefat" id="<?php echo $this->get_field_id( 'image_size' ); ?>" name="<?php echo $this->get_field_name( 'image_size' ); ?>">
		<?php foreach ( $sizes as $size ) : ?>
		<option value="<?php echo esc_attr( $size ); ?>"<?php selected( $size, $image_size ); ?>><?php
			echo esc_attr( $size );
		?></option>
		<?php endforeach; ?>

	</select>
</p>

<?php
	}

}

add_action( 'widgets_init', create_function( '', 'register_widget( "Szbl_Content_Block_Widget" );') );