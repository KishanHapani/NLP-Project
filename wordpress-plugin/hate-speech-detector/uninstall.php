<?php
/**
 * Runs when the plugin is uninstalled from WordPress.
 *
 * Removes all plugin options from the database.
 *
 * @package HateSpeechDetector
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

delete_option( 'hsd_options' );
