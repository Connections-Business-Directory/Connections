/**
 * External dependencies
 */
import { unescape as unescapeString, without, find, invoke, startCase } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, _x, _n, sprintf } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { CheckboxControl, withSpokenMessages, Spinner } from '@wordpress/components';
import { withInstanceId, compose } from '@wordpress/compose';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

/**
 * Internal dependencies
 */
import { buildTermsTree } from '../../utils/terms';

/**
 * Module Constants
 */
const DEFAULT_QUERY = {
	per_page: -1,
	orderby:  'name',
	order:    'asc',
	_fields:  'id,name,parent',
};

const MIN_TERMS_COUNT_FOR_FILTER = 8;

class HierarchicalTermSelector extends Component {
	constructor( ) {
		super( ...arguments );
		this.findTerm = this.findTerm.bind( this );
		this.onChange = this.onChange.bind( this );
		this.setFilterValue = this.setFilterValue.bind( this );
		this.sortBySelected = this.sortBySelected.bind( this );
		this.state = {
			loading: true,
			availableTermsTree: [],
			availableTerms: [],
			filterValue: '',
			filteredTermsTree: [],
		};
	}

	onChange( termId ) {

		const { onChange, terms } = this.props;

		// const termId   = parseInt( event.target.value, 10 );
		const hasTerm  = terms.indexOf( termId ) !== -1;
		const newTerms = hasTerm ? without( terms, termId ) : [ ...terms, termId ];

		onChange( newTerms );
	}

	findTerm( terms, parent, name ) {

		return find( terms, ( term ) => {

			return ( ( ! term.parent && ! parent ) || parseInt( term.parent ) === parseInt( parent ) ) &&
				term.name.toLowerCase() === name.toLowerCase();
		} );
	}

	componentDidMount() {
		this.fetchTerms();
	}

	componentWillUnmount() {
		invoke( this.fetchRequest, [ 'abort' ] );
	}

	fetchTerms() {

		const { taxonomy } = this.props;

		if ( ! taxonomy ) {

			return;
		}

		this.fetchRequest = apiFetch( {
			path: addQueryArgs( `/cn-api/v1/${ taxonomy }/`, DEFAULT_QUERY ),
		} );

		this.fetchRequest.then(
			( terms ) => { // resolve
				const availableTermsTree = this.sortBySelected( buildTermsTree( terms ) );

				this.fetchRequest = null;
				this.setState( {
					loading: false,
					availableTermsTree,
					availableTerms: terms,
				} );
			},
			( xhr ) => { // reject
				if ( xhr.statusText === 'abort' ) {
					return;
				}
				this.fetchRequest = null;
				this.setState( {
					loading: false,
				} );
			}
		);
	}

	sortBySelected( termsTree ) {

		const { terms } = this.props;

		const treeHasSelection = ( termTree ) => {

			if ( terms.indexOf( termTree.id ) !== -1 ) {
				return true;
			}

			if ( undefined === termTree.children ) {
				return false;
			}

			const anyChildIsSelected = termTree.children.map( treeHasSelection ).filter( ( child ) => child ).length > 0;

			if ( anyChildIsSelected ) {
				return true;
			}

			return false;
		};

		const termOrChildIsSelected = ( termA, termB ) => {

			const termASelected = treeHasSelection( termA );
			const termBSelected = treeHasSelection( termB );

			if ( termASelected === termBSelected ) {
				return 0;
			}

			if ( termASelected && ! termBSelected ) {
				return -1;
			}

			if ( ! termASelected && termBSelected ) {
				return 1;
			}

			return 0;
		};

		termsTree.sort( termOrChildIsSelected );

		return termsTree;
	}

	setFilterValue( event ) {

		const { availableTermsTree } = this.state;
		const filterValue = event.target.value;
		const filteredTermsTree = availableTermsTree.map( this.getFilterMatcher( filterValue ) ).filter( ( term ) => term );

		const getResultCount = ( terms ) => {
			let count = 0;
			for ( let i = 0; i < terms.length; i++ ) {
				count++;
				if ( undefined !== terms[ i ].children ) {
					count += getResultCount( terms[ i ].children );
				}
			}
			return count;
		};

		this.setState(
			{
				filterValue,
				filteredTermsTree,
			}
		);

		const resultCount = getResultCount( filteredTermsTree );

		const resultsFoundMessage = sprintf(
			_n( '%d result found.', '%d results found.', resultCount, 'connections' ),
			resultCount
		);

		this.props.debouncedSpeak( resultsFoundMessage, 'assertive' );
	}

