<style type="text/css">
.wp-menu-image { overflow: hidden; }
#adminmenu #menu-posts-<?php echo $this->get_post_type_slug(); ?> .wp-menu-image {
	background: url('<?php echo plugins_url( 'images/szbl-content-block-icon.png', $this->file ); ?>') no-repeat 6px -17px !important;
	background-position: 6px -17px !important;
}
#adminmenu #menu-posts-<?php echo $this->get_post_type_slug(); ?>:hover .wp-menu-image,
#adminmenu #menu-posts-<?php echo $this->get_post_type_slug(); ?>.wp-has-current-submenu .wp-menu-image {
	background-position: 6px 7px !important;
}
</style>