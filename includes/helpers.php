<?php
/** Shared activity helpers. @package MS_Activities */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Read an ACF field with a post-meta fallback. */
function ms_activity_get_field( $field_name, $post_id ) {
	if ( function_exists( 'get_field' ) ) {
		return get_field( $field_name, $post_id );
	}

	return get_post_meta( $post_id, $field_name, true );
}

/** Normalize current and legacy activity statuses. */
function ms_status( $status ) {
	$status = sanitize_key( (string) $status );
	if ( 'booket' === $status ) {
		$status = 'fullt';
	}

	return in_array( $status, array( 'ledig', 'fullt', 'lukket' ), true ) ? $status : 'ledig';
}

/** Return the translated label for a normalized status. */
function ms_activity_status_label( $status ) {
	$labels = array(
		'ledig'  => __( 'Ledig', 'ms-activities' ),
		'fullt'  => __( 'Fullt', 'ms-activities' ),
		'lukket' => __( 'Lukket', 'ms-activities' ),
	);

	return isset( $labels[ $status ] ) ? $labels[ $status ] : $labels['ledig'];
}

/** Parse ACF's formatted or raw date value. */
function ms_activity_parse_date( $date ) {
	if ( ! is_string( $date ) || '' === $date ) {
		return null;
	}

	$format = preg_match( '/^\d{8}$/', $date ) ? '!Ymd' : '!Y-m-d';
	$parsed = DateTimeImmutable::createFromFormat( $format, $date, new DateTimeZone( 'UTC' ) );
	$errors = DateTimeImmutable::getLastErrors();

	if ( false === $parsed || ( is_array( $errors ) && ( $errors['warning_count'] || $errors['error_count'] ) ) ) {
		return null;
	}

	return $parsed;
}

/** Format a date-only value without applying a site timezone shift. */
function ms_activity_format_date( $format, $date ) {
	if ( function_exists( 'wp_date' ) ) {
		return wp_date( $format, $date->getTimestamp(), new DateTimeZone( 'UTC' ) );
	}

	return date_i18n( $format, $date->getTimestamp(), true );
}

/** Format an activity date or date range. */
function ms_date( $from, $to ) {
	$start = ms_activity_parse_date( $from );
	$end   = ms_activity_parse_date( $to );

	if ( ! $start ) {
		return '';
	}
	if ( ! $end || $end < $start ) {
		return ms_activity_format_date( 'j F', $start );
	}
	if ( $start->format( 'Y-m' ) === $end->format( 'Y-m' ) ) {
		return sprintf( __( '%1$s–%2$s', 'ms-activities' ), ms_activity_format_date( 'j', $start ), ms_activity_format_date( 'j F', $end ) );
	}

	return sprintf( __( '%1$s–%2$s', 'ms-activities' ), ms_activity_format_date( 'j F', $start ), ms_activity_format_date( 'j F', $end ) );
}

/** Convert a post into normalized event data. */
function ms_activity_get_event( $post_id ) {
	$from = (string) ms_activity_get_field( 'date_from', $post_id );
	if ( ! ms_activity_parse_date( $from ) ) {
		return null;
	}

	$to = (string) ms_activity_get_field( 'date_to', $post_id );
	return array(
		'from'   => $from,
		'to'     => $to,
		'status' => ms_status( ms_activity_get_field( 'status', $post_id ) ),
		'title'  => get_the_title( $post_id ),
		'link'   => (string) ms_activity_get_field( 'link', $post_id ),
		'info'   => (string) ms_activity_get_field( 'activity_info', $post_id ),
		'date'   => ms_date( $from, $to ),
	);
}
