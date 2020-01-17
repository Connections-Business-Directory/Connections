/**
 * WordPress dependencies
 */
const { Component } = wp.element;
const { decodeEntities } = wp.htmlEntities;

/**
 * External dependencies
 */
import { cloneDeep, findIndex, has, isUndefined } from 'lodash';

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
