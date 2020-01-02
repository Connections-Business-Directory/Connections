/**
 * WordPress dependencies
 */
const { Component } = wp.element;
// const { decodeEntities } = wp.htmlEntities;

/**
 * External dependencies
 */
import { cloneDeep, findIndex, has, isUndefined } from 'lodash';

/**
 * Internal dependencies
 */
import {
	SocialNetworkIcon,
} from "@Connections-Directory/components";

class EntrySocialNetworks extends Component {

	render() {

		const { entry } = this.props;

		if ( isUndefined( entry.social ) ) return null;

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
