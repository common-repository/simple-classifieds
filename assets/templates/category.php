<?php
$categories = apply_filters( 'iworks_classifieds_item_categories', null );
if ( empty( $categories ) ) {
	return;
}
?>
<div class="classified-categories classified-grid">
	<span class="classified-icon"><span class="dashicons dashicons-category"></span> <?php esc_html_e( 'Category:', 'simple-classifieds' ); ?></span>
	<span><?php echo $categories; ?></span>
</div>
