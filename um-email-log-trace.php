<?php
/**
 * Plugin Name:         Ultimate Member - Email Log Trace
 * Description:         Extension to Ultimate Member for logging email activities.
 * Version:             1.1.0 development
 * Requires PHP:        7.4
 * Author:              Miss Veronica
 * License:             GPL v3 or later
 * License URI:         https://www.gnu.org/licenses/gpl-2.0.html
 * Author URI:          https://github.com/MissVeronica
 * Plugin URI:          https://github.com/MissVeronica/um-email-log-trace
 * Update URI:          https://github.com/MissVeronica/um-email-log-trace
 * Text Domain:         ultimate-member
 * Domain Path:         /languages
 * UM version:          2.10.0
 */

 class UM_Email_Log_Trace {

    public $file = WP_CONTENT_DIR . '/um_logging_email.txt';

    function __construct() {

        add_filter( 'um_registration_user_role',             array( $this, 'um_registration_user_role_logging' ), 10, 3 );
        add_action( 'um_user_register',                      array( $this, 'um_user_register_logging' ), 10, 3 );
        add_action( 'um_registration_complete',              array( $this, 'um_registration_complete_logging' ), 90, 3 );
        add_action( 'um_before_user_status_is_set',          array( $this, 'um_before_user_status_is_set_logging' ), 10, 3 );
        add_filter( 'um_disable_email_notification_sending', array( $this, 'um_disable_email_notification_logging' ), 99999, 4 );
        add_action( 'um_before_email_notification_sending',  array( $this, 'um_before_email_notification_logging' ), 20, 2 );
        add_filter( 'um_email_send_subject',                 array( $this, 'um_email_send_subject_logging' ), 99999, 2 );
        add_filter( 'um_email_template_path',                array( $this, 'um_email_template_path_logging' ), 99999, 3 );
        add_action( 'um_dispatch_email',                     array( $this, 'um_dispatch_email_logging' ), 20, 3 );
        add_filter( 'wp_mail',                               array( $this, 'wp_mail_logging' ), 10, 1 );

        add_action( 'um_after_user_is_set_as_awaiting_email_confirmation',
                                                             array( $this, 'um_after_user_is_set_as_awaiting_email_confirmation_logging' ), 10, 1 );
        add_action( 'um_after_user_is_inactive',             array( $this, 'um_after_user_is_inactive_logging' ), 10, 1 );
        add_action( 'um_after_user_is_rejected',             array( $this, 'um_after_user_is_rejected_logging' ), 10, 1 );
        add_action( 'um_after_user_is_set_as_pending',       array( $this, 'um_after_user_is_set_as_pending_logging' ), 10, 1 );
        add_action( 'um_after_user_is_approved',             array( $this, 'um_after_user_is_approved_logging' ), 10, 1 );
        add_action( 'um_after_user_is_reactivated',          array( $this, 'um_after_user_is_reactivated_logging' ), 10, 1 );

        add_filter( 'pre_as_enqueue_async_action',           array( $this, 'pre_as_enqueue_async_action_logging' ), 99999, 2 ); 
        
    }

    public function um_registration_user_role_logging( $user_role, $args, $form_data ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_registration_user_role' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '1 user_role: ' . $user_role . "\n\n", FILE_APPEND );
        return $user_role;
    }

    public function um_user_register_logging( $user_id, $args, $form_data ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_user_register' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '2 user_id: ' . $user_id . "\n\n", FILE_APPEND );
    }

    public function um_registration_complete_logging( $user_id, $args, $form_data ) {
        um_fetch_user( $user_id );
        $registration_status = um_user( 'status' );
        file_put_contents( $this->file, $this->get_time() . 'filter um_registration_complete' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '3 registration_status: ' . $registration_status . "\n\n", FILE_APPEND );
    }

    public function um_before_user_status_is_set_logging( $status, $user_id, $old_status ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_before_user_status_is_set' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '4 status: ' . $status . "\n", FILE_APPEND );
        file_put_contents( $this->file, '5 user_id: ' . $user_id . "\n\n", FILE_APPEND );
    }

    public function um_disable_email_notification_logging( $bool, $email, $template, $args ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_disable_email_notification_sending' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '6 bool: ' . intval( $bool ) . "\n", FILE_APPEND );
        file_put_contents( $this->file, '7 email: ' . $this->verify_log_email( $email ) . "\n", FILE_APPEND );
        file_put_contents( $this->file, '8 template: ' . $this->email_status( $template ) . "\n\n", FILE_APPEND );
        return $bool;
    }

    public function um_before_email_notification_logging( $email, $template ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_before_email_notification_sending' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '9 email: ' . $this->verify_log_email( $email ) . "\n", FILE_APPEND );
        file_put_contents( $this->file, '10 template: ' . $this->email_status( $template ) . "\n\n", FILE_APPEND );
    }

    public function um_email_send_subject_logging( $subject, $template ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_email_send_subject' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '11 subject: ' . $subject . "\n", FILE_APPEND );
        file_put_contents( $this->file, '12 template: ' . $this->email_status( $template ) . "\n\n", FILE_APPEND );
        return $subject;
    }

    public function um_email_template_path_logging( $located, $slug, $args ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_email_template_path' . "\n", FILE_APPEND );
        $status = ( empty( UM()->options()->get( "{$slug}_on" ) )) ? 'Email not active' : 'Email active';
        file_put_contents( $this->file, '13 slug: ' . $this->email_status( $slug ) . "\n", FILE_APPEND );
        file_put_contents( $this->file, '14 located: ' . str_replace( ABSPATH, '...', $located ) . "\n", FILE_APPEND );
        if ( file_exists( $located )) {
            $contents = file_get_contents( $located );
            $contents = ( empty( $contents )) ? 'empty' : $contents;
            file_put_contents( $this->file, '15 templete size: ' . strlen( $contents ) . " bytes\n\n", FILE_APPEND );
        } else {
            file_put_contents( $this->file, '15 templete not found' . "\n\n", FILE_APPEND );
        }
        return $located;
    }

    public function um_dispatch_email_logging( $user_email, $template, $args ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_dispatch_email' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '16 user_email: ' . $this->verify_log_email( $user_email ) . "\n", FILE_APPEND );
        $status = ( empty( UM()->options()->get( "{$template}_on" ) )) ? 'Email not active' : 'Email active';
        file_put_contents( $this->file, '17 template: ' . $this->email_status( $template ) . "\n\n", FILE_APPEND );
    }

    public function wp_mail_logging( $args ) {
        file_put_contents( $this->file, $this->get_time() . 'filter wp_mail' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '20 Outgoing email in WP to user_email: ' . $this->verify_log_email( $args['to'] ) . "\n\n", FILE_APPEND );
        return  $args;
    }

    public function um_after_user_is_set_as_awaiting_email_confirmation_logging( $user_id ) {
        file_put_contents( $this->file, $this->get_time() . 'filter om_after_user_is_set_as_awaiting_email_confirmation' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '31 user_id: ' . $user_id . "\n\n", FILE_APPEND );
    }

    public function um_after_user_is_inactive_logging( $user_id ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_after_user_is_inactive' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '32 user_id: ' . $user_id . "\n\n", FILE_APPEND );  
    }

    public function um_after_user_is_rejected_logging( $user_id ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_after_user_is_rejected' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '33 user_id: ' . $user_id . "\n\n", FILE_APPEND );
    }

    public function um_after_user_is_set_as_pending_logging( $user_id ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_after_user_is_set_as_pending' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '34 user_id: ' . $user_id . "\n\n", FILE_APPEND );
    }

    public function um_after_user_is_approved_logging( $user_id ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_after_user_is_approved' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '35 user_id: ' . $user_id . "\n\n", FILE_APPEND );
    }

    public function um_after_user_is_reactivated_logging( $user_id ) {
        file_put_contents( $this->file, $this->get_time() . 'filter um_after_user_is_reactivated' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '36 user_id: ' . $user_id . "\n\n", FILE_APPEND );
    }

    public function pre_as_enqueue_async_action_logging( $short_circuit, $hook ) {
        $sc = ( $short_circuit == null ) ? 'null' : 'not null !!! ' . intval( $short_circuit );
        file_put_contents( $this->file, $this->get_time() . 'filter pre_as_enqueue_async_action' . "\n", FILE_APPEND );
        file_put_contents( $this->file, '40 Action hook: ' . $hook . "\n", FILE_APPEND );
        file_put_contents( $this->file, '41 short_circuit: ' . $sc . "\n", FILE_APPEND );
        $bool = ( UM()->maybe_action_scheduler()->is_enabled() ) ? 'true' : 'false';
        $scheuled = '';
        if ( $bool == 'true' ) {
            $cron_job = wp_next_scheduled( 'action_scheduler_run_queue' );
            $scheuled = ' Next time for cronjob: ' . date_i18n( 'Y-m-d H:i:s ', $cron_job );
        }
        file_put_contents( $this->file, '42 Action Scheduler active: ' . $bool . $scheuled . "\n\n", FILE_APPEND );
        return $short_circuit;
    }

    public function verify_log_email( $email ) {
        $user = get_user_by( 'email', $email );
        $res = ( is_email( $email )) ? 'OK valid email for user_id ' . $user->ID : 'invlid email';
        $res = ( bloginfo( 'admin_email' )   == $email ) ? 'WP Admin email' : $res;
        $res = ( get_option( 'admin_email' ) == $email ) ? 'UM Admin email' : $res;
        return $res;
    }

    public function get_time() {
        return date_i18n( 'Y-m-d H:i:s ', current_time( 'timestamp' ));
    }

    public function email_status( $template ) {
        $status = ( empty( UM()->options()->get( "{$template}_on" ) )) ? 'Email NOT active' : 'Email active';
        return $template . ' Template status: ' . $status;
    }

}

new UM_Email_Log_Trace();


