<?php
$price = apply_filters( 'iworks_classifieds_item_price', null );
if ( empty( $price ) ) {
	return;
}
?>
<div class="classified-price">
	<span><?php echo $price; ?></span>
</div>
