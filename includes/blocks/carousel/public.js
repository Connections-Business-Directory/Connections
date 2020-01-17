/**
 * WordPress dependencies
 */
const { render } = wp.element;
const { decodeEntities } = wp.htmlEntities;

/**
 * Internal dependencies
 */
import { decodeROT13 } from "@Connections-Directory/components/rot13";

/**
 * External dependencies
 */
import Slider from 'react-slick';
import 'slick-carousel/slick/slick.css';
import 'slick-carousel/slick/slick-theme.css';

/**
 * Import styles.
 */
import './style.scss';

const carousels = document.querySelectorAll( '.slick-slider-block' );

carousels.forEach( carousel => {

	const settings = JSON.parse( carousel.dataset.slickSliderSettings );

	/*
	 * Build in support for the ROT13 Email Encryption addon by searching for the encoded email and then decoding it.
	 * This should be built into the addon, but this should do for now.
	 */
	const afterChange = () => {

		const encoded = carousel.querySelectorAll( '.slick-slider-block .slick-current span.qrpelcg' );

		encoded.forEach( item => {

			item.outerHTML = decodeROT13( decodeEntities( item.innerHTML ) );
		} );
	};

	const slides = Array.prototype.map.call(
		carousel.querySelectorAll( '.slick-slider-slide' ),
		( slide, i ) => {

			return (
				<div key={ i } dangerouslySetInnerHTML={ { __html: slide.innerHTML } } />
			)
		}
	);

	render(
		<Slider afterChange={ afterChange } { ...settings }>{ slides }
		</Slider>,
		carousel
	);
});
