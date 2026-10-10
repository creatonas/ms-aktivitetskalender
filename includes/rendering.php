<?php
/** Activity queries and public rendering. @package MS_Activities */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Return collected hidden modal sources. Preserved for compatibility. */
function ms_activity_modal_output( $modal_contents ) {
	return empty( $modal_contents ) ? '' : implode( '', $modal_contents );
}

/** Build a title, link, or modal trigger for an event. */
function ms_activity_event_title( $event, &$modal_contents, $info_id ) {
	$title = esc_html( $event['title'] );

	if ( '' !== $event['info'] ) {
		$action = '';
		if ( '' !== $event['link'] ) {
			$action = sprintf(
				'<p class="ms-modal-actions"><a class="ms-modal-action" href="%1$s">%2$s <span aria-hidden="true">&rarr;</span></a></p>',
				esc_url( $event['link'] ),
				esc_html__( 'Gå til arrangement', 'ms-activities' )
			);
		}

		$modal_contents[ $info_id ] = sprintf(
			'<div id="%1$s" class="ms-hidden-info" hidden>%2$s%3$s</div>',
			esc_attr( $info_id ),
			wp_kses_post( $event['info'] ),
			$action
		);

		return sprintf(
			'<button type="button" class="ms-modal-link" data-ms-modal-target="%1$s" data-ms-modal-title="%2$s" data-ms-modal-date="%3$s"><span class="ms-modal-link-label">%4$s</span><span class="ms-info-icon" aria-hidden="true">i</span></button>',
			esc_attr( $info_id ),
			esc_attr( $event['title'] ),
			esc_attr( $event['date'] ),
			$title
		);
	}

	if ( '' !== $event['link'] ) {
		return sprintf(
			'<a href="%1$s">%2$s <span class="ms-link-icon" aria-hidden="true">&nearr;</span></a>',
			esc_url( $event['link'] ),
			$title
		);
	}

	return $title;
}

/** Query events that can overlap a calendar month. */
function ms_activity_calendar_query( $first, $last ) {
	$first_raw = $first->format( 'Ymd' );
	$last_raw  = $last->format( 'Ymd' );

	return new WP_Query(
		array(
			'post_type'      => 'aktiviteter',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'no_found_rows'  => true,
			'meta_key'       => 'date_from',
			'orderby'        => 'meta_value_num',
			'order'          => 'ASC',
			'meta_query'     => array(
				'relation' => 'AND',
				array( 'key' => 'date_from', 'value' => $last_raw, 'compare' => '<=', 'type' => 'NUMERIC' ),
				array(
					'relation' => 'OR',
					array( 'key' => 'date_to', 'value' => $first_raw, 'compare' => '>=', 'type' => 'NUMERIC' ),
					array(
						'relation' => 'AND',
						array( 'key' => 'date_from', 'value' => $first_raw, 'compare' => '>=', 'type' => 'NUMERIC' ),
						array(
							'relation' => 'OR',
							array( 'key' => 'date_to', 'compare' => 'NOT EXISTS' ),
							array( 'key' => 'date_to', 'value' => '', 'compare' => '=' ),
						),
					),
				),
			),
		)
	);
}

