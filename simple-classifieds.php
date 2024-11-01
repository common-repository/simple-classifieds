<?php
/*
Plugin Name: Simple Classifieds
Text Domain: simple-classifieds
Plugin URI: http://iworks.pl/classifieds/
Description: Realy simple classifieds plugin.
Version: 1.0.1
Author: Marcin Pietrzak
Author URI: http://iworks.pl/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

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

/**
 * static options
 */
define( 'IWORKS_CLASSIFIEDS_VERSION', '1.0.1' );
define( 'IWORKS_CLASSIFIEDS_PREFIX', 'iworks_classifieds_' );
$base   = dirname( __FILE__ );
$vendor = $base . '/vendor';

/**
 * require: iworks_classifieds Class
 */
if ( ! class_exists( 'iworks_classifieds' ) ) {
	require_once $vendor . '/iworks/classifieds.php';
}
/**
 * configuration
 */
require_once $base . '/etc/options.php';
/**
 * require: IworksOptions Class
 */
if ( ! class_exists( 'iworks_options' ) ) {
	require_once $vendor . '/iworks/options/options.php';
}

/**
 * load options
 */
$iworks_classifieds_options = new iworks_options();
$iworks_classifieds_options->set_option_function_name( 'iworks_classifieds_options' );
$iworks_classifieds_options->set_option_prefix( IWORKS_CLASSIFIEDS_PREFIX );

function iworks_classifieds_options_init() {
	global $iworks_classifieds_options;
	$iworks_classifieds_options->options_init();
}

function iworks_classifieds_activate() {
	$iworks_classifieds_options = new iworks_options();
	$iworks_classifieds_options->set_option_function_name( 'iworks_classifieds_options' );
	$iworks_classifieds_options->set_option_prefix( IWORKS_CLASSIFIEDS_PREFIX );
	$iworks_classifieds_options->activate();
	/**
	 * install tables
	 */
	$iworks_classifieds = new iworks_classifieds();
}

function iworks_classifieds_deactivate() {
	global $iworks_classifieds_options;
	$iworks_classifieds_options->deactivate();
}

$iworks_classifieds = new iworks_classifieds();

/**
 * install & uninstall
 */
register_activation_hook( __FILE__, 'iworks_classifieds_activate' );
register_deactivation_hook( __FILE__, 'iworks_classifieds_deactivate' );
/**
 * Ask for vote
 */
require_once dirname( __FILE__ ) . '/vendor/iworks/rate/rate.php';
do_action(
	'iworks-register-plugin',
	plugin_basename( __FILE__ ),
	__( 'Simple Classifieds', 'simple-classifieds' ),
	'simple-classifieds'
);
