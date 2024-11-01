<?php
$location = apply_filters( 'iworks_classifieds_item_location', null );
if ( empty( $location ) ) {
	return;
}
?>
<div class="classified-location classified-grid">
	<span class="classified-icon"><span class="dashicons dashicons-location"></span> <?php esc_html_e( 'Location:', 'simple-classifieds' ); ?></span>
	<span><?php echo $location; ?></span>
</div>
