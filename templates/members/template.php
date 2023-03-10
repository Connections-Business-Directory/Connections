<?php
/**
 * Template HTML Output.
 *
 * @package     Connections
 * @subpackage  Template HTML Output
 * @copyright   Copyright (c) 2015, Steven A. Zahm
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       unknown
 *
 * @phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @author :   Phill Pafford
 * @website:  http://llihp.blogspot.com
 *
 * @todo   :
 *    -Add Link to personal profile in popup for each member displayed, NOT FOR MOBILE VIEWING
 */
// create the div.
$member_listing        = '<div class="member-entry">';
$mobile_member_listing = '<div class="member-entry">';

// Info Div header.
$member_listing        .= '<div><span class="member-details"><strong>';
$mobile_member_listing .= '<div><span class="member-details"><strong>';
/** @var cnEntry $entry */
if ( count( $entry->getFamilyMembers() ) > 0 ) {

	// create family member.
	$member_group = new cnEntry();

	// Create the popup container.
	// $member_popup_info = '<div id="popup-group-name"><span>' . $entry->getFamilyName() . '</span></div>';

	// Set a counter.
	$counter = 0;

	foreach ( $entry->getFamilyMembers() as $relationData ) {
		// Increment.
		$counter ++;

		// Set family member id.
		$member_group->set( $relationData['entry_id'] );

		if ( $counter > 1 ) {
			$member_list_first_names .= ', ' . $member_group->getName( array( 'format' => '%first%' ) );
		} else {
			$member_list_first_names = $member_group->getName( array( 'format' => '%first%' ) );
		}
	}

	$member_popup_info  = '<div id="popup-group-members"><span>' . esc_html( $member_list_first_names ) . '</span></div>';
	$member_popup_info .= '<div id="popup-group-name"><span>' . esc_html( $entry->getFamilyName() ) . '</span></div>';

	// Get Home phone number.
	foreach ( $entry->getPhoneNumbers() as $key_homephone => $value_homephone ) {

		// List home number for family.
		foreach ( $value_homephone as $key_homenumber => $value_homenumber ) {
			// echo "Home Key: " . $key_homenumber . " Home Value: " . $value_homenumber . "<br />";

			// Check for home number.
			if ( 'type' === $key_homenumber && 'homephone' === $value_homenumber ) {

				// Find home number in $value_homephone array.
				if ( '' !== $value_homephone->number ) {

					// Add homephone info.
					$member_popup_info     .= 'Home: ' . esc_html( $value_homephone->number ) . '<br />';
					$mobile_member_listing .= 'Home: ' . esc_html( $value_homephone->number ) . '<br />';
				}
			}
		}
	}

	// Get Address for group.
	foreach ( $entry->getAddresses() as $value_address ) {

		// List all addresses
		// foreach ( $value_addresses as $value_address) {

		// list each address.
		if ( 'home' === $value_address->type ) {

			// Format the address.
			$address = esc_html( $value_address->line_one ) . '<br />';

			// Check for line2.
			if ( '' !== $value_address->line_two ) {
				$address .= esc_html( $value_address->line_two ) . '<br />';
			}

			$address .= esc_html( $value_address->city ) . ', ';
			$address .= esc_html( $value_address->state ) . ' ';
			$address .= esc_html( $value_address->zipcode );

			$address_link  = 'https://maps.google.com/?q=';
			$address_link .= $value_address->line_one . ' ';

			// Check for line2.
			if ( '' !== $value_address->line_two ) {
				$address_link .= $value_address->line_two . ' ';
			}

			$address_link .= $value_address->city . ', ';
			$address_link .= $value_address->state . ' ';
			$address_link .= $value_address->zipcode;

			// Add the address.
			$member_popup_info     .= $address . '<br /><a class="google-maps-link" href="' . esc_url( $address_link ) . '" target="_blank">View Large Map</a><br />';
			$mobile_member_listing .= '<br /><a class="google-maps-link" href="' . esc_url( $address_link ) . '">View Map</a><br />';

			// Find all the spaces.
			$pattern = '/\s/';

			// replace with +.
			$replacement = '+';

			// Convert for iframe Google map.
			$iframe_google_map = preg_replace( $pattern, $replacement, $address_link );

			// Google maps parameter
			// Embed.
			$google_maps_parms  = '&amp;output=embed';
			$google_maps_parms .= '&amp;ie=UTF8';

			// t= Map Type. The available options are "m" map, "k" satellite, "h" hybrid, "p" terrain.
			$google_maps_parms .= '&amp;t=h';

			// z= Sets the zoom level.
			$google_maps_parms .= '&amp;z=17';

			// Add the parms.
			$iframe_google_map = $iframe_google_map . $google_maps_parms;

			// Hide the map.
			$member_popup_info .= '<div class="google-map" >';
			$member_popup_info .= '<iframe width="350" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="' . $iframe_google_map . '">';
			$member_popup_info .= '</iframe>';
			$member_popup_info .= '</div>';
		}
		// }
	}

	// Search for member info.
	foreach ( $entry->getFamilyMembers() as $relationData ) {

		// Set family member id.
		$member_group->set( $relationData['entry_id'] );

		// Clear temp vars.
		$member_name          = '';
		$mobile_member_name   = '';
		$member_mobile        = '';
		$mobile_member_mobile = '';
		$member_email         = '';
		$mobile_member_email  = '';

		// List family member, add link to personal profile.
		$member_name         = '<br />' . $member_group->getFullFirstLastName() . ':<br />'; // @todo: Would like to link to person profile
		$mobile_member_name .= '<br />' . $member_group->getFullFirstLastName() . ':<br />';

		// Check for family member and display all info.
		if ( count( $relationData ) > 0 ) {
			// Check if array.
			if ( is_array( $member_group->getPhoneNumbers() ) ) {
				// Get all phone numbers for family members.
				foreach ( $member_group->getPhoneNumbers() as $key_phone => $value_phone ) {

					// List all numbers for family members.
					foreach ( $value_phone as $key_number => $value_number ) {

						if ( 'type' === $key_number && 'cellphone' === $value_number ) {

							// Find Mobile number(s) in $value_phone array.
							if ( '' !== $value_phone->number ) {

								// Add mobile info.
								$member_mobile        = 'Mobile: ' . $value_phone->number . '<br />';
								$mobile_member_mobile = 'Mobile: ' . $value_phone->number . '<br />';
							}
						}
					}
				}
			}

			// Check if array.
			if ( is_array( $member_group->getEmailAddresses() ) ) {
				// Get email addresses.
				foreach ( $member_group->getEmailAddresses() as $key_email => $value_email ) {

					// Display the personal email address.
					foreach ( $value_email as $key_eAddress => $value_eAddress ) {
						if ( 'address' === $key_eAddress && '' !== $value_eAddress ) {

							// Add email info.
							$member_email        = 'Email: ' . $value_email->address . '<br />';
							$mobile_member_email = 'Email: ' . $value_email->address . '<br />';
						}
					}
				}
			}

			// Check for Mobile Phone and Address before displaying name
			// If both are blank don't display name.
			if ( '' !== $member_mobile || '' !== $member_email ) {
				$member_popup_info     .= $member_name . $member_mobile . $member_email;
				$mobile_member_listing .= $mobile_member_name . $mobile_member_mobile . $mobile_member_email;
			}
		}
	}
}

