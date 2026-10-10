<?php
/**
 * Plugin Name: Aktivitetskalender
 * Plugin URI:  https://github.com/creatonas/ms-aktivitetskalender
 * Description: Aktivitetskalender med liste, månedsoversikt og shortcodes.
 * Version:     5.3.2
 * Author:      Creato Design AS
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ms-activities
 * Domain Path: /languages
 * Update URI:  https://github.com/creatonas/ms-aktivitetskalender
 *
 * @package MS_Activities
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MS_ACTIVITIES_VERSION', '5.3.2' );
define( 'MS_ACTIVITIES_PATH', plugin_dir_path( __FILE__ ) );
define( 'MS_ACTIVITIES_URL', plugin_dir_url( __FILE__ ) );

require_once MS_ACTIVITIES_PATH . 'includes/helpers.php';
require_once MS_ACTIVITIES_PATH . 'includes/rendering.php';
require_once MS_ACTIVITIES_PATH . 'includes/ajax.php';
require_once MS_ACTIVITIES_PATH . 'includes/shortcodes.php';

/** Register the activity post type. */
function ms_activities_register_post_type() {
	$labels = array(
		'name'               => _x( 'Aktiviteter', 'post type general name', 'ms-activities' ),
		'singular_name'      => _x( 'Aktivitet', 'post type singular name', 'ms-activities' ),
		'menu_name'          => _x( 'Aktiviteter', 'admin menu', 'ms-activities' ),
		'add_new'            => __( 'Legg til ny', 'ms-activities' ),
		'add_new_item'       => __( 'Legg til ny aktivitet', 'ms-activities' ),
		'edit_item'          => __( 'Rediger aktivitet', 'ms-activities' ),
		'new_item'           => __( 'Ny aktivitet', 'ms-activities' ),
		'view_item'          => __( 'Vis aktivitet', 'ms-activities' ),
		'all_items'          => __( 'Alle aktiviteter', 'ms-activities' ),
		'search_items'       => __( 'Søk i aktiviteter', 'ms-activities' ),
		'not_found'          => __( 'Ingen aktiviteter funnet.', 'ms-activities' ),
		'not_found_in_trash' => __( 'Ingen aktiviteter funnet i papirkurven.', 'ms-activities' ),
	);

	register_post_type(
		'aktiviteter',
		array(
			'labels'       => $labels,
			'public'       => true,
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'aktiviteter' ),
			'menu_icon'    => 'dashicons-calendar-alt',
			'show_in_rest' => true,
			'supports'     => array( 'title' ),
		)
	);
}
add_action( 'init', 'ms_activities_register_post_type' );

/** Register the local ACF field group with existing keys preserved. */
function ms_activities_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_ms_activities',
			'title'    => __( 'Aktivitetsdata', 'ms-activities' ),
			'fields'   => array(
				array( 'key' => 'f1', 'label' => __( 'Dato fra', 'ms-activities' ), 'name' => 'date_from', 'type' => 'date_picker', 'return_format' => 'Y-m-d', 'required' => 1 ),
				array( 'key' => 'f2', 'label' => __( 'Dato til', 'ms-activities' ), 'name' => 'date_to', 'type' => 'date_picker', 'return_format' => 'Y-m-d' ),
				array(
					'key'           => 'f3',
					'label'         => __( 'Status', 'ms-activities' ),
					'name'          => 'status',
					'type'          => 'select',
					'choices'       => array( 'ledig' => __( 'Ledig', 'ms-activities' ), 'fullt' => __( 'Fullt', 'ms-activities' ), 'lukket' => __( 'Lukket', 'ms-activities' ) ),
					'default_value' => 'ledig',
				),
				array( 'key' => 'f4', 'label' => __( 'Lenke', 'ms-activities' ), 'name' => 'link', 'type' => 'url' ),
				array( 'key' => 'f5', 'label' => __( 'Arrangementinformasjon', 'ms-activities' ), 'name' => 'activity_info', 'type' => 'wysiwyg', 'tabs' => 'all', 'toolbar' => 'basic', 'media_upload' => 0 ),
			),
			'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'aktiviteter' ) ) ),
		)
	);
}
add_action( 'acf/init', 'ms_activities_register_fields' );

/** Warn administrators when the editing dependency is unavailable. */
function ms_activities_acf_notice() {
	if ( function_exists( 'get_field' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		esc_html__( 'Aktivitetskalender trenger Advanced Custom Fields for å redigere aktivitetsdata. Lagrede aktiviteter kan fortsatt vises.', 'ms-activities' )
	);
}
add_action( 'admin_notices', 'ms_activities_acf_notice' );

/** Register frontend assets. Shortcodes enqueue them only when rendered. */
function ms_activities_register_assets() {
	wp_register_style( 'ms-activities', MS_ACTIVITIES_URL . 'public/css/ms-activities.css', array(), MS_ACTIVITIES_VERSION );
	wp_register_script( 'ms-activities', MS_ACTIVITIES_URL . 'public/js/ms-activities.js', array(), MS_ACTIVITIES_VERSION, true );

	$config = array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'i18n'    => array(
			'close'        => __( 'Lukk', 'ms-activities' ),
			'information'  => __( 'Informasjon', 'ms-activities' ),
			'loadingError' => __( 'Kalenderen kunne ikke lastes. Prøv igjen.', 'ms-activities' ),
		),
	);

	wp_add_inline_script( 'ms-activities', 'window.msActivitiesConfig = ' . wp_json_encode( $config ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'ms_activities_register_assets' );

/** Enqueue assets from a rendered shortcode. */
function ms_activities_enqueue_assets() {
	if ( ! wp_script_is( 'ms-activities', 'registered' ) ) {
		ms_activities_register_assets();
	}

	wp_enqueue_style( 'ms-activities' );
	wp_enqueue_script( 'ms-activities' );
}

/** Refresh rewrite rules after registering the post type. */
function ms_activities_activate() {
	ms_activities_register_post_type();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ms_activities_activate' );

/** Flush plugin rewrite rules on deactivation. */
function ms_activities_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'ms_activities_deactivate' );
