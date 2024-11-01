<?php

function iworks_classifieds_options() {
	$iworks_classifieds_options = array();
	/**
	 * main settings
	 */
	$parent = add_query_arg( 'post_type', 'iworks_classified', 'edit.php' );

	$iworks_classifieds_options['index']    = array(
		'version'    => '1.0.1',
		'use_tabs'   => true,
		'page_title' => __( 'Configuration', 'simple-classifieds' ),
		'menu'       => 'submenu',
		'parent'     => $parent,
		'options'    => array(
			array(
				'type'  => 'heading',
				'label' => __( 'Emails', 'simple-classifieds' ),
			),
			array(
				'type'  => 'subheading',
				'label' => __( 'Reply-to e-mail address', 'simple-classifieds' ),
			),
			array(
				'name'              => 'emails_reply_to',
				'type'              => 'email',
				'th'                => __( 'Reply to', 'simple-classifieds' ),
				'sanitize_callback' => 'sanitize_email',
				'classes'           => array( 'large-text' ),
			),
			array(
				'type'  => 'subheading',
				'label' => __( 'Administrator notification of new classified', 'simple-classifieds' ),
			),
			array(
				'name'    => 'emails_new_classified_admin_subject',
				'th'      => __( 'Subject', 'simple-classifieds' ),
				'default' => __( 'New Classified %CLASSIFIED_TITLE%', 'simple-classifieds' ),
				'classes' => array( 'large-text' ),
			),
			array(
				'name'     => 'emails_new_classified_admin_content',
				'type'     => 'textarea',
				'th'       => __( 'Content', 'simple-classifieds' ),
				'classes'  => array( 'large-text' ),
				'rows'     => 10,
				'default'  => __(
					'Hello,
New classified: %CLASSIFIED_TITLE% %CLASSIFIED_PERMALINK%
Categories: %CLASSIFIED_CATEGORIES%
Author login: %CLASSIFIED_AUTHOR_LOGIN%
Author email: %CLASSIFIED_AUTHOR_EMAIL%

Content:
%CLASSIFIED_CONTENT%
',
					'simple-classifieds'
				),
				'autoload' => 'no',
			),
			array(
				'type'  => 'subheading',
				'label' => __( 'User  notification of new classified', 'simple-classifieds' ),
			),
			array(
				'name'     => 'emails_new_classified_user_subject',
				'type'     => 'text',
				'th'       => __( 'Subject', 'simple-classifieds' ),
				'default'  => __( 'New Classified %CLASSIFIED_TITLE%', 'simple-classifieds' ),
				'classes'  => array( 'large-text' ),
				'autoload' => 'no',
			),
			array(
				'name'     => 'emails_new_classified_user_content',
				'type'     => 'textarea',
				'th'       => __( 'Content', 'simple-classifieds' ),
				'classes'  => array( 'large-text' ),
				'rows'     => 10,
				'default'  => __(
					'Hello,
New classified: %CLASSIFIED_TITLE% %CLASSIFIED_PERMALINK%
Categories: %CLASSIFIED_CATEGORIES%
Author login: %CLASSIFIED_AUTHOR_LOGIN%
Author email: %CLASSIFIED_AUTHOR_EMAIL%

Content:
%CLASSIFIED_CONTENT%
',
					'simple-classifieds'
				),
				'autoload' => 'no',
			),
			array(
				'type'  => 'heading',
				'label' => __( 'General', 'simple-classifieds' ),
			),
			array(
				'name'              => 'general_allow_to_add',
				'type'              => 'checkbox',
				'th'                => __( 'Allow to add', 'simple-classifieds' ),
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
			),
			array(
				'type'  => 'heading',
				'label' => __( 'Classified', 'simple-classifieds' ),
			),
			array(
				'name'              => 'category_show',
				'type'              => 'checkbox',
				'th'                => __( 'Category', 'simple-classifieds' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'description'       => __( 'Use classified categories.', 'simple-classifieds' ),
			),
			array(
				'name'                => 'category_slug',
				'type'                => 'text',
				'th'                  => __( 'Category slug', 'simple-classifieds' ),
				'default'             => 'cf-category',
				'description'         => __( 'Classified category slug.', 'simple-classifieds' ),
				'flush_rewrite_rules' => 'yes',
			),
			array(
				'name'              => 'renewal_status',
				'type'              => 'checkbox',
				'th'                => __( 'Renewal', 'simple-classifieds' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
				'description'       => __( 'Force to renevel after selected period of time.', 'simple-classifieds' ),
			),
			array(
				'name'              => 'renewal_days',
				'type'              => 'number',
				'th'                => __( 'Renewal days', 'simple-classifieds' ),
				'default'           => 7 * 5,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'small-text' ),
				'description'       => __( 'Number of days to show classified..', 'simple-classifieds' ),
			),
			array(
				'type'  => 'heading',
				'label' => __( 'Listing', 'simple-classifieds' ),
			),
			array(
				'name'              => 'listing_show_social_media',
				'type'              => 'checkbox',
				'th'                => __( 'Show social media links', 'simple-classifieds' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
			),
			array(
				'name'              => 'listing_show_boats_table',
				'type'              => 'checkbox',
				'th'                => __( 'Show boats table', 'simple-classifieds' ),
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
			),
			array(
				'name'              => 'listing_tag_to_person',
				'type'              => 'checkbox',
				'th'                => __( 'Tag to person', 'simple-classifieds' ),
				'description'       => __( 'Replace person tag by fleet person.', 'simple-classifieds' ),
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
			),
			array(
				'name'              => 'listing_show_articles_with_listing_tag',
				'type'              => 'checkbox',
				'th'                => __( 'Add posts list', 'simple-classifieds' ),
				'description'       => __( 'Add posts list to person with matching tag.', 'simple-classifieds' ),
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
			),
			array(
				'type'  => 'heading',
				'label' => __( 'Boats', 'simple-classifieds' ),
			),
			array(
				'name'              => 'boat_add_crew_manually',
				'type'              => 'checkbox',
				'th'                => __( 'Add crew manually', 'simple-classifieds' ),
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
			),
			array(
				'name'              => 'boat_add_extra_data',
				'type'              => 'checkbox',
				'th'                => __( 'Add extra data', 'simple-classifieds' ),
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
			),
			array(
				'name'              => 'boad_add_social_media',
				'type'              => 'checkbox',
				'th'                => __( 'Add boat social media', 'simple-classifieds' ),
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
			),
			array(
				'name'    => 'boat_taxonomies',
				'type'    => 'checkbox_group',
				'th'      => __( 'Boat taxonomies', 'simple-classifieds' ),
				'options' => array(
					'sail' => __( 'Sail manufacturer', 'simple-classifieds' ),
					'mast' => __( 'Mast manufacturer', 'simple-classifieds' ),
					'hull' => __( 'Hull manufacturer', 'simple-classifieds' ),
				),
			),
			array(
				'name'              => 'boat_auto_add_feature_image',
				'type'              => 'checkbox',
				'th'                => __( 'Auto add feature image', 'simple-classifieds' ),
				'description'       => __( 'Automagicaly add feature image, if there is some taged with boat number.', 'simple-classifieds' ),
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'classes'           => array( 'switch-button' ),
			),
		),
	);
	$iworks_classifieds_options['currency'] = array(
		'options' => array(
			'currency'         => array(
				'PLN' => array(
					'label'         => __( 'Polish zloty', 'simple-classifieds' ),
					'symbol'        => __( 'PLN', 'simple-classifieds' ),
					'thousands_sep' => '&nbsp;',
				),
				'EUR' => array(
					'label'  => __( 'European euro', 'simple-classifieds' ),
					'symbol' => '&euro;',
				),
				'USD' => array(
					'label'  => __( 'United States dollar', 'simple-classifieds' ),
					'symbol' => '$',
					'sign'   => 'left',
					'space'  => '',
				),
			),
			'currency_default' => 'PLN',
		),
	);
	return $iworks_classifieds_options;
}