	getFilterMatcher( filterValue ) {

		const matchTermsForFilter = ( originalTerm ) => {

			if ( '' === filterValue ) {
				return originalTerm;
			}

			// Shallow clone, because we'll be filtering the term's children and
			// don't want to modify the original term.
			const term = { ...originalTerm };

			// Map and filter the children, recursive so we deal with grandchildren
			// and any deeper levels.
			if ( term.children.length > 0 ) {
				term.children = term.children.map( matchTermsForFilter ).filter( ( child ) => child );
			}

			// If the term's name contains the filterValue, or it has children
			// (i.e. some child matched at some point in the tree) then return it.
			if ( -1 !== term.name.toLowerCase().indexOf( filterValue ) || term.children.length > 0 ) {
				return term;
			}

			// Otherwise, return false. After mapping, the list of terms will need
			// to have false values filtered out.
			return false;
		};

		return matchTermsForFilter;
	}

	renderTerms( renderedTerms ) {

		const { terms = [] } = this.props;

		return renderedTerms.map( ( term ) => {

			const id = `editor-post-taxonomies-hierarchical-term-${ term.id }`;

			return (
				<div key={ term.id } className="editor-post-taxonomies__hierarchical-terms-choice cbd__hierarchical-terms-choice">

					<CheckboxControl
						checked={ terms.indexOf( term.id ) !== -1 }
						onChange={ () => {
							const termId = parseInt( term.id, 10 );
							this.onChange( termId );
						} }
						label={ unescapeString( term.name ) }
					/>
					{ !! term.children.length && (
						<div className="editor-post-taxonomies__hierarchical-terms-subchoices">
							{ this.renderTerms( term.children ) }
						</div>
					) }

				</div>
			);
		} );
	}

	render() {

		const {
			      taxonomy,
			      instanceId,
		      } = this.props;
		const {
			      availableTermsTree,
			      availableTerms,
			      filteredTermsTree,
			      loading,
			      filterValue
		      } = this.state;

		if ( loading ) {
			return (
				<p>
					<Spinner />
					{ __( 'Loading Data', 'connections' ) }
				</p>
			);
		}

		const filterInputId = `editor-post-taxonomies__hierarchical-terms-filter-${ instanceId }`;
		const filterLabel = sprintf(
			_x( 'Search %s', 'term', 'connections' ),
			startCase( taxonomy )
		);
		const groupLabel = sprintf(
			_x( 'Available %s', 'term', 'connections' ),
			startCase( taxonomy )
		);
		const showFilter = availableTerms.length >= MIN_TERMS_COUNT_FOR_FILTER;

		return [
			showFilter && <label
				key="filter-label"
				htmlFor={ filterInputId }>
				{ filterLabel }
			</label>,
			showFilter && <input
				type="search"
				id={ filterInputId }
				value={ filterValue }
				onChange={ this.setFilterValue }
				className="editor-post-taxonomies__hierarchical-terms-filter"
				key="term-filter-input"
			/>,
			<div
				className="editor-post-taxonomies__hierarchical-terms-list"
				key="term-list"
				tabIndex="0"
				role="group"
				aria-label={ groupLabel }
			>
				{ this.renderTerms( '' !== filterValue ? filteredTermsTree : availableTermsTree ) }
			</div>,
		];

	}
}

export default compose( [
	// withSelect( ( select, { slug } ) => {
	// 	// const { getCurrentPost } = select( 'core/editor' );
	// 	// const { getTaxonomy } = select( 'core' );
	// 	// const taxonomy = getTaxonomy( slug );
	// 	return {
	// 		// hasCreateAction: taxonomy ? get( getCurrentPost(), [ '_links', 'wp:action-create-' + taxonomy.rest_base ], false ) : false,
	// 		// hasAssignAction: taxonomy ? get( getCurrentPost(), [ '_links', 'wp:action-assign-' + taxonomy.rest_base ], false ) : false,
	// 		// terms: select( 'connections-directory/categories' ).getCategories( {per_page: -1} ),
	// 		// terms: [],
	// 		// taxonomy,
	// 	};
	// } ),
	// withDispatch( ( dispatch ) => ( {
	// 	onUpdateTerms( terms, restBase ) {
	// 		dispatch( 'connections-directory/categories' ).receiveItems( terms );
	// 	},
	// } ) ),
	withSpokenMessages,
	withInstanceId,
	// withFilters( 'editor.PostTaxonomyType' ),
] )( HierarchicalTermSelector );
