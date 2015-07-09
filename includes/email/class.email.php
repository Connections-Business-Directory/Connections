<?php

/**
 * Class for sending email.
 * The purpose of this wp_mail wrapper class is to
 * make using wp_mail more sane; simpler to use while
 * maintaining compatibility with SMTP plugins.
 *
 * Examples:
 *
 * <code>
 * $email = new cnEmail;
 *
 * // Set email to be sent as HTML.
 * $email->html()
 *
 * // Set from whom the email is being sent.
 * $email->from( 'send@domain.tld', 'From Name' );
 *
 * // Send to multiple email addesses.
 * // Call for each address to which the email is to be sent.
 * $email->to( 'email-1@domain.tld', 'name' );
 * $email->to( 'email-2@domain.tld' );
 * $email->to( 'email-3@domain.tld', 'another name' );
 *
 * // Set the subject.
 * $email->subject( 'This is the email subject' );
 *
 * // Set the message.
 * $email->message( 'This is the email message.' );
 *
 * // Send the email.
 * $email->send();
 *
 * // To send the same email but to a different email address.
 * $email->clear( 'to' );
 * $email->to( 'email-4@domain.tld', 'name' );
 * $email->send();
 *
 * // The object can be completely reset for reuse to send a completely different email.
 * $email->clear();
 * </code>
 *
 * @package     Connections
 * @subpackage  Email
 * @copyright   Copyright (c) 2013, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       0.7.8
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class cnEmail
 */
class cnEmail {

	/**
	 * The email header array to be passed tp wp_mail().
	 *
	 * @since 0.7.8
	 * @var array
	 */
	private $header = array();

	/**
	 * The default email content type.
	 *
	 * @since 0.7.8
	 * @var string
	 */
	private $type = 'text/plain';

	/**
	 * The email charset.
	 *
	 * @since 0.7.8
	 * @var string
	 */
	private $charset = '';

	/**
	 * Array to store the name and email addresses
	 * from whom the email was sent.
	 *
	 * @since 0.7.8
	 * @var array
	 */
	private $from = array();

	/**
	 * Multidimensional array to store the names and email addresses
	 * to which the email is to be sent.
	 *
	 * @since 0.7.8
	 * @var array
	 */
	private $to = array();

	/**
	 * Multidimensional array to store the names and email addresses
	 * to which the email is to be sent as cc.
	 *
	 * @since 0.7.8
	 * @var array
	 */
	private $cc = array();

	/**
	 * Multidimensional array to store the names and email addresses
	 * to which the email is to be sent as bcc.
	 *
	 * @since 0.7.8
	 * @var array
	 */
	private $bcc = array();

	/**
	 * The email subject line.
	 *
	 * @since 0.7.8
	 * @var string
	 */
	private $subject = '';

	/**
	 * The email message/
	 *
	 * @since 0.7.8
	 * @var string
	 */
	private $message = '';

	/**
	 * Files to attach: a single filename, an array of file names,
	 * or a newline-delimited string list of multiple file names.
	 *
	 * @since 0.7.8
	 * @var array
	 */
	private $attachments = array();

	/**
	 * Set class defaults.
	 *
	 * @access public
	 * @since  0.7.8
	 */
	public function __construct() {

		// Set the charset.
		$this->charSet( get_bloginfo( 'charset' ) );
	}

	/**
	 * Add custom headers to be passed to the wp_mail() $header param.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @param $header
	 */
	public function header( $header ) {

		$this->header[] = $header;

	}

	/**
	 * Set whether or not the email should be sent as HTML.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @param bool $html
	 */
	public function html( $html = TRUE ) {

		if ( $html ) {

			// Set HTML Content Type
			$this->type = 'text/html';

		} else {

			// Set Plain Text Content Type
			$this->type = 'text/plain';

		}

	}

	/**
	 * Set the email character set.
	 *
	 * @access public
	 * @since  0.7.8
	 *
	 * @param $charset
	 */
	public function charSet( $charset ) {

		$this->charset = $charset;

	}

	/**
	 * Add attachment to be passed to the wp_mail() $attachments param.
	 *
	 * Files to attach: a single filename, an array of file names,
	 * or a newline-delimited string list of multiple file names.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param string | array
	 * @return void
	 */
	public function attachments( $attachments ) {

		$this->attachments = $attachments;

	}

	/**
	 * Sets the `from` email and name [optional].
	 * This will be passed the the wp_mail() $headers param.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  string $email From email address.
	 * @param  string $name  [optional] From email name.
	 * @return void
	 */
	public function from( $email, $name = '' ) {

		if ( empty( $name ) ) {

			$this->from['email'] = $email;

		} else {

			$this->from['name'] = $name;
			$this->from['email'] = $email;

		}

	}

