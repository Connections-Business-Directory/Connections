/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';

/**
 * External dependencies
 */
import { isUndefined } from 'lodash';

class EntryExcerpt extends Component {

	render() {

		const { entry } = this.props;

		if ( isUndefined( entry.excerpt ) || 1 > entry.excerpt.rendered.length ) return null;

		return (
			<div className='excerpt' dangerouslySetInnerHTML={ { __html: decodeEntities( entry.excerpt.rendered ) } } />
		)
	}
}

export default EntryExcerpt;
