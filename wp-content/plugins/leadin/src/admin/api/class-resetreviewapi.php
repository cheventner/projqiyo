<?php

namespace Leadin\admin\api;

use Leadin\LeadinOptions;

/**
 * Reset Review Timer Api, used to reset the timer since the last connection to show the
 */
class ResetReviewApi {
	/**
	 * Reset Review Timer API constructor. Adds the ajax hooks.
	 */
	public function __construct() {
		add_action( 'wp_ajax_leadin_reset_review_ajax', array( $this, 'run' ) );
	}

	/**
	 *  Reset Review Timer Api runner. Resets the timer on the options to show the plugin feedback notice
	 */
	public function run() {
		LeadinOptions::reset_review_option();
	}
}
