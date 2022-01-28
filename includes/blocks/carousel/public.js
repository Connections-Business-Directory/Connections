/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';
import { decodeEntities } from '@wordpress/html-entities';

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

const carousels = document.querySelectorAll( '.slick-slider-block' );

carousels.forEach( carousel => {

	const settings = JSON.parse( carousel.dataset.slickSliderSettings );

	/*
	 * Example breakpoint settings.
	 * @link https://stackoverflow.com/q/57153664/5351316
	 */
	// slick( {
	// 	dots:           true,
	// 	infinite:       true,
	// 	slidesToShow:   3,
	// 	slidesToScroll: 1,
	// 	mobileFirst:    true,
	// 	responsive:     [
	// 		{
	// 			breakpoint: 1200,
	// 			settings:   {
	// 				slidesToShow: 3,
	// 				infinite:     true
	// 			}
	// 		},
	// 		{
	// 			breakpoint: 768,
	// 			settings:   {
	// 				slidesToShow: 2,
	// 				infinite:     true
	// 			}
	// 		},
	// 		{
	// 			breakpoint: 480,
	// 			settings:   {
	// 				slidesToShow: 1,
	// 				infinite:     true
	// 			}
	// 		}
	// 	]
	// } );

	settings.responsive = [
		{
			breakpoint: 769,
			settings: {
				slidesToShow: settings.slidesToShow,
				slidesToScroll: settings.slidesToScroll
			}
		},
		{
			breakpoint: 481,
			settings: {
				slidesToShow: 1,
				slidesToScroll: 1
			}
		}
	];

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
