/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';

/**
 * External dependencies
 */
import { isUndefined } from 'lodash';

class EntryPhoneNumbers extends Component {

	render() {

		const { entry, preferred = false } = this.props;

		if ( isUndefined( entry.tel )  || 0 === entry.tel.length ) return null;

		let numbers = entry.tel
			.filter( ( phone ) => {

				return preferred === phone.preferred || false === preferred;
			} )
			.map( ( phone ) => {

			return (
				<div key={ phone.id }>{ phone.number.rendered }</div>
			)
		} );

		if ( 0 === numbers.length ) {

			numbers = <div key={ entry.tel[0].id }>{ entry.tel[0].number.rendered }</div>
		}

		return (
			<div className='phone-numbers'>{ numbers }</div>
		)
	}
}

export default EntryPhoneNumbers;
