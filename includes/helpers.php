<?php
/**
 * Helper Functions
 */

if (!defined('ABSPATH')) exit;

/**
 * Convert status from field to display format
 */
function ms_status($s) {
    return $s == 'booket' ? 'fullt' : $s;
}

/**
 * Format date range for display
 */
function ms_date($from, $to) {
    $f = strtotime($from);
    $t = $to ? strtotime($to) : null;
    
    if ($t && date('Y-m', $f) == date('Y-m', $t)) {
        return date_i18n('j', $f) . '-' . date_i18n('j F', $t);
    }
    
    return date_i18n('j F', $f);
}