/**
 * WordPress dependencies
 */
import { addQueryArgs } from '@wordpress/url';
import { registerStore } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

const actions = {
	receiveItems( terms ) {
		return {
			type: 'RECEIVE_ITEMS',
			terms
		};
	},
	fetchFromAPI( path, query ) {
		return {
			type: 'FETCH_FROM_API',
			path,
			query
		};
	},
};

const DEFAULT_STATE = {
	terms: [],
};

registerStore( 'connections-directory/categories', {

	reducer( state = DEFAULT_STATE, action ) {

		// console.log( 'state', state );
		// console.log( 'action', action );

		switch ( action.type ) {

			case 'RECEIVE_ITEMS':

				return {
					...state,
					terms: action.terms,
				};
		}

		return state;
	},

	actions,

	selectors: {
		getCategories( state, query ) {

			// console.log( 'state', state );
			// console.log( 'query', query );

			return state.terms;
		},
	},

	controls: {
		FETCH_FROM_API( action ) {
			return apiFetch( {
				path: addQueryArgs( action.path, action.query ),
			} );
		},
	},

	resolvers: {
		* getCategories( query = {} ) {

			console.log( query );

			const terms = yield actions.fetchFromAPI( `/cn-api/v1/category/`, query );

			yield actions.receiveItems( terms, query );
		},
	},
});
