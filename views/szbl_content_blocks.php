<section id="szbl-content-blocks">

<?php
	foreach ( $blocks as $block )
		include dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'szbl_content_block.php';
?>

</section>