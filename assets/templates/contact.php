<div class="classified-contact-wrap">
	<div class="classified-contact classified-grid">
		<span></span>
		<span><button data-id="<?php echo esc_attr( get_the_ID() ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'classified-' . get_the_ID() ) ); ?>" type="button"><span class="dashicons dashicons-megaphone"></span> <?php esc_html_e( 'Reveal contact info', 'simple-classifieds' ); ?></button></span>
	</div>
</div>
