/**
 * External dependencies
 */
import {
	find,
	invoke,
	isEmpty,
	map,
	throttle,
	unescape as unescapeString,
	uniqBy,
} from 'lodash';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { FormTokenField } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Module constants
 */
const DEFAULT_QUERY = {
	per_page: -1,
};

const MAX_TERMS_SUGGESTIONS = 20;

const isSameTermName = ( termA, termB ) => termA.toLowerCase() === termB.toLowerCase();

class FilterTagSelector extends Component {

	constructor() {

		super( ...arguments );

		this.unescapeTerm = this.unescapeTerm.bind( this );
		this.unescapeTerms = this.unescapeTerms.bind( this );
		this.onChange = this.onChange.bind( this );
		this.searchTerms = throttle( this.searchTerms.bind( this ), 300 );

		this.state = {
			loading: false,
			availableTerms: [],
			selectedTerms: [],
		};
	}

	/**
	 * Returns a term object with name unescaped.
	 * The unescape of the name property is done using lodash unescape function.
	 *
	 * @param {Object} term The term object to unescape.
	 *
	 * @return {Object} Term object with name property unescaped.
	 */
	unescapeTerm( term ) {

		const { renderField } = this.props;

		return {
			...term,
			unescapeName: unescapeString( term[ renderField ] ),
		};
	};

	/**
	 * Returns an array of term objects with names unescaped.
	 * The unescape of each term is performed using the unescapeTerm function.
	 *
	 * @param {Object[]} terms Array of term objects to unescape.
	 *
	 * @return {Object[]} Array of therm objects unescaped.
	 */
	unescapeTerms( terms ) {

		return map( terms, this.unescapeTerm );
	};

	componentDidMount() {

		if ( ! isEmpty( this.props.terms ) ) {

			this.setState( { loading: false } );

			this.initRequest = this.fetchTerms( {
				search: this.props.terms.join( ',' ),
				per_page: -1,
			} );

			this.initRequest.then(
				() => {
					this.setState( { loading: false } );
				},
				( xhr ) => {
					if ( xhr.statusText === 'abort' ) {
						return;
					}
					this.setState( {
						loading: false,
					} );
				}
			);
		}
	}

	componentWillUnmount() {

		invoke( this.initRequest, [ 'abort' ] );
		invoke( this.searchRequest, [ 'abort' ] );
	}

	componentDidUpdate( prevProps ) {

		if ( prevProps.terms !== this.props.terms ) {

			this.updateSelectedTerms( this.props.terms );
		}
	}

	fetchTerms( params = {} ) {

		const { getFields, type, renderField } = this.props;
		const query = { ...DEFAULT_QUERY, ...params, ...{_fields: getFields} };
		const request = apiFetch( {
			path: addQueryArgs( `/cn-api/v1/autocomplete/${ type }`, query ),
		} );

		request.then( this.unescapeTerms ).then( ( terms ) => {

			this.setState( ( state ) => ( {
				availableTerms: state.availableTerms.concat(
					terms.filter( ( term ) => ! find( state.availableTerms, ( availableTerm ) => availableTerm[ renderField ] === term[ renderField ] ) )
				),
			} ) );

			this.updateSelectedTerms( this.props.terms );
		} );

		return request;
	}

	updateSelectedTerms( terms = [] ) {

		const { renderField, returnField } = this.props;

		const selectedTerms = terms.reduce( ( result, termId ) => {

			// console.log( termId );

			const termObject = find( this.state.availableTerms, ( term ) => term[ returnField ] === termId );

			if ( termObject ) {

				result.push( termObject[ renderField ] );
			}

			return result;
		}, [] );

		// console.log( 'this.state.availableTerms=', this.state.availableTerms );
		// console.log( 'updateSelectedTerms::selectedTerms=', selectedTerms );

		this.setState( {
			selectedTerms,
		} );
	}

	onChange( termNames ) {

		const { onChange, renderField, returnField } = this.props;

		// console.log( 'onChange::onChange=', termNames );

		const renderFieldToReturnField = ( names, availableTerms ) => {
			return names
				.map( ( termName ) =>
					find( availableTerms, ( term ) => isSameTermName( term[ renderField ], termName ) )[ returnField ]
				);
		};

		// console.log( 'termNames:', termNames );

		const uniqueTerms = uniqBy( termNames, ( term ) => term.toLowerCase() );

		const addTerm = uniqueTerms.filter( ( termName ) =>
			find( this.state.availableTerms, ( term ) => isSameTermName( term[ renderField ], termName ) )
		);

		// console.log( 'addTerm:', addTerm );

		const selectedTermIDs = renderFieldToReturnField( addTerm, this.state.availableTerms );

		// console.log( 'selectedTermIDs:', selectedTermIDs );

		onChange( selectedTermIDs );
	}

	searchTerms( search = '' ) {

		invoke( this.searchRequest, [ 'abort' ] );
		this.searchRequest = this.fetchTerms( { search } );
	}

	render() {

		const {
			      label,
			      messages,
		      } = this.props;

		const { loading, availableTerms, selectedTerms } = this.state;
		const termNames = availableTerms.map( ( term ) => term.unescapeName );

		return (
			<FormTokenField
				value={ selectedTerms }
				suggestions={ termNames }
				onChange={ this.onChange }
				onInputChange={ this.searchTerms }
				maxSuggestions={ MAX_TERMS_SUGGESTIONS }
				disabled={ loading }
				label={ label }
				messages={ messages }
			/>
		);
	}
}

export default FilterTagSelector;
