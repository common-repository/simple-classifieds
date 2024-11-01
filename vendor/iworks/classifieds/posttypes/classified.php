<?php
/*
Copyright 2019-2021 Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

defined( 'WPINC' ) || exit;

if ( class_exists( 'iworks_classifieds_posttypes_classified' ) ) {
	return;
}

require_once dirname( dirname( __FILE__ ) ) . '/posttypes.php';

class iworks_classifieds_posttypes_classified extends iworks_classifieds_posttypes {

	protected $post_type_name = 'iworks_classified';

	/**
	 * Single classified meta field name
	 */
	private $single_classified_field_name = 'iworks_classified';

	/**
	 * Taxonomies
	 */
	private $taxonomies = array(
		'category' => 'classified_category',
	);

	/**
	 * Classified hash key
	 *
	 * @since 1.0.1
	 *
	 * @var string
	 */
	private $hash_name = '_iworks_classified_key';

	/**
	 * Classified renewal key
	 *
	 * @since 1.0.1
	 *
	 * @var string
	 */
	private $renewal_name = '_iworks_classified_renewal';

	public function __construct() {
		parent::__construct();
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_filter( 'the_excerpt', array( $this, 'the_excerpt' ) );
		add_action( 'init', array( $this, 'set_data' ), 11 );
		add_action( 'init', array( $this, 'register_expired_status' ), 1 );
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_action( 'wp_ajax_classified_get_contact_data', array( $this, 'ajax_get_contact_data' ) );
		add_action( 'wp_ajax_nopriv_classified_get_contact_data', array( $this, 'ajax_get_contact_data' ) );
		add_action( 'save_post', array( $this, 'set_expiration_date' ), 11, 3 );
		/**
		 * add columns
		 */
		add_filter( 'manage_' . $this->post_type_name . '_posts_columns', array( $this, 'set_custom_columns' ) );
		add_action( 'manage_' . $this->post_type_name . '_posts_custom_column', array( $this, 'get_custom_column_value' ), 10, 2 );
		/**
		 * Register helper filters
		 */
		add_filter( 'iworks_classifieds_item_price', array( $this, 'filter_helper_get_price' ), 10, 2 );
		add_filter( 'iworks_classifieds_item_categories', array( $this, 'filter_helper_get_categories' ), 10, 2 );
		add_filter( 'iworks_classifieds_item_location', array( $this, 'filter_helper_get_location' ), 10, 2 );
		add_filter( 'iworks_classifieds_contact_email', array( $this, 'filter_helper_get_contact_email' ), 10, 2 );
	}

	private function get_price( $post_id, $result = 'text' ) {
		$meta_name = $this->options->get_option_name( 'item_price', false );
		$value     = wp_parse_args(
			get_post_meta( $post_id, $meta_name, true ),
			array(
				'integer'  => 0,
				'currency' => null,
			)
		);
		if ( empty( $value['currency'] ) || empty( $value['integer'] ) ) {
			if ( 'text' === $result ) {
				return __( 'Price is not set.', 'simple-classifieds' );
			}
			return null;
		}
		switch ( $result ) {
			case 'number':
				return $value['integer'];
			case 'currency':
				return $value['currency'];
		}
		$data = $this->options->get_group( 'currency' );
		$data = wp_parse_args(
			$data['options']['currency'][ $value['currency'] ],
			array(
				'sign'          => 'right',
				'decimals'      => 0,
				'dec_point'     => '.',
				'thousands_sep' => ',',
				'space'         => ' ',
			)
		);
		$mask = '%1$s%3$s%2$s';
		if ( 'left' === $data['sign'] ) {
			$mask = '%2$s%3$s%1$s';
		}
		return sprintf(
			$mask,
			number_format(
				$value['integer'],
				$data['decimals'],
				$data['dec_point'],
				$data['thousands_sep']
			),
			$data['symbol'],
			$data['space']
		);
	}

	public function body_class( $classes ) {
		if ( ! is_singular() ) {
			return $classes;
		}
		$post_type = get_post_type();
		if ( $post_type != $this->post_type_name ) {
			return $classes;
		}
		/**
		 * is price set?
		 */
		$price = $this->get_price( get_the_ID(), 'number' );
		$state = 'is';
		if ( empty( $price ) ) {
			$state = 'is-not';
		}
		$classes[] = sprintf( 'classified-price-%s-set', $state );
		/**
		 * Currency
		 */
		if ( ! empty( $price ) ) {
			$classes[] = sprintf(
				'classified-price-currency-%s',
				strtolower( $this->get_price( get_the_ID(), 'currency' ) )
			);
		}
		return $classes;
	}

	/**
	 * Helper to get contact email
	 *
	 * @since 1.0.0
	 */
	public function filter_helper_get_contact_email( $content = null, $post_id = null ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}
		if ( empty( $post_id ) ) {
			return $content;
		}
		$meta_name = $this->options->get_option_name( 'contact_email', false );
		$value     = get_post_meta( $post_id, $meta_name, true );
		if ( empty( $value ) ) {
			return $content;
		}
		return $value;
	}

	/**
	 * Helper to get item location
	 *
	 * @since 1.0.0
	 */
	public function filter_helper_get_location( $content = null, $post_id = null ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}
		if ( empty( $post_id ) ) {
			return $content;
		}
		$meta_name = $this->options->get_option_name( 'location_location', false );
		$value     = get_post_meta( $post_id, $meta_name, true );
		if ( is_array( $value ) ) {
			$value   = array_filter( array_map( 'trim', $value ) );
			$content = implode( ', ', $value );
		}
		return $content;
	}

	/**
	 * Helper to get classified categories
	 *
	 * @since 1.0.0
	 */
	public function filter_helper_get_categories( $content = null, $post_id = null ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}
		if ( empty( $post_id ) ) {
			return $content;
		}
		return get_the_term_list( $post_id, $this->taxonomies['category'] );
	}

	/**
	 * Helper to get item prices
	 *
	 * @since 1.0.0
	 */
	public function filter_helper_get_price( $content = null, $post_id = null ) {
		if ( empty( $post_id ) ) {
			$post_id = get_the_ID();
		}
		if ( empty( $post_id ) ) {
			return $content;
		}
		return $this->get_price( $post_id );
	}

	public function register_expired_status() {
		register_post_status(
			'expired',
			array(
				'label'                     => _x( 'Expired', 'classified' ),
				'label_count'               => _n_noop( 'Expired <span class="count">(%s)</span>', 'Expired <span class="count">(%s)</span>', 'classified' ),
				'exclude_from_search'       => true,
				'show_in_rest'              => true,
				'show_in_admin_status_list' => true,
				'show_in_admin_all_list'    => true,
			)
		);
	}

	public function set_data() {
		/**
		 * replace names to proper
		 */
		if ( ! is_a( $this->options, 'iworks_options' ) ) {
			return;
		}
		/**
		 * Single classified meta field name
		 */
		$this->single_classified_field_name = $this->options->get_option_name( 'classified', true );
		/**
		 * fields
		 */
		$data       = $this->options->get_group( 'currency' );
		$currencies = array();
		foreach ( $data['options']['currency'] as $key => $currency ) {
			$currencies[ $key ] = $currency['label'];
		}
		$this->fields = array(
			'contact'  => array(
				'contact' => array( 'label' => __( 'Name', 'simple-classifieds' ) ),
				'email'   => array( 'label' => __( 'Email', 'simple-classifieds' ) ),
				'phone'   => array( 'label' => __( 'Phone', 'simple-classifieds' ) ),
			),
			'item'     => array(
				'price' => array(
					'label' => __( 'Price', 'simple-classifieds' ),
					'type'  => 'money',
					'args'  => array(
						'kind'             => 'simple',
						'currency'         => $currencies,
						'currency_default' => $data['options']['currency_default'],
					),
				),
			),
			'location' => array(
				'location' => array(
					'type' => 'location',
				),
			),
		);
		/**
		 * add class to metaboxes
		 */
		foreach ( array_keys( $this->fields ) as $name ) {
			if ( 'basic' == $name ) {
				continue;
			}
			$key = sprintf( 'postbox_classes_%s_%s', $this->get_name(), $name );
			add_filter( $key, array( $this, 'add_defult_class_to_postbox' ) );
		}
	}

	/**
	 * Add default class to postbox,
	 */
	public function add_defult_class_to_postbox( $classes ) {
		$classes[] = 'iworks-type';
		return $classes;
	}

	public function register() {
		if ( ! is_a( $this->options, 'iworks_options' ) ) {
			return;
		}
		global $iworks_classifieds;
		/**
		 * taxonomies configuration
		 */
		$labels = array(
			'name'          => _x( 'Categories', 'taxonomy general name', 'simple-classifieds' ),
			'singular_name' => _x( 'Category', 'taxonomy singular name', 'simple-classifieds' ),
		);
		$args   = array(
			'labels'            => $labels,
			'hierarchical'      => true,
			'query_var'         => true,
			'rewrite'           => array( 'slug' => $this->options->get_option( 'category_slug' ) ),
			'show_admin_column' => true,
			'show_in_rest'      => true,
		);
		register_taxonomy( $this->taxonomies['category'], array(), apply_filters( 'classified_register_taxonomy', $args, 'category', $this->taxonomies['category'] ) );
		/**
		 * post_type
		 */
		$labels = array(
			'name'                  => _x( 'Classifieds', 'classified General Name', 'simple-classifieds' ),
			'singular_name'         => _x( 'Classified', 'classified Singular Name', 'simple-classifieds' ),
			'menu_name'             => __( 'Classifieds', 'simple-classifieds' ),
			'name_admin_bar'        => __( 'Classified', 'simple-classifieds' ),
			'archives'              => __( 'Classifieds', 'simple-classifieds' ),
			'attributes'            => __( 'Classified Attributes', 'simple-classifieds' ),
			'all_items'             => __( 'Classifieds', 'simple-classifieds' ),
			'add_new_item'          => __( 'Add New classified', 'simple-classifieds' ),
			'add_new'               => __( 'Add New', 'simple-classifieds' ),
			'new_item'              => __( 'New classified', 'simple-classifieds' ),
			'edit_item'             => __( 'Edit classified', 'simple-classifieds' ),
			'update_item'           => __( 'Update classified', 'simple-classifieds' ),
			'view_item'             => __( 'View classified', 'simple-classifieds' ),
			'view_items'            => __( 'View classifieds', 'simple-classifieds' ),
			'search_items'          => __( 'Search classified', 'simple-classifieds' ),
			'not_found'             => __( 'Not found', 'simple-classifieds' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'simple-classifieds' ),
			'featured_image'        => __( 'Featured Image', 'simple-classifieds' ),
			'set_featured_image'    => __( 'Set featured image', 'simple-classifieds' ),
			'remove_featured_image' => __( 'Remove featured image', 'simple-classifieds' ),
			'use_featured_image'    => __( 'Use as featured image', 'simple-classifieds' ),
			'insert_into_item'      => __( 'Insert into classified', 'simple-classifieds' ),
			'uploaded_to_this_item' => __( 'Uploaded to this classified', 'simple-classifieds' ),
			'items_list'            => __( 'Classifieds list', 'simple-classifieds' ),
			'items_list_navigation' => __( 'Classifieds list navigation', 'simple-classifieds' ),
			'filter_items_list'     => __( 'Filter classifieds list', 'simple-classifieds' ),
		);
		$args   = array(
			'label'                => __( 'Classified', 'simple-classifieds' ),
			'labels'               => $labels,
			'supports'             => array( 'title', 'editor', 'thumbnail', 'revision' ),
			'hierarchical'         => false,
			'public'               => true,
			'show_ui'              => true,
			'show_in_menu'         => true,
			'show_in_admin_bar'    => true,
			'show_in_nav_menus'    => true,
			'can_export'           => true,
			'has_archive'          => _x( 'simple-classifieds', 'slug for archive', 'simple-classifieds' ),
			'exclude_from_search'  => false,
			'publicly_queryable'   => true,
			'capability_type'      => 'page',
			'menu_icon'            => 'dashicons-megaphone',
			'register_meta_box_cb' => array( $this, 'register_meta_boxes' ),
			'rewrite'              => array(
				'slug' => _x( 'classified', 'slug for single classified', 'simple-classifieds' ),
			),
			'show_in_rest'         => true,
			'taxonomies'           => array( $this->taxonomies['category'] ),
		);
		register_post_type( $this->post_type_name, $args );
	}

	/**
	 * Check & generate classified hash key
	 *
	 * @since 1.0.1
	 */
	private function update_post_key( $post_id ) {
		$value = get_post_meta( $post_id, $this->hash_name, true );
		if ( empty( $value ) ) {
			$value = wp_generate_password( 32, false, false );
			delete_post_meta( $post_id, $this->hash_name );
			add_post_meta( $post_id, $this->hash_name, $value, true );
		}
	}

	public function save_post_meta( $post_id, $post, $update ) {
		$this->update_post_key( $post_id );
		$result = $this->save_post_meta_fields( $post_id, $post, $update, $this->fields );
		if ( ! $result ) {
			return;
		}
	}


	/**
	 * Add extra data to do classified excerpt
	 *
	 * @since 1.0.0
	 */
	public function the_excerpt( $excerpt ) {
		$post_type = get_post_type();
		if ( $post_type != $this->post_type_name ) {
			return $excerpt;
		}
		$before = $after = '';
		/**
		 * before
		 */
		$before .= '<div class="classified-excerpt">';
		$before .= $this->get_template( 'price' );
		$before .= $this->get_template( 'category' );
		$before .= '</div>';
		return $before . $excerpt . $after;
	}


	/**
	 * Add extra data to do classified
	 *
	 * @since 1.0.0
	 */
	public function the_content( $content ) {
		if ( ! is_singular() ) {
			return $content;
		}
		$post_type = get_post_type();
		if ( $post_type != $this->post_type_name ) {
			return $content;
		}
		$before = $after = '';
		/**
		 * before
		 */
		$before .= '<div class="classified-single classifieds-before">';
		$before .= $this->get_template( 'avatar' );
		$before .= $this->get_template( 'price' );
		$before .= $this->get_template( 'category' );
		$before .= $this->get_template( 'location' );
		$before .= '</div>';
		/**
		 * after
		 */
		$after .= '<div class="classified-single classifieds-after">';
		$after .= $this->get_template( 'contact' );
		$after .= '</div>';
		/**
		 * return content
		 */
		return $before . $content . $after;
	}

	/**
	 * Register post metaboxes
	 *
	 * @since 1.0.0
	 */
	public function register_meta_boxes( $post ) {
		add_meta_box( 'contact', __( 'Contact', 'simple-classifieds' ), array( $this, 'contact' ), $this->post_type_name );
		add_meta_box( 'item', __( 'Price', 'simple-classifieds' ), array( $this, 'item' ), $this->post_type_name );
		add_meta_box( 'location', __( 'Location', 'simple-classifieds' ), array( $this, 'location' ), $this->post_type_name );
	}

	public function contact( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	public function item( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	public function location( $post ) {
		$this->get_meta_box_content( $post, $this->fields, __FUNCTION__ );
	}

	public function save_google_map_data( $term_id, $tt_id ) {
		$location   = $this->get_location_array( array(), $term_id );
		$meta_value = $this->google_get_one( implode( ', ', $location ) );
		delete_term_meta( $term_id, 'google' );
		add_term_meta( $term_id, 'google', $meta_value, true );
	}

	private function google_get_one( $url, $encoded = false ) {
		$data = array();
		if ( ! $encoded ) {
			$url = urlencode( $url );
		}
		$args                 = array(
			'address' => $url,
			'sensor'  => 'false',
		);
		$google_maps_data_url = add_query_arg( $args, 'http://maps.google.com/maps/api/geocode/json' );
		$response             = wp_remote_get( $google_maps_data_url );
		if ( is_array( $response ) ) {
			$data = json_decode( $response['body'] );
			if ( 'OK' == $data->status && count( $data->results ) ) {
				$data = $data->results[0];
				$data = json_decode( json_encode( $data ), true );
			}
		}
		return $data;
	}

	/**
	 * Data to reveal contact informations
	 *
	 * @since 1.0.0
	 */
	public function ajax_get_contact_data() {
		$nonce   = filter_input( INPUT_GET, '_wpnonce', FILTER_SANITIZE_STRING );
		$post_id = filter_input( INPUT_GET, 'id', FILTER_VALIDATE_INT );
		if ( wp_verify_nonce( $nonce, 'classified-' . $post_id ) ) {
			$content = '';
			foreach ( $this->fields['contact'] as $key => $data ) {
				$name  = $this->options->get_option_name( 'contact_' . $key );
				$value = get_post_meta( $post_id, $name, true );
				if ( empty( $value ) ) {
					continue;
				}
				switch ( $key ) {
					case 'email':
						$href     = sprintf( 'mailto:%s?subject=%s', $value, htmlentities( get_the_title( $post_id ) ) );
						$content .= $this->helper_row( 'email', $data['label'], $value, $href );
						break;
					case 'phone':
						$href     = sprintf( 'tel:%s', $value );
						$content .= $this->helper_row( 'phone', $data['label'], $value, $href );
						break;
					case 'contact':
						$content .= $this->helper_row( 'admin-users', $data['label'], $value );
						break;
				}
			}
			echo $content;
		}
		exit;
	}

	private function helper_row( $dashicon, $label, $value, $href = null ) {
		$content  = '';
		$content  = sprintf( '<div class="classified-%s classified-grid">', esc_attr( $dashicon ) );
		$content .= sprintf(
			'<span class="classified-icon"><span class="dashicons dashicons-%s"></span> %s</span>',
			esc_attr( $dashicon ),
			esc_html( $label )
		);
		if ( empty( $href ) ) {
			$value = esc_html( $value );
		} else {
			$value = sprintf(
				'<a href="%s">%s</a>',
				esc_attr( $href ),
				esc_html( $value )
			);
		}
		$content .= sprintf( '<span>%s</span>', $value );
		$content .= '</div>';
		return $content;
	}

	public function set_expiration_date( $post_id, $post, $update ) {
		$post_type = get_post_type( $post_id );
		if ( $this->post_type_name !== $post_type ) {
			return false;
		}
		$this->save_expiration_date( $post_id );
	}

	private function save_expiration_date( $post_id ) {
		$days      = intval( $this->options->get_option( 'renewal_days' ) ) * DAY_IN_SECONDS;
		$timestamp = time() + $days;
		$result    = update_post_meta( $post_id, $this->renewal_name, $timestamp );
		if ( false === $result ) {
			add_post_meta( $post_id, $this->renewal_name, $timestamp, true );
		}
	}

	public function set_custom_columns( $columns ) {
		$status = $this->options->get_option( 'renewal_status' );
		if ( $status ) {
			$columns['renewal'] = esc_html__( 'Renewal date', 'simple-classifieds' );
		}
		return $columns;
	}

	public function get_custom_column_value( $column, $post_id ) {
		switch ( $column ) {
			case 'renewal':
				$value = get_post_meta( $post_id, $this->renewal_name, true );
				if ( empty( $value ) ) {
					$this->save_expiration_date( $post_id );
					$value = get_post_meta( $post_id, $this->renewal_name, true );
				}
				echo date_i18n( 'Y-m-d', $value );
				break;
		}
	}
}

