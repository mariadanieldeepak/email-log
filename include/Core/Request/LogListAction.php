<?php namespace EmailLog\Core\Request;

use EmailLog\Core\Loadie;

/**
 * Actions performed in Log List.
 *
 * @since 2.0.0
 */
class LogListAction implements Loadie {

	/**
	 * Setup actions.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_action( 'wp_ajax_el-log-list-view-message', array( $this, 'display_email_message_callback' ) );

		add_action( 'el-log-list-delete', array( $this, 'delete_logs' ) );
		add_action( 'el-log-list-delete-all', array( $this, 'delete_all_logs' ) );
	}

	/**
	 * AJAX callback for displaying email content.
	 *
	 * @since 1.6
	 */
	public function display_email_message_callback() {
		if ( current_user_can( 'manage_options' ) ) {
			$message = '';

			$id = absint( $_GET['log_id'] );
			if ( $id > 0 ) {
				$message = $this->get_table_manager()->get_log_message( $id );
			}

			echo wpautop( $message );
		}

		die(); // this is required to return a proper result
	}

	/**
	 * Delete log entries by id.
	 *
	 * @param array $data Request data.
	 */
	public function delete_logs( $data ) {
		$ids = $data['email-log'];

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids = array_map( 'absint', $ids );
		$id_list = implode( ',', $ids );

		$logs_deleted = $this->get_table_manager()->delete_logs( $id_list );
		$this->render_log_deleted_notice( $logs_deleted );
	}

	/**
	 * Delete all log entries.
	 */
	public function delete_all_logs() {
		$logs_deleted = $this->get_table_manager()->delete_all_logs();
		$this->render_log_deleted_notice( $logs_deleted );
	}

	/**
	 * Render Logs deleted notice.
	 *
	 * @param int|False $logs_deleted Number of entries deleted, False otherwise.
	 */
	protected function render_log_deleted_notice( $logs_deleted ) {
		$message = __( 'There was some problem in deleting the email logs', 'email-log' );
		$type    = 'error';

		if ( absint( $logs_deleted ) > 0 ) {
			$message = sprintf( _n( '1 email log deleted.', '%s email logs deleted', $logs_deleted, 'email-log' ), $logs_deleted );
			$type    = 'updated';
		}

		add_settings_error(
			'log-list',
			'deleted-email-logs',
			$message,
			$type
		);
	}

	/**
	 * Get TableManager instance.
	 *
	 * @return  \EmailLog\Core\DB\TableManager TableManager instance.
	 */
	protected function get_table_manager() {
		$email_log = email_log();

		return $email_log->table_manager;
	}
}