/** Render a month calendar. Existing first two arguments remain compatible. */
function ms_render_calendar( $month, $year, $instance = '' ) {
	$month = (int) $month;
	$year  = (int) $year;
	if ( $month < 1 || $month > 12 || $year < 1970 || $year > 2100 ) {
		return '<p class="ms-calendar-error">' . esc_html__( 'Ugyldig måned eller år.', 'ms-activities' ) . '</p>';
	}
	if ( '' === $instance ) {
		$instance = wp_unique_id( 'ms-calendar-' );
	}

	$first = new DateTimeImmutable( sprintf( '%04d-%02d-01', $year, $month ), new DateTimeZone( 'UTC' ) );
	$last  = $first->modify( 'last day of this month' );
	$query = ms_activity_calendar_query( $first, $last );
	$events = array();
	$modal_contents = array();

	foreach ( $query->posts as $post ) {
		$event = ms_activity_get_event( $post->ID );
		if ( ! $event ) {
			continue;
		}

		$start = ms_activity_parse_date( $event['from'] );
		$end   = ms_activity_parse_date( $event['to'] );
		if ( ! $end || $end < $start ) {
			$end = $start;
		}
		if ( $end < $first || $start > $last ) {
			continue;
		}

		$cursor = $start < $first ? $first : $start;
		$end    = $end > $last ? $last : $end;
		while ( $cursor <= $end ) {
			$events[ $cursor->format( 'Y-m-d' ) ][] = array( 'post_id' => $post->ID, 'event' => $event );
			$cursor = $cursor->modify( '+1 day' );
		}
	}
	wp_reset_postdata();

	$weekday_labels = array(
		__( 'Man', 'ms-activities' ), __( 'Tir', 'ms-activities' ), __( 'Ons', 'ms-activities' ),
		__( 'Tor', 'ms-activities' ), __( 'Fre', 'ms-activities' ), __( 'Lør', 'ms-activities' ), __( 'Søn', 'ms-activities' ),
	);
	$weekdays = '';
	foreach ( $weekday_labels as $weekday ) {
		$weekdays .= '<span role="columnheader">' . esc_html( $weekday ) . '</span>';
	}

	$days = '';
	for ( $blank = 1; $blank < (int) $first->format( 'N' ); $blank++ ) {
		$days .= '<div class="ms-day ms-day--empty" role="gridcell" aria-hidden="true"></div>';
	}

	$today = current_time( 'Y-m-d' );
	for ( $day = 1; $day <= (int) $last->format( 'j' ); $day++ ) {
		$date    = sprintf( '%04d-%02d-%02d', $year, $month, $day );
		$classes = 'ms-day' . ( $date === $today ? ' ms-day--today' : '' );
		$items   = '';

		if ( isset( $events[ $date ] ) ) {
			foreach ( $events[ $date ] as $item ) {
				$event   = $item['event'];
				$info_id = sanitize_html_class( $instance . '-activity-' . $item['post_id'] );
				$title   = ms_activity_event_title( $event, $modal_contents, $info_id );
				$items  .= sprintf(
					'<span class="ms-status ms-status--%1$s"><span class="ms-dot ms-dot--%1$s" aria-hidden="true"></span>%2$s</span>',
					esc_attr( $event['status'] ),
					$title
				);
			}
		}

		$days .= sprintf(
			'<div class="%1$s" role="gridcell" aria-label="%2$s"><strong>%3$d</strong>%4$s</div>',
			esc_attr( $classes ),
			esc_attr( ms_activity_format_date( 'j F Y', new DateTimeImmutable( $date, new DateTimeZone( 'UTC' ) ) ) ),
			$day,
			'' === $items ? '' : '<div class="ms-day-events">' . $items . '</div>'
		);
	}

	return sprintf(
		'<div class="ms-calendar-nav"><div class="ms-nav-left"><button type="button" data-ms-calendar-nav="-1" class="grve-btn grve-round grve-bg-primary-2 grve-text-white grve-btn-medium" aria-label="%1$s">&larr;</button></div><div class="ms-nav-center"><time datetime="%2$s">%3$s</time></div><div class="ms-nav-right"><button type="button" data-ms-calendar-nav="1" class="grve-btn grve-round grve-bg-primary-2 grve-text-white grve-btn-medium" aria-label="%4$s">&rarr;</button></div></div><div class="ms-calendar-scroll"><div class="ms-calendar-head" role="row">%5$s</div><div class="ms-calendar" role="grid" aria-label="%6$s">%7$s</div></div>%8$s',
		esc_attr__( 'Forrige måned', 'ms-activities' ), esc_attr( $first->format( 'Y-m' ) ),
		esc_html( ms_activity_format_date( 'F Y', $first ) ), esc_attr__( 'Neste måned', 'ms-activities' ),
		$weekdays, esc_attr( ms_activity_format_date( 'F Y', $first ) ), $days,
		ms_activity_modal_output( $modal_contents )
	);
}

/** Render the upcoming activity table. */
function ms_render_activity_list( $instance = '' ) {
	if ( '' === $instance ) {
		$instance = wp_unique_id( 'ms-list-' );
	}
	$today_raw = current_time( 'Ymd' );
	$query = new WP_Query(
		array(
			'post_type' => 'aktiviteter', 'post_status' => 'publish', 'posts_per_page' => -1, 'no_found_rows' => true,
			'meta_key' => 'date_from', 'orderby' => 'meta_value_num', 'order' => 'ASC',
			'meta_query' => array(
				'relation' => 'OR',
				array( 'key' => 'date_from', 'value' => $today_raw, 'compare' => '>=', 'type' => 'NUMERIC' ),
				array( 'key' => 'date_to', 'value' => $today_raw, 'compare' => '>=', 'type' => 'NUMERIC' ),
			),
		)
	);

	$rows = '';
	$modal_contents = array();
	foreach ( $query->posts as $post ) {
		$event = ms_activity_get_event( $post->ID );
		if ( ! $event ) {
			continue;
		}
		$info_id = sanitize_html_class( $instance . '-activity-' . $post->ID );
		$title   = ms_activity_event_title( $event, $modal_contents, $info_id );
		$rows   .= sprintf(
			'<tr><td data-label="%1$s"><span class="ms-date-icon grve-text-primary-2" aria-hidden="true"><span class="grve-icon et-icon-calendar"></span></span>%2$s</td><td data-label="%3$s">%4$s</td><td data-label="%5$s"><span class="ms-status ms-status--%6$s"><span class="ms-dot ms-dot--%6$s" aria-hidden="true"></span>%7$s</span></td></tr>',
			esc_attr__( 'Dato', 'ms-activities' ), esc_html( $event['date'] ), esc_attr__( 'Aktivitet', 'ms-activities' ), $title,
			esc_attr__( 'Status', 'ms-activities' ), esc_attr( $event['status'] ), esc_html( ms_activity_status_label( $event['status'] ) )
		);
	}
	wp_reset_postdata();

	if ( '' === $rows ) {
		$rows = '<tr><td colspan="3">' . esc_html__( 'Ingen kommende aktiviteter.', 'ms-activities' ) . '</td></tr>';
	}

	return sprintf(
		'<div class="ms-activities ms-list"><table class="ms-table"><thead><tr><th>%1$s</th><th>%2$s</th><th>%3$s</th></tr></thead><tbody>%4$s</tbody></table>%5$s</div>',
		esc_html( current_time( 'Y' ) ), esc_html__( 'Aktivitet', 'ms-activities' ), esc_html__( 'Status', 'ms-activities' ),
		$rows, ms_activity_modal_output( $modal_contents )
	);
}
