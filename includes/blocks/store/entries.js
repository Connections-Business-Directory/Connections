/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { registerStore } from '@wordpress/data';
import { addQueryArgs } from '@wordpress/url';

const ENDPOINT = '/cn-api/v1/entry/';

const actions = {
	addEntities( entities ) {

		// console.log( 'actions=>addEntities::entities', entities );

		return {
			type: 'ADD_ENTITIES',
			entities,
		};
	},
	receiveEntityRecords( path ) {

		// console.log( 'actions=>receiveEntityRecords::path', path );

		return {
			type: 'RECEIVE_ENTITY_RECORDS',
			path,
		};
	},
};

const store = registerStore( 'connections-directory/entries', {
	reducer( state = { entities: {}, entityType:'all' }, action ) {

		// console.log( 'reducer::state', state );
		// console.log( 'reducer::action', action );

		switch ( action.type ) {
			case 'ADD_ENTITIES':
				return {
					...state,
					entities: action.entities,
				};

		}

		return state;
	},

	actions,

	selectors: {
		getEntityRecords( state, query ) {

			// console.log( 'selectors=>getEntityRecords::state', state );
			// console.log( 'selectors=>getEntityRecords::query', query );

			const { entities } = state;

			return entities;
		},
	},

	controls: {
		RECEIVE_ENTITY_RECORDS( action ) {

			// console.log( 'controls=>RECEIVE_ENTITY_RECORDS::query', action );

			return apiFetch( { path: action.path } );
		},
	},

	resolvers: {
		* getEntityRecords( query = {} ) {

			// console.log( 'resolvers=>getEntityRecords::query', query );

			const path = addQueryArgs(
				ENDPOINT,
				{
					...query,
					context: 'edit',
				}
			);

			const entities = yield actions.receiveEntityRecords( path );
			return actions.addEntities( entities );
		},
	},
} );

export default store;
