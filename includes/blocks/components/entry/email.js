/**
 * WordPress dependencies
 */
const { Component } = wp.element;
// const { decodeEntities } = wp.htmlEntities;

/**
 * External dependencies
 */
import { cloneDeep, findIndex, has, isUndefined } from 'lodash';

class EntryEmail extends Component {

	render() {

		const { entry, preferred = false } = this.props;

		if ( isUndefined( entry.email ) ) return null;

		let addresses = entry.email
			.filter( ( email ) => {

				return preferred === email.preferred || false === preferred;
			} )
			.map( ( email ) => {

				return (
					<div key={ email.id }>{ email.address.rendered }</div>
				)
			} );

		if ( 0 === addresses.length ) {

			addresses = <div key={ entry.email[0].id }>{ entry.email[0].address.rendered }</div>
		}

		return (
			<div className='email-addresses'>{ addresses }</div>
		)
	}
}

export default EntryEmail;
