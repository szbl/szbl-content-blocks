<select name="<?php echo esc_attr( $name ); ?>" id="<?php echo esc_attr( $id ); ?>">
	<?php if ( $show_option_none !== false ) : ?>
	<option value=""><?php echo esc_html( $show_option_none ); ?></option>
	<?php endif; ?>
	
	<?php if ( count( $posts ) > 0 ) : foreach ( $posts as $post ) : ?>
	<option value="<?php echo esc_attr( $post->ID ); ?>"<?php selected( $post->ID, $selected ); ?>><?php
		echo $post->post_title;
	?></option>
	
	<?php endforeach; endif; ?>
	
</select>