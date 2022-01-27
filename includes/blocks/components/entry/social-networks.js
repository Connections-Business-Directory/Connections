/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';

/**
 * External dependencies
 */
import { isUndefined } from 'lodash';

/**
 * Internal dependencies
 */
import {
	SocialNetworkIcon,
} from "@Connections-Directory/components";

class EntrySocialNetworks extends Component {

	render() {

		const { entry } = this.props;

		if ( isUndefined( entry.social ) || 0 === entry.social.length ) return null;

		let networks = entry.social.map( ( network ) => {

			return (
				<SocialNetworkIcon key={ network.id } network={ network } size={ 24 } />
			)
		} );

		return (
			<span className='social-media-block'>{ networks }</span>
		)
	}
}

export default EntrySocialNetworks;
