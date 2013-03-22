<?php
// keep original post
$org = $GLOBALS['post'];

// override the query
$GLOBALS['post'] = $block;
setup_postdata( $block );
?>
<article class="szbl-content-block" id="szbl-content-block-<?php echo $block->ID; ?>">
	<?php
		if ( $title )
		{
			echo '<' . $title_tag . '>' 
				 . apply_filters( 'szbl_content_block_title', get_the_title() ) 
				 . '</' . $title_tag . '>';
		}
		if ( $image && has_post_thumbnail() )
		{
			echo '<div class="szbl-content-block-featured-image" id="szbl-content-block-'
				 . get_the_ID() . '-featured-image">';
			the_post_thumbnail( $image_size, array( 'class' => $image_class ) );
			echo '</div>';
		}
		echo apply_filters( 'szbl_content_blocks_content', get_the_content() );
	?>
</article>
<?php
// reset query
$GLOBALS['post'] = $org;
setup_postdata( $org );