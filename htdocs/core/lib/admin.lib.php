<?php
/**
 * Admin Library Functions for Demo
 * 
 * @file        admin.lib.php
 * @brief       Admin functions for standalone demo
 */

if (!function_exists('dolibarr_set_const')) {
    function dolibarr_set_const($db, $name, $value, $type = 'chaine', $visible = 0, $note = '', $entity = 1) {
        // Simulate setting configuration constant
        return 1;
    }
}

if (!function_exists('dolibarr_del_const')) {
    function dolibarr_del_const($db, $name, $entity = 1) {
        // Simulate deleting configuration constant
        return 1;
    }
}