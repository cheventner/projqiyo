<?php

namespace Leadin\admin;

use Leadin\LeadinOptions;
use Leadin\wp\User;
use Leadin\utils\Versions;

/**
 * Class containing all the functions to generate links to HubSpot.
 */
class Links {
	/**
	 * Get a map of <admin page, url>
	 */
	public static function get_routes_mapping() {
		$portal_id      = get_option( 'leadin_portalId' );
		$dashboard_page = "/reports-dashboard/$portal_id/marketing";
		$starting_page  = "/wordpress-plugin-ui/$portal_id/onboarding/start";
		$landing_page   = ( LeadinOptions::get_experiment_option() === LeadinOptions::WP012_CONTROL ? $dashboard_page : $starting_page );

		return array(
			'leadin'               => $landing_page,
			'leadin_dashboard'     => $dashboard_page,
			'leadin_contacts'      => "/contacts/$portal_id/contacts",
			'leadin_lists'         => "/contacts/$portal_id/lists",
			'leadin_forms'         => "/forms/$portal_id",
			'leadin_settings'      => "/wordpress-plugin-ui/$portal_id/settings",
			'leadin_starting_page' => $starting_page,
			'leadin_pricing'       => "/pricing/$portal_id/marketing",
		);
	}

	/**
	 * Get page name from the current page id.
	 * E.g. "hubspot_page_leadin_forms" => "forms"
	 */
	private static function get_page_id() {
		$screen_id = get_current_screen()->id;
		return preg_replace( '/^(hubspot_page_|toplevel_page_)/', '', $screen_id );
	}

