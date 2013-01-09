<section id="szbl-content-blocks">
<?php
	foreach ( $this->blocks as $block )
		include dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'shortcode-content-block.php';
?>

</section>