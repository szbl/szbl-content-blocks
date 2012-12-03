<article class="szbl-content-block" id="szbl-content-block-<?php echo $block->ID; ?>">
	<?php
		if ( $title )
			echo '<' . $title_tag . '>' 
				 . apply_filters( 'szbl-content-block-title', $block->post_title ) 
				 . '</' . $title_tag . '>';
		
		echo apply_filters( 'szbl-content-block-content', $block->post_content );
	?>
</article>