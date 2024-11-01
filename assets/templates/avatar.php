<div class="classified-author">
	<div class="classified-avatar">
		<?php echo get_avatar( apply_filters( 'iworks_classifieds_contact_email', 'default', get_the_ID() ), 60 ); ?>
	</div>
	<div class="classified-author-name">
		<?php echo apply_filters( 'classifieds_tpl_single_posted_by', sprintf( '<strong>%s</strong>', get_post_meta( get_the_ID(), 'iworks_classifieds_contact_contact', true ) ), get_the_ID() ); ?><br/>
		<?php printf( __( '%1$s (%2$s ago)', 'simple-classifieds' ), date_i18n( get_option( 'date_format' ), get_post_time( 'U', false, get_the_ID() ) ), human_time_diff( get_post_time( 'U', false, get_the_ID() ), current_time( 'timestamp' ) ) ); ?>
	</div>
</div>

