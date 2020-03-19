<?php

namespace Leadin\admin\api;

/**
 * Remove Review Timer Api, used to flag the user so the feedback banner does not show again
 */
class RemoveReviewApi {
	/**
	 * Remove Review Timer API constructor. Adds the ajax hooks.
	 */
	public function __construct() {
		add_action( 'wp_ajax_leadin_remove_review_ajax', array( $this, 'run' ) );
	}

	/**
	 *  Remove Review Timer Api runner. Add a flag to the user metadata so the flag banner does not show again
	 */
	public function run() {
		$wp_user    = wp_get_current_user();
		$wp_user_id = $wp_user->ID;
		add_user_meta( $wp_user_id, 'leadin_hide_feedback_notice', true );
	}
}
