/**
 * WordPress dependencies
 */
const { Component, Fragment } = wp.element;
const { decodeEntities } = wp.htmlEntities;

class EntryName extends Component {

	render() {

		const { attributes, entry, tag } = this.props;
		const Tag = tag;

		return (
			<Tag>{ decodeEntities( entry.name.rendered ) }</Tag>
		)
	}
}

export default EntryName;