	/**
	 * Sets the `to` email and name [optional].
	 * Can be called multiple times, one for each email address to which the email is to be sent.
	 * This will be passed the the wp_mail() $to param as an array.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  string $email To email address.
	 * @param  string $name  [optional] To email name.
	 * @return void
	 */
	public function to( $email, $name = '' ) {

		$count = count( $this->to );

		$this->to[ $count ]['email'] = trim( $email );
		$this->to[ $count ]['name'] = $name;

	}

	/**
	 * Sets the `cc` email and name [optional].
	 * Can be called multiple times, one for each email address to which the email is to be cc/d.
	 * This will be passed the the wp_mail() $to param as an array.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  string $email To email address.
	 * @param  string $name  [optional] To email name.
	 * @return void
	 */
	public function cc( $email, $name = '' ) {

		$count = count( $this->cc );

		$this->cc[ $count ]['email'] = trim( $email );
		$this->cc[ $count ]['name'] = $name;

	}

	/**
	 * Sets the `bcc` email and name [optional].
	 * Can be called multiple times, one for each email address to which the email is to be bcc/d.
	 * This will be passed the the wp_mail() $to param as an array.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  string $email To email address.
	 * @param  string $name  [optional] To email name.
	 * @return void
	 */
	public function bcc( $email, $name = '' ) {

		$count = count( $this->bcc );

		$this->bcc[ $count ]['email'] = trim( $email );
		$this->bcc[ $count ]['name'] = $name;

	}

	/**
	 * The email subject line.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  string $subject
	 * @return void
	 */
	public function subject( $subject ) {

		$this->subject = $subject;

	}

	/**
	 * The email message content.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  string $content
	 * @return void
	 */
	public function message( $content ) {

		$this->message = $content;

	}

	/**
	 * Send the email.
	 *
	 * This will remove all filters hooked in to all of the current
	 * wp_mail filters to prevent them from overriding the values
	 * set by this class. To be a good citizen, we'll add them back
	 * after the email has been sent.
	 *
	 * @access public
	 * @since 0.7.8
	 * @return bool
	 */
	public function send() {
		global $wp_filter;

		$filter = array();

		/*
		 * Temporarily store the filters hooked to the wp_mail filters.
		 */
		$filter['param']      = isset( $wp_filter['wp_mail'] ) ? $wp_filter['wp_mail'] : '';
		$filter['type']       = isset( $wp_filter['wp_mail_content_type'] ) ? $wp_filter['wp_mail_content_type'] : '';
		$filter['charset']    = isset( $wp_filter['wp_mail_charset'] ) ? $wp_filter['wp_mail_charset'] : '';
		$filter['from_name']  = isset( $wp_filter['wp_mail_from_name'] ) ? $wp_filter['wp_mail_from_name'] : '';
		$filter['from_email'] = isset( $wp_filter['wp_mail_from'] ) ? $wp_filter['wp_mail_from'] : '';

		/*
		 * Remove all filters hooked into the wp_mail filters to prevent conflicts.
		 */
		remove_all_filters( 'wp_mail' );
		remove_all_filters( 'wp_mail_content_type' );
		remove_all_filters( 'wp_mail_charset' );
		remove_all_filters( 'wp_mail_from_name' );
		remove_all_filters( 'wp_mail_from' );

		/**
		 * Prepare data to be compliant with @see wp_mail().
		 */
		$this->parseType();
		$this->parseFrom();
		$to = $this->parseTo();
		$this->parseCC();
		$this->parseBCC();

		$email = apply_filters(
			'cn_email',
			array(
				'to'          => $to,
				'subject'     => $this->subject,
				'message'     => $this->message,
				'headers'     => $this->header,
				'attachments' => $this->attachments
			)
		);

		/*
		 * Allow extensions to filter the email before sending.
		 */
		$email['headers']     = apply_filters( 'cn_email_header', $email['headers'] );
		$this->type           = apply_filters( 'cn_email_type', $this->type );
		$this->charset        = apply_filters( 'cn_email_charset', $this->charset );

		$this->from           = apply_filters( 'cn_email_from', $this->from );
		$email['to']          = apply_filters( 'cn_email_to', $email['to'] );
		$this->cc             = apply_filters( 'cn_email_cc', $this->cc );
		$this->bcc            = apply_filters( 'cn_email_bcc', $this->bcc );

		$email['subject']     = apply_filters( 'cn_email_subject', $email['subject'] );
		$email['message']     = apply_filters( 'cn_email_message', $email['message'] );
		$email['attachments'] = apply_filters( 'cn_email_attachments', $email['attachments'] );

		/*
		 * Allow extensions to do a pre send action.
		 */
		do_action(
			'cn_email_pre_send',
			$email['headers'],
			$this->type,
			$this->charset,
			$this->from,
			$email['to'],
			$this->cc,
			$this->bcc,
			$email['subject'],
			$email['message'],
			$email['attachments']
		);

		/*
		 * Send the email using wp_mail().
		 */
		$response = wp_mail( $email['to'], $email['subject'], $email['message'], $email['headers'], $email['attachments'] );

		/*
		 * Allow extensions to do a post send action.
		 */
		do_action(
			'cn_email_post_send',
			$email['headers'],
			$this->type,
			$this->charset,
			$this->from,
			$email['to'],
			$this->cc,
			$this->bcc,
			$email['subject'],
			$email['message'],
			$email['attachments'],
			$response
		);

		/*
		 * Be a good citizen and add the filters that were hooked back to the wp_mail filters.
		 */
		if ( ! empty( $filter['param'] ) ) $wp_filter['wp_mail']               = $filter['param'];
		if ( ! empty( $filter['type'] ) ) $wp_filter['wp_mail_content_type']   = $filter['type'];
		if ( ! empty( $filter['charset'] ) ) $wp_filter['wp_mail_charset']     = $filter['charset'];
		if ( ! empty( $filter['from_name'] ) ) $wp_filter['wp_mail_from_name'] = $filter['from_name'];
		if ( ! empty( $filter['from_email'] ) ) $wp_filter['wp_mail_from']     = $filter['from_email'];

		/**
		 * wp_mail() returns a (bool), so lets return the result.
		 */
		return $response;
	}

