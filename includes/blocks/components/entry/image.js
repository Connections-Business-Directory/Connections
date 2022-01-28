/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';
import { __, sprintf } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { has } from 'lodash';

class EntryImage extends Component {

	render() {

		const {
			      entry,
			      size,
			      type,
			      usePlaceholder = false,
			      placeholderWidth = 600,  // This should be set the width fetched via the REST API.
			      placeholderHeight = 520, // This should be set the height fetched via the REST API.
		      } = this.props;

		if ( has( entry.images, [ type, size ] ) ) {

			const {
				height,
				url,
				width,
			} = entry.images[ type ][ size ];

			return (
				<span className="cn-image-style">
					<span style={ {
						display:  'block',
						maxWidth: '100%',
						width:    `${ width }px`
					} }>
						<img
							className={ 'cn-image ' + type }
							width={ width }
							height={ height }
							srcSet={ url.replace( /^https?:/, '' ) + ' 1x' }
							alt={
								sprintf(
									__( '%s of %s', 'connections' ),
									type.charAt(0).toUpperCase() + type.slice(1),
									decodeEntities( entry.fn.rendered )
								)
							}
							// loading='lazy'
						/>
					</span>
				</span>
			)

		} else if ( true === usePlaceholder ) {

			let style = {
				display: 'inline-block',
				paddingBottom: `calc(${placeholderHeight} / ${placeholderWidth} * 100%)`,
				width: placeholderWidth + 'px',
			}

			return (
				<span className={ 'cn-image-' + type + ' cn-image-none' }
				      style={style}>
					<span>{ __( 'No Image Available', 'connections' ) }</span>
				</span>
			)

		} else {

			return null;
		}
	}
}

export default EntryImage;
