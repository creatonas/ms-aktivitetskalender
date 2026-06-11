/**
 * Aktivitetskalender Plugin - JavaScript
 */

let msMonth = parseInt(document.documentElement.getAttribute('data-month')) || new Date().getMonth() + 1;
let msYear = parseInt(document.documentElement.getAttribute('data-year')) || new Date().getFullYear();

/**
 * Load calendar content via AJAX
 */
function msLoad() {
    const wrap = document.getElementById('ms-calendar-wrap');
    if (!wrap) return;
    
    fetch(msAjax.ajaxUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'action=ms_load_calendar&m=' + msMonth + '&y=' + msYear
    })
    .then(r => r.text())
    .then(html => {
        wrap.innerHTML = html;
    })
    .catch(err => console.error('Calendar load error:', err));
}

/**
 * Navigate to next month
 */
function msNextMonth() {
    msMonth++;
    if (msMonth > 12) {
        msMonth = 1;
        msYear++;
    }
    msLoad();
}

/**
 * Navigate to previous month
 */
function msPrevMonth() {
    msMonth--;
    if (msMonth < 1) {
        msMonth = 12;
        msYear--;
    }
    msLoad();
}

/**
 * Show table view
 */
function msShowTable() {
    const table = document.getElementById('ms-table');
    const calendar = document.getElementById('ms-calendar');
    const btnList = document.getElementById('btnList');
    const btnCal = document.getElementById('btnCal');
    
    if (table) table.style.display = 'block';
    if (calendar) calendar.style.display = 'none';
    if (btnList) btnList.classList.add('ms-active');
    if (btnCal) btnCal.classList.remove('ms-active');
}

/**
 * Show calendar view
 */
function msShowCalendar() {
    const table = document.getElementById('ms-table');
    const calendar = document.getElementById('ms-calendar');
    const btnList = document.getElementById('btnList');
    const btnCal = document.getElementById('btnCal');
    
    if (table) table.style.display = 'none';
    if (calendar) calendar.style.display = 'block';
    if (btnCal) btnCal.classList.add('ms-active');
    if (btnList) btnList.classList.remove('ms-active');
}

/**
 * Initialize - Show table by default
 */
document.addEventListener('DOMContentLoaded', function() {
    msShowTable();
});