/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { Spinner } from '@wordpress/components';
import { Component, Fragment, render } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import {
	EntryImage,
	EntryName,
	EntryTitle,
} from "@Connections-Directory/components";

const ENDPOINT = '/cn-api/v1/recently_viewed/';

class RecentlyViewed extends Component {

	/**
	 * Constructor for the Carousel Component.
	 *
	 * Sets up state, and creates bindings for functions.
	 *
	 * @param {object} props Component properties.
	 */
	constructor( props ) {

		super( props );

		this.prepareQueryArgs = this.prepareQueryArgs.bind( this );
		this.fetchAPI = this.fetchAPI.bind( this );
		this.fetchEntries = this.fetchEntries.bind( this );

		this.state = {
			queryResults: [],
			isLoading:    true,
		};
	}

	componentDidMount() {
		// console.log( this.props.name, ': componentDidMount()' );
		// console.log( getRecentlyViewed() );

		// const args = {
		// 	recent: getRecentlyViewed(),
		// }

		this.fetchEntries();
	}

	/**
	 * @param {object} args
	 */
	prepareQueryArgs( args ) {

		let query = {};

		// console.log( query );
		// console.log( this.props );

		query['exclude'] = this.props.exclude;

		query['_images'] = [
			{ type: 'logo', size: 'custom', width: 600, height: 520, zc: 2 },
			{ type: 'photo', size: 'custom', width: 600, height: 520, zc: 2 }
		];

		query['per_page'] = this.props.limit;

		query['_fields'] = 'type,fn.rendered,job_title.rendered,phone,email,social,excerpt.rendered,images,link';

		query = { ...query, ...args };

		return query;
	}

	fetchAPI( query ) {

		const path = addQueryArgs(
			ENDPOINT,
			{
				...query,
				context: 'view',
			}
		);

		// console.log( 'Fetching... ', query );

		return apiFetch( { path: path } );
	}

	/**
	 * @param {object} args
	 */
	fetchEntries( args = {} ) {

		this.fetchAPI( this.prepareQueryArgs( args ) ).then( ( results ) => {

			this.setState( { isLoading: false, queryResults: results } );
		} );
	};

	render() {

		let entries = this.state.queryResults;

		const hasEntries = Array.isArray( entries ) && entries.length;

		// console.log( this.props );

		if ( !hasEntries ) {

			return (
				<Fragment>
					<div>
						{ this.state.isLoading ?
							<p>{ __( 'Loading...', 'connections' ) } <Spinner /></p> :
							<p>{ __( 'No recently viewed directory entries.', 'connections' ) }</p>
						}
					</div>
				</Fragment>
			)

		} else {

			const items = entries
				// No need to display the Entry currently being viewed.
				.filter( ( entry ) => document.URL !== entry.link )
				// Render the Entry name as a permalink within `li` tags.
				.map( ( entry, i ) => {
					// console.log( entry );

					return (
						<li key={ i }>
							<div className="cn-recently-viewed-image">
								<EntryImage entry={ entry } type={ 'organization' === entry.type ? 'logo' : 'photo' } size='custom' usePlaceholder={true}/>
							</div>
							<div className="cn-recently-viewed-details">
								<EntryName entry={ entry } tag='span' asPermalink={ true } />
								<EntryTitle entry={ entry } />
							</div>
						</li>
						)
				} );

			return ( <ul>{items}</ul> );
		}

	};
}

const recentlyViewed = document.querySelectorAll( '.cn-recently-viewed' );

recentlyViewed.forEach( instance => {

	let limit = instance.getAttribute( 'data-limit' );
	let exclude = instance.getAttribute( 'data-exclude' );

	render( <RecentlyViewed limit={limit} exclude={exclude}/>, instance );
} );
