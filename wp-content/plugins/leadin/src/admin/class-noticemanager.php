<?php

namespace Leadin\admin;

use Leadin\LeadinOptions;
use Leadin\wp\User;

/**
 * Class responsible for rendering the admin notices.
 */
class NoticeManager {
	/**
	 * Class constructor, adds the necessary hooks.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, 'leadin_action_required_notice' ) );
	}

	/**
	 * Render the disconnected banner.
	 */
	private function leadin_render_disconnected_banner() {
		?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<img src="<?php echo esc_attr( LEADIN_PATH . '/assets/images/sprocket.svg' ); ?>" height="16" style="margin-bottom: -3px" />
					&nbsp;
					<?php
						echo sprintf(
							esc_html( __( 'The HubSpot plugin isn’t connected right now. To use HubSpot tools on your WordPress site, %1$sconnect the plugin now%2$s.', 'leadin' ) ),
							'<a href="admin.php?page=leadin&bannerClick=true">',
							'</a>'
						); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
				</p>
			</div>
		<?php
	}

	/**
	 * Render feedback tool notice
	 *
	 * @param String $user_name Either user's first name or user's nice_name.
	 */
	private function leadin_feedback_notice( $user_name ) {
		?>
			<div id="leadin_feedback_notice" class="notice notice-info is-dismissible leadin-notice">
				<div class="leadin-notice__content">
					<p>
					<?php
						echo esc_html ( sprintf( __( 'Hey %1$s, you have been using the plugin for some time now. If you\'re enjoying our free plugin, we\'d really appreciate it if you could leave us a quick review on WordPress.', 'leadin' ), $user_name ) );
					?>
					</p>
					<div class="leadin-notice__options">
						<a id="leadin_feedback_link" target="_blank" href="https://wordpress.org/support/plugin/leadin/reviews/#new-post">
							<?php echo esc_html( __( 'Yes! Let\'s go', 'leadin' ) ); ?>
						</a>
						<a id="leadin_dismiss_feedback_link" href="#">
							<?php echo esc_html( __( 'Maybe later', 'leadin' ) ); ?>
						</a>
						<a id="leadin_remove_feedback_link" href="#">
							<?php echo esc_html( __( 'I already did', 'leadin' ) ); ?>
						</a>
					</div>
					<div class="leadin-notice__footer">
						<img class="leadin-notice__footer-image" src="<?php echo esc_attr( LEADIN_PATH . '/assets/images/davidlykhim-headshot-circle.png' ); ?>" height="42" width="42">
						<div class="leadin-notice__footer-text">
							<span><?php echo esc_html( __( 'Thanks', 'leadin' ) ); ?></span>
							<span>David Ly Khim</span>
							<span><?php echo esc_html( __( 'HubSpot WordPress Team', 'leadin' ) ); ?></span>
						</div>
					</div>
				</div>
				<button type="button" class="notice-dismiss"></button>
			</div>
		<?php
	}

	/**
	 * Find what notice (if any) needs to be rendered
	 */
	public function leadin_action_required_notice() {
		$current_screen = get_current_screen();
		$portal_id      = LeadinOptions::get_portal_id();

		if ( 'leadin' !== $current_screen->parent_base ) {
			if ( empty( $portal_id ) && User::is_admin() ) {
				$this->leadin_render_disconnected_banner();
			}
		}
		if ( ! empty( $portal_id ) && 'dashboard' === $current_screen->id && strtotime( '-2 weeks' ) >= LeadinOptions::get_review_option() ) {
			$wp_user    = wp_get_current_user();
			$wp_user_id = $wp_user->ID;
			$user_name  = $wp_user->first_name ? $wp_user->first_name : $wp_user->user_nicename;

			if ( ! get_user_meta( $wp_user_id, 'leadin_hide_feedback_notice', true ) ) {
				$this->leadin_feedback_notice( $user_name );
			}
		}
	}
}
