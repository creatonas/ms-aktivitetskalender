<?php
/** Public calendar AJAX endpoint. @package MS_Activities */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Validate a bounded integer request value without coercing invalid input. */
function ms_activities_request_integer( $key, $min, $max ) {
	if ( ! isset( $_POST[ $key ] ) || ! is_scalar( $_POST[ $key ] ) ) {
		return null;
	}

	$value = sanitize_text_field( wp_unslash( (string) $_POST[ $key ] ) );
	if ( ! ctype_digit( $value ) ) {
		return null;
	}

	$value = (int) $value;
	return $value >= $min && $value <= $max ? $value : null;
}

/** Return a rendered month for the deliberately public calendar. */
function ms_load_calendar() {
	$month = ms_activities_request_integer( 'm', 1, 12 );
	$year  = ms_activities_request_integer( 'y', 1970, 2100 );

	if ( null === $month || null === $year ) {
		status_header( 400 );
		echo esc_html__( 'Ugyldig måned eller år.', 'ms-activities' );
		wp_die();
	}

	$instance = isset( $_POST['instance'] ) && is_scalar( $_POST['instance'] ) ? sanitize_key( wp_unslash( (string) $_POST['instance'] ) ) : '';
	if ( '' === $instance ) {
		$instance = wp_unique_id( 'ms-calendar-' );
	}

	// Dynamic values are escaped for their HTML context by the renderer.
	echo ms_render_calendar( $month, $year, $instance ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	wp_die();
}
add_action( 'wp_ajax_ms_load_calendar', 'ms_load_calendar' );
add_action( 'wp_ajax_nopriv_ms_load_calendar', 'ms_load_calendar' );
