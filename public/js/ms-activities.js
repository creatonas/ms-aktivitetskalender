( function () {
	'use strict';

	let lastModalTrigger = null;

	function translated( key, fallback ) {
		const config = window.msActivitiesConfig || {};
		return config.i18n && config.i18n[ key ] ? config.i18n[ key ] : fallback;
	}

	function ensureModal() {
		let modal = document.getElementById( 'ms-activity-modal' );
		if ( modal ) {
			return modal;
		}

		modal = document.createElement( 'div' );
		modal.className = 'ms-modal';
		modal.id = 'ms-activity-modal';
		modal.hidden = true;
		modal.setAttribute( 'aria-hidden', 'true' );

		const content = document.createElement( 'div' );
		content.className = 'ms-modal-content';
		content.setAttribute( 'role', 'dialog' );
		content.setAttribute( 'aria-modal', 'true' );
		content.setAttribute( 'aria-labelledby', 'ms-activity-modal-title' );
		content.setAttribute( 'tabindex', '-1' );

		const header = document.createElement( 'div' );
		header.className = 'ms-modal-header';
		const headingWrap = document.createElement( 'div' );
		const title = document.createElement( 'h3' );
		title.className = 'ms-modal-title';
		title.id = 'ms-activity-modal-title';
		const date = document.createElement( 'div' );
		date.className = 'ms-modal-date';
		date.id = 'ms-activity-modal-date';
		const close = document.createElement( 'button' );
		close.type = 'button';
		close.className = 'ms-modal-close';
		close.setAttribute( 'aria-label', translated( 'close', 'Lukk' ) );
		close.textContent = '×';

		const body = document.createElement( 'div' );
		body.className = 'ms-modal-body';
		body.id = 'ms-activity-modal-body';

		headingWrap.append( title, date );
		header.append( headingWrap, close );
		content.append( header, body );
		modal.append( content );
		document.body.append( modal );
		return modal;
	}

	function openModal( trigger ) {
		const source = document.getElementById( trigger.dataset.msModalTarget );
		if ( ! source ) {
			return;
		}

		const modal = ensureModal();
		modal.querySelector( '#ms-activity-modal-title' ).textContent = trigger.dataset.msModalTitle || translated( 'information', 'Informasjon' );
		modal.querySelector( '#ms-activity-modal-date' ).textContent = trigger.dataset.msModalDate || '';
		modal.querySelector( '#ms-activity-modal-body' ).innerHTML = source.innerHTML;
		modal.hidden = false;
		modal.classList.add( 'is-open' );
		modal.setAttribute( 'aria-hidden', 'false' );
		document.body.classList.add( 'ms-modal-open' );
		lastModalTrigger = trigger;
		modal.querySelector( '.ms-modal-close' ).focus();
	}

	function closeModal() {
		const modal = document.getElementById( 'ms-activity-modal' );
		if ( ! modal || modal.hidden ) {
			return;
		}

		modal.classList.remove( 'is-open' );
		modal.setAttribute( 'aria-hidden', 'true' );
		modal.hidden = true;
		modal.querySelector( '#ms-activity-modal-body' ).innerHTML = '';
		document.body.classList.remove( 'ms-modal-open' );

		if ( lastModalTrigger ) {
			lastModalTrigger.focus();
			lastModalTrigger = null;
		}
	}

	function trapModalFocus( event ) {
		const modal = document.getElementById( 'ms-activity-modal' );
		if ( ! modal || modal.hidden || 'Tab' !== event.key ) {
			return;
		}

		const focusable = Array.from( modal.querySelectorAll( 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])' ) );
		if ( ! focusable.length ) {
			event.preventDefault();
			return;
		}

		const first = focusable[ 0 ];
		const last = focusable[ focusable.length - 1 ];
		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	}

	function switchView( button ) {
		const toggle = button.closest( '[data-ms-toggle]' );
		if ( ! toggle ) {
			return;
		}

		toggle.querySelectorAll( '[data-ms-view]' ).forEach( function ( item ) {
			const active = item === button;
			item.classList.toggle( 'ms-active', active );
			item.setAttribute( 'aria-pressed', active ? 'true' : 'false' );
		} );
		toggle.querySelectorAll( '[data-ms-panel]' ).forEach( function ( panel ) {
			panel.hidden = panel.dataset.msPanel !== button.dataset.msView;
		} );
	}

	async function loadCalendar( button ) {
		const wrapper = button.closest( '.ms-calendar-wrap' );
		const config = window.msActivitiesConfig || {};
		if ( ! wrapper || ! config.ajaxUrl ) {
			return;
		}

		let month = Number.parseInt( wrapper.dataset.msMonth, 10 ) + Number.parseInt( button.dataset.msCalendarNav, 10 );
		let year = Number.parseInt( wrapper.dataset.msYear, 10 );
		if ( month > 12 ) {
			month = 1;
			year += 1;
		} else if ( month < 1 ) {
			month = 12;
			year -= 1;
		}

		const parameters = new URLSearchParams( {
			action: 'ms_load_calendar',
			m: String( month ),
			y: String( year ),
			instance: wrapper.dataset.msInstance || '',
		} );
		wrapper.setAttribute( 'aria-busy', 'true' );
		button.disabled = true;

		try {
			const response = await fetch( config.ajaxUrl, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: parameters.toString(),
			} );
			const html = await response.text();
			if ( ! response.ok ) {
				throw new Error( 'Calendar request failed.' );
			}

			wrapper.dataset.msMonth = String( month );
			wrapper.dataset.msYear = String( year );
			wrapper.innerHTML = html;
		} catch ( error ) {
			const existing = wrapper.querySelector( '.ms-calendar-error' );
			if ( ! existing ) {
				const message = document.createElement( 'p' );
				message.className = 'ms-calendar-error';
				message.setAttribute( 'role', 'alert' );
				message.textContent = translated( 'loadingError', 'Kalenderen kunne ikke lastes. Prøv igjen.' );
				wrapper.prepend( message );
			}
		} finally {
			wrapper.removeAttribute( 'aria-busy' );
		}
	}

	document.addEventListener( 'click', function ( event ) {
		const modalTrigger = event.target.closest( '[data-ms-modal-target]' );
		const viewButton = event.target.closest( '[data-ms-view]' );
		const calendarButton = event.target.closest( '[data-ms-calendar-nav]' );
		const modal = document.getElementById( 'ms-activity-modal' );

		if ( modalTrigger ) {
			openModal( modalTrigger );
		} else if ( viewButton ) {
			switchView( viewButton );
		} else if ( calendarButton ) {
			loadCalendar( calendarButton );
		} else if ( modal && ( event.target === modal || event.target.closest( '.ms-modal-close' ) ) ) {
			closeModal();
		}
	} );

	document.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key ) {
			closeModal();
		} else {
			trapModalFocus( event );
		}
	} );
}() );
