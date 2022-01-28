/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';

class SocialNetworkIcon extends Component {

	render() {

		const { network, size } = this.props;

		const classes = [
			`cn-brandicon-${network.slug}`,
			`cn-brandicon-size-${size}`,
		];

		return (
			<span className='social-media-network cn-social-media-network'>
				<a className={ `url ${ network.type }` } href={ network.url } target='_blank' rel='noopener' title={ network.name }>
				<i className={ classes.join( ' ' ) } />
			</a>
			</span>
		)
	}
}

export default SocialNetworkIcon;
