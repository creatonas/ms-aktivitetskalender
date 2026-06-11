<?php
/**
 * Plugin Name: Aktivitetskalender
 * Description: v4.7.1 - Date on separate line fix
 * Version: 4.7.1
 * Author: Creato Design AS
 */

if (!defined('ABSPATH')) exit;

/* STYLES */
add_action('wp_head', function(){
echo '<style>

/* DATE ON OWN LINE */
.ms-day strong{
    display:block;
    margin-bottom:6px;
    font-size:14px;
}

</style>';
});
