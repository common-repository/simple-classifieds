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

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( class_exists( 'iworks_classifieds' ) ) {
	return;
}

require_once dirname( dirname( __FILE__ ) ) . '/iworks.php';

class iworks_classifieds extends iworks {

	private $capability;
	private $post_type_boat;
	private $post_type_person;
	private $post_type_result;

	public function __construct() {
		parent::__construct();
		$this->base       = dirname( dirname( __FILE__ ) );
		$this->dir        = basename( dirname( $this->base ) );
		$this->version    = '1.0.1';
		$this->capability = apply_filters( 'iworks_classifieds_capability', 'manage_options' );
		/**
		 * post_types
		 */
		$post_types = array( 'classified' );
		foreach ( $post_types as $post_type ) {
			include_once $this->base . '/iworks/classifieds/posttypes/' . $post_type . '.php';
			$class        = sprintf( 'iworks_classifieds_posttypes_%s', $post_type );
			$value        = sprintf( 'post_type_%s', $post_type );
			$this->$value = new $class();
		}
		/**
		 * hooks
		 */
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ), 11 );
		/**
		 * iWorks Rate integration
		 */
		add_action( 'iworks_rate_css', array( $this, 'iworks_rate_css' ) );
		/**
		 * Init
		 */
		add_action( 'init', array( $this, 'load_textdomain' ), 0 );
	}

	public function register_assets() {
		wp_register_style(
			'simple-classifieds',
			plugins_url( 'assets/css/classifieds.css', $this->base ),
			array(),
			$this->version
		);
		wp_register_script(
			'simple-classifieds',
			plugins_url( 'assets/scripts/classifieds.js', $this->base ),
			array(),
			$this->version,
			true
		);
	}

	public function enqueue_assets() {
		if ( ! is_singular( $this->post_type_classified->get_name() ) ) {
			return;
		}
		wp_enqueue_style( 'simple-classifieds' );
		wp_localize_script(
			'simple-classifieds',
			'simple_classifieds',
			apply_filters(
				'classifieds_wp_localize_script',
				array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
				)
			)
		);
		wp_enqueue_script( 'simple-classifieds' );
	}

	/**
	 * Load translations
	 *
	 * @since 1.0.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'simple-classifieds', false, $this->dir . '/languages' );
	}

	public function admin_init() {
		iworks_classifieds_options_init();
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
	}

	public function get_post_type_name( $post_type ) {
		$value = sprintf( 'post_type_%s', $post_type );
		if ( isset( $this->$value ) ) {
			return $this->$value->get_name();
		}
		return new WP_Error( 'broke', __( 'Classifieds do not have such post type!', 'simple-classifieds' ) );
	}

	/**
	 * Plugin row data
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( $this->dir . '/classifieds.php' == $file ) {
			if ( ! is_multisite() && current_user_can( $this->capability ) ) {
				$links[] = '<a href="themes.php?page=' . $this->dir . '/admin/index.php">' . __( 'Settings' ) . '</a>';
			}

			$links[] = '<a href="http://iworks.pl/donate/classifieds.php">' . __( 'Donate' ) . '</a>';

		}
		return $links;
	}


	/**
	 * Get person name
	 */
	public function get_person_name( $user_post_id ) {
		return $this->post_type_person->get_person_name_by_id( $user_post_id );
	}
	/**
	 * Get person avatar
	 */
	public function get_person_avatar( $user_post_id ) {
		return $this->post_type_person->get_person_avatar_by_id( $user_post_id );
	}

	public function get_list_by_post_type( $type ) {
		$args  = array(
			'post_type' => $this->{'post_type_' . $type}->get_name(),
			'nopaging'  => true,
		);
		$list  = array();
		$posts = get_posts( $args );
		foreach ( $posts as $post ) {
			$list[ $post->post_title ] = $post->ID;
		}
		return $list;
	}

	/**
	 * Change logo for "rate" message.
	 *
	 * @since 2.6.6
	 */
	public function iworks_rate_css() {
		$logo = plugin_dir_url( dirname( dirname( __FILE__ ) ) ) . 'assets/images/logo.svg';
		echo '<style type="text/css">';
		printf( '.iworks-notice-classifieds .iworks-notice-logo{background-image:url(%s);}', esc_url( $logo ) );
		echo '</style>';
	}
}
