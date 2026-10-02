<?php
/** Remove only this plugin's settings for the current site. */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
delete_option( 'tsar_hotel_updates_settings' );
