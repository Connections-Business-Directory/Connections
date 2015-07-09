<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var WP_Post $post
 * @var array   $meta
 */
?>

<div class="wrap">

	<h2><?php esc_html_e( 'System Email Log Item', 'connections' ); ?></h2>

	<table id='cn-email-log-details'>

		<tr>
			<th><?php echo esc_html_x( 'Date', 'Date and time an email was sent.', 'connections' ); ?></th>
			<td><?php echo date_i18n( 'Y-m-d H:i:s', strtotime( $post->post_date ) ); ?></td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Sent', 'connections' ); ?></th>
			<td><?php echo cnLog_Email::viewLogItem( 'response', $meta['response'] ); ?></td>
		</tr>

		<tr>
			<th><?php esc_html_e( 'Headers', 'connections' ); ?></th>
			<td><?php echo cnLog_Email::viewLogItem( 'headers', $meta['headers'] ); ?></td>
		</tr>

		<?php if ( $meta['type'] ): ?>
			<tr>
				<th><?php esc_html_e( 'Content Type', 'connections' ); ?></th>
				<td><?php echo cnLog_Email::viewLogItem( 'type', $meta['type'] ); ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( $meta['character_set'] ): ?>
			<tr>
				<th><?php esc_html_e( 'Character Set', 'connections' ); ?></th>
				<td><?php echo cnLog_Email::viewLogItem( 'character_set', $meta['character_set'] ); ?></td>
			</tr>
		<?php endif; ?>

		<tr>
			<th><?php echo esc_html_x( 'Subject', 'Email subject.', 'connections' ); ?></th>
			<td><?php echo esc_html( $post->post_title ); ?></td>
		</tr>

		<tr>
			<th><?php echo esc_html_x( 'From', 'Email sender (From).', 'connections' ); ?></th>
			<td><?php echo cnLog_Email::viewLogItem( 'from', $meta['from'] ); ?></td>
		</tr>

		<tr>
			<th><?php echo esc_html_x( 'To', 'Email recipients (To).', 'connections' ); ?></th>
			<td><?php echo cnLog_Email::viewLogItem( 'to', $meta['to'] ); ?></td>
		</tr>

		<?php if ( $meta['cc'] ) : ?>
			<tr>
				<th><?php echo esc_html_x( 'CC', 'Courtesy copy email addresses.', 'connections' ); ?></th>
				<td><?php cnLog_Email::viewLogItem( 'cc', $meta['cc'] ) ?></td>
			</tr>
		<?php endif; ?>

		<?php if ( $meta['bcc'] ): ?>
			<tr>
				<th><?php echo esc_html_x( 'BCC', 'Blind courtesy copy email addresses.', 'connections' ); ?></th>
				<td><?php cnLog_Email::viewLogItem( 'bcc', $meta['bcc'] ); ?></td>
			</tr>
		<?php endif; ?>

		<tr>
			<th><?php esc_html_e( 'Attachments', 'connections' ); ?></th>
			<td><?php echo cnLog_Email::viewLogItem( 'attachments', $meta['attachments'] ); ?></td>
		</tr>

		<tr>
			<th><?php echo esc_html_x( 'Message', 'Content of email.', 'connections' ); ?></th>
			<?php if ( ! empty( $meta['type'] ) && 'text/html' == $meta['type'] ) : ?>
				<td><?php echo wp_kses_post( $post->post_content ); ?></td>
			<?php else : ?>
				<td><?php echo wp_kses_data( $post->post_content ); ?></td>
			<?php endif; ?>
		</tr>

	</table>

	<br class="clear"/>
</div>
