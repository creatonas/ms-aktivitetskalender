<?php
/** Public shortcodes. @package MS_Activities */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Render the activity list shortcode. */
function ms_activities_shortcode() {
	ms_activities_enqueue_assets();
	return ms_render_activity_list( wp_unique_id( 'ms-list-' ) );
}
add_shortcode( 'ms_activities', 'ms_activities_shortcode' );

/** Render the calendar shortcode. */
function ms_calendar_shortcode() {
	ms_activities_enqueue_assets();
	$month    = (int) current_time( 'n' );
	$year     = (int) current_time( 'Y' );
	$instance = wp_unique_id( 'ms-calendar-' );

	return sprintf(
		'<div class="ms-activities ms-calendar-wrap" data-ms-instance="%1$s" data-ms-month="%2$d" data-ms-year="%3$d" aria-live="polite">%4$s</div>',
		esc_attr( $instance ), $month, $year, ms_render_calendar( $month, $year, $instance )
	);
}
add_shortcode( 'ms_calendar', 'ms_calendar_shortcode' );

/** Render the combined list/calendar switcher. */
function ms_activities_toggle_shortcode() {
	ms_activities_enqueue_assets();
	$instance          = wp_unique_id( 'ms-toggle-' );
	$calendar_instance = $instance . '-calendar';
	$month             = (int) current_time( 'n' );
	$year              = (int) current_time( 'Y' );

	return sprintf(
		'<div class="ms-activities ms-toggle" data-ms-toggle><div class="ms-toggle-buttons grve-btn-wrapper" role="group" aria-label="%1$s"><button type="button" data-ms-view="list" class="grve-btn grve-round grve-bg-primary-2 grve-text-white grve-btn-medium ms-active" aria-pressed="true">%2$s</button><button type="button" data-ms-view="calendar" class="grve-btn grve-round grve-bg-primary-2 grve-text-white grve-btn-medium" aria-pressed="false">%3$s</button></div><div data-ms-panel="list">%4$s</div><div data-ms-panel="calendar" hidden><div class="ms-calendar-wrap" data-ms-instance="%5$s" data-ms-month="%6$d" data-ms-year="%7$d" aria-live="polite">%8$s</div></div></div>',
		esc_attr__( 'Velg kalendervisning', 'ms-activities' ), esc_html__( 'Liste', 'ms-activities' ), esc_html__( 'Måned', 'ms-activities' ),
		ms_render_activity_list( $instance . '-list' ), esc_attr( $calendar_instance ), $month, $year,
		ms_render_calendar( $month, $year, $calendar_instance )
	);
}
add_shortcode( 'ms_activities_toggle', 'ms_activities_toggle_shortcode' );