// Build members first name list.
if ( '' !== $member_list_first_names ) {
	$member_list_first_names = ' - ' . $member_list_first_names;
}

// Add group name.
$member_listing    .= '<a class="contact" id="' . esc_attr( $entry->getId() ) . '">' . esc_html( $entry->getFamilyName() . $member_list_first_names ) . '</a>';
$mobile_member_info = '<span class="m-contact" id="' . esc_attr( $entry->getId() ) . '"><b>' . esc_html( $entry->getFamilyName() . $member_list_first_names ) . '</b></span><br />' . $mobile_member_listing;

// Close the Info Div header.
$member_listing     .= '</strong></span><br />';
$mobile_member_info .= '</strong></span><br />';
$member_listing     .= '<textarea style="display: none;">' . esc_textarea( $member_popup_info ) . '</textarea>';
// Close the div.
$member_listing     .= '</div><div style="clear:both;"></div></div>';
$mobile_member_info .= '</div><div style="clear:both;"></div></div><hr />';

// This works for the mobile browser check with the MobilePress plugin.
if ( isset( $_SESSION['SESS_MOBILE_ACTIVE'] ) && true === $_SESSION['SESS_MOBILE_ACTIVE'] ) {
	// HTML is escaped above.
	echo $mobile_member_info; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} else {
	// Display Family Listing.
	// HTML is escaped above.
	echo $member_listing; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
unset( $member_listing, $mobile_member_info );
