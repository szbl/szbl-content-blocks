<article class="szbl-content-block" id="szbl-content-block-<?php echo $block->ID; ?>">
	<?php
		if ( $title )
		{
			echo '<' . $title_tag . '>' 
				 . apply_filters( 'szbl_content_block_title', $block->post_title ) 
				 . '</' . $title_tag . '>';
		}
		if ( $image && has_post_thumbnail( $block->ID ) )
		{
			echo '<div class="szbl-content-block-featured-image" id="szbl-content-block-'
				 . (int) $block->ID . '-featured-image">'
				 . get_the_post_thumbnail( $block->ID, $image_size, array( 'class' => $image_class ) )
				 . '</div>';
		}
		echo apply_filters( 'szbl_content_blocks_content', $block->post_content );
	?>
</article>