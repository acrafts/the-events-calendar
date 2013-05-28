<?php

if ( !defined('ABSPATH') ) 
	die('-1');

if( !class_exists('Tribe_PRO_Template_Factory') ) {
	class Tribe_PRO_Template_Factory extends Tribe_Template_Factory {

		public function __construct() {
			parent::__construct();
			add_action('tribe_events_asset_package', array(__CLASS__, 'asset_package'), 10, 2);
		}

		public static function asset_package( $name, $deps = array() ){

			$tec_pro = TribeEventsPro::instance();
			$prefix = 'tribe-events-pro';

			// setup plugin resources & 3rd party vendor urls
			$resources_url = trailingslashit( $tec_pro->pluginUrl ) . 'resources/';
			$vendor_url = trailingslashit( $tec_pro->pluginUrl ) . 'vendor/';

			switch( $name ) {
				case 'ajax-weekview' :					
					$ajax_data = array( "ajaxurl"     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'post_type' => TribeEvents::POSTTYPE );
					$path1 = Tribe_Template_Factory::getMinFile( $vendor_url . 'jquery-slimscroll/jquery.slimscroll.js', true );
					$path2 = Tribe_Template_Factory::getMinFile( $resources_url . 'tribe-events-week.js', true );
					wp_enqueue_script( 'tribe-events-pro-slimscroll', $path1, array('tribe-events-pro', 'jquery-ui-draggable'), null, true );
					wp_enqueue_script('tribe-events-pro-week', $path2, array('tribe-events-pro-slimscroll'), false, true);
					wp_localize_script( 'tribe-events-pro-week', 'TribeWeek', $ajax_data );
					break;					
				case 'ajax-photoview' :				
					$tribe_paged = ( !empty( $_REQUEST['tribe_paged'] ) ) ? $_REQUEST['tribe_paged'] : 0;
					$ajax_data = array( "ajaxurl"     => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'tribe_paged' => $tribe_paged );
					$path1 = Tribe_Template_Factory::getMinFile( $vendor_url . 'isotope/jquery.isotope.js', true );
					$path2 = Tribe_Template_Factory::getMinFile( $resources_url . 'tribe-events-photo-view.js', true );
					wp_enqueue_script( 'tribe-events-pro-isotope', $path1, array('tribe-events-pro'), null, true );
					wp_enqueue_script('tribe-events-pro-photo', $path2, array('tribe-events-pro-isotope'), null, true);
					wp_localize_script( 'tribe-events-pro-photo', 'TribePhoto', $ajax_data );
					break;					
				case 'ajax-dayview':
					$ajax_data = array( "ajaxurl"   => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ),
					                    'post_type' => TribeEvents::POSTTYPE );
					$path = Tribe_Template_Factory::getMinFile( $resources_url . 'tribe-events-ajax-day.js', true );
					wp_enqueue_script( 'tribe-events-pro-ajax-day', $path, array('tribe-events-pro'), null, true );
					wp_localize_script( 'tribe-events-pro-ajax-day', 'TribeCalendar', $ajax_data );
					break;

				case 'ajax-maps':
					$http = is_ssl() ? 'https' : 'http';

					wp_register_script( 'gmaps', $http . '://maps.google.com/maps/api/js?sensor=false', array( 'tribe-events-pro' ) );
					$path = Tribe_Template_Factory::getMinFile( $resources_url . 'tribe-events-ajax-maps.js', true );
					wp_register_script( 'tribe-events-pro-geoloc', $path, array( 'gmaps' ) );
					wp_enqueue_script( 'tribe-events-pro-geoloc' );

					$geoloc = TribeEventsGeoLoc::instance();
					$data   = array( 'ajaxurl'  => admin_url( 'admin-ajax.php', $http ),
					                 'nonce'    => wp_create_nonce( 'tribe_geosearch' ),
					                 'center'   => $geoloc->estimate_center_point(),
					                 'map_view' => ( TribeEvents::instance()->displaying == 'map' ) ? true : false );

					wp_localize_script( 'tribe-events-pro-geoloc', 'GeoLoc', $data );

					break;
			}
			parent::asset_package( $name, $deps );
		}
	}
}