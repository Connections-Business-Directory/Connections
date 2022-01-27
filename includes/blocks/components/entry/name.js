/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';

class EntryName extends Component {

	render() {

		const { asPermalink = false, attributes, entry, tag } = this.props;
		const Tag = tag;

		let name = decodeEntities( entry.fn.rendered );

		if ( asPermalink ) {

			name = <a href={entry.link}>{name}</a>
		}

		return (
			<Tag>{name}</Tag>
		)
	}
}

export default EntryName;
