/**
 * WordPress dependencies
 */
const { Component, Fragment } = wp.element;
const { decodeEntities } = wp.htmlEntities;

/**
 * External dependencies
 */
import { cloneDeep, findIndex, has, isUndefined } from 'lodash';

class EntryTitle extends Component {

	render() {

		const { entry } = this.props;

		if ( isUndefined( entry.job_title ) || 1 > entry.job_title.rendered.length ) return null;

		return (
			<div className='title'>{ decodeEntities( entry.job_title.rendered ) }</div>
		)
	}
}

export default EntryTitle;
