/**
 * WordPress dependencies
 */
const { Component } = wp.element;
const { decodeEntities } = wp.htmlEntities;
const { __, _n, _nx, _x, sprintf } = wp.i18n;

/**
 * External dependencies
 */
import { has } from 'lodash';

class EntryImage extends Component {

	render() {

		const {
			      entry,
			      size,
			      type
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

		} else {

			return null;
		}
	}
}

export default EntryImage;