	/**
	 * Get the parsed `leadin_route` from the query string.
	 */
	private static function get_iframe_route() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$iframe_route = isset( $_GET['leadin_route'] ) ? wp_unslash( $_GET['leadin_route'] ) : array();
		return is_array( $iframe_route ) ? $iframe_route : array();
	}

	/**
	 * Return query string from object.
	 *
	 * @param array $arr query parameters to stringify.
	 */
	private static function http_build_query( $arr ) {
		return http_build_query( $arr, null, ini_get( 'arg_separator.output' ), PHP_QUERY_RFC3986 );
	}

	/**
	 * Validate static version.
	 *
	 * @param string $version version of the static bundle.
	 */
	private static function is_static_version_valid( $version ) {
		preg_match( '/static-\d+\.\d+/', $version, $match );
		return ! empty( $match );
	}

	/**
	 * Return utm_campaign to add to the signup link.
	 */
	private static function get_utm_campaign() {
		$wpe_template = LeadinOptions::get_wpe_template();
		if ( 'hubspot' === $wpe_template ) {
			return 'wp-engine-site-template';
		}
	}

	/**
	 * Return WordPress ajax url.
	 */
	public static function get_ajax_url() {
		return admin_url( 'admin-ajax.php' );
	}

	/**
	 * Return array of query parameters to add to the iframe src.
	 */
	public static function get_query_params() {
		$query_param_array = array(
			'l'       => get_locale(),
			'php'     => Versions::get_php_version(),
			'v'       => LEADIN_PLUGIN_VERSION,
			'wp'      => Versions::get_wp_version(),
			'theme'   => get_option( 'stylesheet' ),
			'admin'   => User::is_admin(),
			'ajaxUrl' => self::get_ajax_url(),
			'domain'  => get_site_url(),
			'nonce'   => wp_create_nonce( 'hubspot-ajax' ),
		);

		if ( self::is_static_version_valid( LEADIN_STATIC_BUNDLE_VERSION ) ) {
			$query_param_array['s'] = LEADIN_STATIC_BUNDLE_VERSION;
		}

		return self::http_build_query( $query_param_array );
	}

	/**
	 * Return the signup url based on the site options.
	 */
	public static function get_signup_url() {
		// Get attribution string.
		$acquisition_option = LeadinOptions::get_acquisition_attribution();
		parse_str( $acquisition_option, $signup_params );
		$signup_params['enableCollectedForms'] = 'true';
		$redirect_page                         = get_option( 'leadin_portalId' ) ? 'leadin_settings' : 'leadin';
		$signup_params['wp_redirect_url']      = admin_url( "admin.php?page=$redirect_page" );

		// Get leadin query.
		$leadin_query = self::get_query_params();
		parse_str( $leadin_query, $leadin_params );

		$signup_params = array_merge( $signup_params, $leadin_params );

		// Add signup pre-fill info.
		$wp_user                    = wp_get_current_user();
		$signup_params['firstName'] = $wp_user->user_firstname;
		$signup_params['lastName']  = $wp_user->user_lastname;
		$signup_params['email']     = $wp_user->user_email;
		$signup_params['company']   = get_bloginfo( 'name' );
		$signup_params['domain']    = parse_url( get_site_url(), PHP_URL_HOST );
		// TODO: Only temporary fix. We need a proper mechanism to specify to signup the experiments should be disabled on the tests.
		$signup_params['exp-disabled'] = 'true';
		$signup_params['show_nav']     = 'true';
		$signup_params['wp_user']      = $wp_user->first_name ? $wp_user->first_name : $wp_user->user_nicename;
		$signup_params['wp_gravatar']  = get_avatar_url( $wp_user->ID );

		$affiliate_code = LeadinOptions::get_affiliate_code();
		$signup_url     = LEADIN_SIGNUP_BASE_URL . '/signup/wordpress?';

		if ( LeadinOptions::get_experiment_option() ) {
			$signup_params['wpExperimentGroup'] = LeadinOptions::get_experiment_option();
		}

		if ( $affiliate_code ) {
			$signup_url     .= self::http_build_query( $signup_params );
			$destination_url = rawurlencode( $signup_url );
			return "https://mbsy.co/$affiliate_code?url=$destination_url";
		}

		$signup_params['utm_source'] = 'wordpress-plugin';
		$signup_params['utm_medium'] = 'marketplaces';

		$utm_campaign = self::get_utm_campaign();
		if ( ! empty( $utm_campaign ) ) {
			$signup_params['utm_campaign'] = $utm_campaign;
		}

		return $signup_url . self::http_build_query( $signup_params );
	}

	/**
	 * Get background iframe src.
	 */
	public static function get_background_iframe_src() {
		$portal_id     = LeadinOptions::get_portal_id();
		$portal_id_url = '';

		if ( ! empty( $portal_id ) ) {
			$portal_id_url = "/$portal_id";
		}

		$query  = '';
		$screen = get_current_screen();

		return LEADIN_BASE_URL . "/wordpress-plugin-ui$portal_id_url/background?$query" . self::get_query_params();
	}

	/**
	 * Return login link to redirect to when the user isn't authenticated in HubSpot
	 */
	public static function get_login_url() {
		$portal_id = LeadinOptions::get_portal_id();
		return LEADIN_BASE_URL . "/wordpress-plugin-ui/$portal_id/login?" . self::get_query_params();
	}

	/**
	 * Returns the url for the connection page
	 */
	private static function get_connection_src() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
		$portal_id = filter_var( wp_unslash( $_GET['leadin_connect'] ), FILTER_VALIDATE_INT );
		return LEADIN_BASE_URL . "/wordpress-plugin-ui/onboarding/connect?portalId=$portal_id&" . self::get_query_params();
	}

	/**
	 * Returns the right iframe src.
	 * The src will be `/wordpress-plugin-ui/{portalId}/{path}/{routes}`,
	 * where path is the content after `leadin_` in ?page=leadin_path
	 * and routes is the content of the `leadin_route` query parameter
	 * eg: ?page=leadin_forms&leadin_route[0]=foo&leadin_route[1]=bar
	 * will point to /wordpress-plugin-ui/{portalId}/forms/foo/bar
	 */
	public static function get_iframe_src() {
		$screen = get_current_screen();
		if ( 'dashboard' === $screen->id || 'post' === $screen->id || 'page' === $screen->id ) {
			return self::get_background_iframe_src();
		}

		$leadin_onboarding = 'leadin_onboarding';
		$search = '';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['leadin_connect'] ) ) {
			$extra = '';
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_GET['is_new_portal'] ) ) {
				$extra = '&isNewPortal=true';
			}
			return self::get_connection_src() . $extra;
		}

		if ( get_transient( $leadin_onboarding ) ) {
			delete_transient( $leadin_onboarding );
			$search = '&justConnected=true';
		}

		if ( LeadinOptions::get_experiment_option() ) {
			$search .= '&wpExperimentGroup=' . LeadinOptions::get_experiment_option();
		}

		if ( empty( LeadinOptions::get_portal_id() ) ) {
			set_transient( $leadin_onboarding, 'true' );
			$route = '/wordpress-plugin-ui/intro';
		} else {
			$page_id = self::get_page_id();
			$routes  = self::get_routes_mapping();

			if ( isset( $routes[ $page_id ] ) ) {
				$route = $routes[ $page_id ];
			} else {
				$route = '';
			}
		}

		$sub_routes = join( '/', self::get_iframe_route() );
		$sub_routes = empty( $sub_routes ) ? $sub_routes : "/$sub_routes";
		// Query string separator "?" may have been added to the URL already.
		$add_separator = strpos( $sub_routes, '?' ) ? '&' : '?';

		return LEADIN_BASE_URL . "$route$sub_routes" . $add_separator . self::get_query_params() . $search;
	}
}
