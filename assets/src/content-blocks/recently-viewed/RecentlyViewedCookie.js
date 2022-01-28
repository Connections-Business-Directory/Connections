/**
 * External dependencies
 */
import * as Cookies from "js-cookie";
import { isArray, remove, take } from "lodash";

/**
 * @return {[]}
 */
const getRecentlyViewed = () => {

	let recentlyViewed = Cookies.getJSON( 'cnRecentlyViewed' );

	if ( !isArray( recentlyViewed ) ) {

		recentlyViewed = [];
	}

	return recentlyViewed;
}

/**
 * The `cnViewing` variable is set during the `Connections_Directory/Render/Template/Single_Entry/After` action
 * by `wp_add_inline_script()` hooked to the `frontend` handle.
 *
 * NOTE: The `cnViewing` variable is JSON encoded, it must be decoded before use.
 *
 * @typedef {{postID: string, entryID: string}} cnViewing
 */
if ( 'undefined' !== typeof cnViewing ) {

	let recentlyViewed = getRecentlyViewed();

	// If current Entry being viewed already exists, remove it from the array.
	if ( 0 <= recentlyViewed.findIndex( x => x.entryID === cnViewing.entryID ) ) {

		remove( recentlyViewed, x => x.entryID === cnViewing.entryID );
	}

	// Insert the currently being viewed Entry to the head of the array.
	recentlyViewed.unshift( cnViewing );

	Cookies.set( 'cnRecentlyViewed', take( recentlyViewed, 10 ) );
}
