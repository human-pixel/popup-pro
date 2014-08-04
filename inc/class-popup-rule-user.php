<?php
/**
 * Core rule: Login / No Login / No Comment / No ProSite
 *
 * NOTE: DON'T RENAME THIS FILE!!
 * This filename is saved as metadata with each popup that uses these rules.
 * Renaming the file will DISABLE the rules, which is very bad!
 *
 * @since  4.6
 */
class IncPopupRule_User extends IncPopupRule {

	/**
	 * Initialize the rule object.
	 *
	 * @since  4.6
	 */
	protected function init() {
		$this->filename = basename( __FILE__ );

		// 'login' rule.
		$this->add_rule(
			'login',
			__( 'Visitor is logged in', PO_LANG ),
			__( 'Shows the Pop Up if the user is logged in to your site.', PO_LANG ),
			'no_login',
			1
		);

		// 'no_login' rule.
		$this->add_rule(
			'no_login',
			__( 'Visitor is not logged in', PO_LANG ),
			__( 'Shows the Pop Up if the user is not logged in to your site.', PO_LANG ),
			'login',
			1
		);

		// 'comment' rule.
		$this->add_rule(
			'comment',
			__( 'Visitor has commented before', PO_LANG ),
			__(
				'Shows the Pop Up if the user has already left a comment. ' .
				'You may want to combine this condition with either "Visitor ' .
				'is logged in" or "Visitor is not logged in".', PO_LANG
			),
			'no_comment',
			20
		);

		// 'no_comment' rule.
		$this->add_rule(
			'no_comment',
			__( 'Visitor has never commented', PO_LANG ),
			__(
				'Shows the Pop Up if the user has never left a comment. ' .
				'You may want to combine this condition with either "Visitor ' .
				'is logged in" or "Visitor is not logged in".', PO_LANG
			),
			'comment',
			20
		);

		if ( function_exists( 'is_pro_site' ) ) {
			// 'no_prosite' rule.
			$this->add_rule(
				'no_prosite',
				__( 'Site is not a Pro-Site', PO_LANG ),
				__( 'Shows the Pop Up if the site is not a Pro-Site.', PO_LANG ),
				'',
				20
			);
		}
	}


	/*===========================*\
	===============================
	==                           ==
	==           LOGIN           ==
	==                           ==
	===============================
	\*===========================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_login( $data ) {
		return is_user_logged_in();
	}


	/*==============================*\
	==================================
	==                              ==
	==           NO_LOGIN           ==
	==                              ==
	==================================
	\*==============================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_no_login( $data ) {
		return ! is_user_logged_in();
	}


	/*================================*\
	====================================
	==                                ==
	==           NO_COMMENT           ==
	==                                ==
	====================================
	\*================================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_no_comment( $data ) {
		$comments = $this->did_user_comment();
		return 0 === $comments;
	}


	/*=============================*\
	=================================
	==                             ==
	==           COMMENT           ==
	==                             ==
	=================================
	\*=============================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_comment( $data ) {
		$comments = $this->did_user_comment();
		return 0 < $comments;
	}


	/*================================*\
	====================================
	==                                ==
	==           NO_PROSITE           ==
	==                                ==
	====================================
	\*================================*/


	/**
	 * Apply the rule-logic to the specified popup
	 *
	 * @since  4.6
	 * @param  mixed $data Rule-data which was saved via the save_() handler.
	 * @return bool Decission to display popup or not.
	 */
	protected function apply_no_prosite( $data ) {
		$prosite = function_exists( 'is_pro_site' ) && is_pro_site();
		return ! $prosite;
	}


	/*======================================*\
	==========================================
	==                                      ==
	==           HELPER FUNCTIONS           ==
	==                                      ==
	==========================================
	\*======================================*/


	/**
	 * Checks if the user did already post any comments.
	 *
	 * @since  4.6
	 * @return bool
	 */
	protected function did_user_comment() {
		global $wpdb;
		static $Comment = null;

		if ( null === $Comment ) {
			// Guests (and maybe logged in users) are tracked via a cookie.
			$Comment = isset( $_COOKIE['comment_author_' . COOKIEHASH] ) ? 1 : 0;

			if ( ! $Comment && is_user_logged_in() ) {
				// For logged-in users we can also check the database.
				$sql = "
					SELECT COUNT(1)
					FROM {$wpdb->comments}
					WHERE user_id = %s
				";
				$sql = $wpdb->prepare( $sql, get_current_user_id() );
				$count = absint( $wpdb->get_var( $sql ) );
				$Comment = $count > 0;
			}
		}
		return $Comment;
	}
};

IncPopupRules::register( 'IncPopupRule_User' );