	/**
	 * Set the content type and char set header.
	 *
	 * @access private
	 * @since  8.2.10
	 */
	private function parseType() {

		$this->header['type'] = sprintf( 'Content-type: %1$s; charset=%2$s', $this->type, $this->charset );
	}

	/**
	 * Set the 'From' header for @see wp_mail().
	 *
	 * @access private
	 * @since  8.2.10
	 */
	private function parseFrom() {

		if ( isset( $this->from['name'] ) ) {

			$this->header['from'] = sprintf( 'From: %1$s <%2$s>', $this->from['name'], $this->from['email'] );

		} else {

			$this->header['from'] = sprintf( 'From: %s', $this->from['email'] );

		}
	}

	/**
	 * Build the to array for the @see wp_mail() $to param.
	 *
	 * @access private
	 * @since  8.2.10
	 *
	 * @return array
	 */
	private function parseTo() {

		$to = array();

		if ( count( $this->to ) >= 1 ) {

			for ( $i = 0; $i < count( $this->to ); $i++ ) {

				if ( empty( $this->to[ $i ]['name'] ) ) {

					$to[] = $this->to[ $i ]['email'];

				} else {

					$to[] = sprintf( '%1$s <%2$s>', $this->to[ $i ]['name'], $this->to[ $i ]['email'] );

				}
			}
		}

		return $to;
	}

	/**
	 * Build the cc header string for @see wp_mail() and add it to the header.
	 *
	 * @access private
	 * @since  8.2.10
	 */
	private function parseCC() {

		if ( count( $this->cc ) >= 1 ) {

			for ( $i = 0; $i < count( $this->cc ); $i++ ) {

				if ( empty( $this->cc[ $i ]['name'] ) ) {

					$this->header[] = sprintf( 'Cc: %s', $this->cc[ $i ]['email'] );

				} else {

					$this->header[] = sprintf( 'Cc: %1$s <%2$s>', $this->cc[ $i ]['name'], $this->cc[ $i ]['email'] );

				}
			}
		}
	}

	/**
	 * Build the bcc header string for @see wp_mail() and add it to the header.
	 *
	 * @access private
	 * @since  8.2.10
	 */
	private function parseBCC() {

		if ( count( $this->bcc ) >= 1 ) {

			for ( $i = 0; $i < count( $this->bcc ); $i++ ) {

				if ( empty( $this->bcc[ $i ]['name'] ) ) {

					$this->header[] = sprintf( 'Bcc: %s', $this->bcc[ $i ]['email'] );

				} else {

					$this->header[] = sprintf( 'Bcc: %1$s <%2$s>', $this->bcc[ $i ]['name'], $this->bcc[ $i ]['email'] );

				}
			}
		}
	}

	/**
	 * Clear any of the email properties.
	 *
	 * @access public
	 * @since 0.7.8
	 * @param  string $property The property to clear.
	 * @return void
	 */
	public function clear( $property = 'all' ) {

		switch ( $property ) {

			case 'header':

				$this->header = array();
				$this->charset = get_bloginfo( 'charset' );
				$this->html( FALSE );
				break;

			case 'to':

				$this->to = array();
				break;

			case 'cc':

				$this->cc = array();
				break;

			case 'bcc':

				$this->bcc = array();
				break;

			case 'subject':

				$this->subject = '';
				break;

			case 'message':

				$this->message = '';
				break;

			default:

				$this->header = array();
				$this->charset = get_bloginfo( 'charset' );
				$this->html( FALSE );

				$this->to = array();
				$this->cc = array();
				$this->bcc = array();

				$this->subject = '';
				$this->message = '';
				break;
		}

	}

}
