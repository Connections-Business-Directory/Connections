/**
 * External dependencies
 */
import { groupBy } from 'lodash';

/**
 * Returns terms in a tree form.
 *
 * Copies from WP core since it does not appear to be available in the global wp object.
 * @link https://github.com/WordPress/gutenberg/blob/master/packages/editor/src/utils/terms.js
 *
 * @param {Array} flatTerms  Array of terms in flat format.
 *
 * @return {Array} Array of terms in tree format.
 */
export function buildTermsTree( flatTerms ) {
	const termsByParent = groupBy( flatTerms, 'parent' );
	const fillWithChildren = ( terms ) => {
		return terms.map( ( term ) => {
			const children = termsByParent[ term.id ];
			return {
				...term,
				children: children && children.length ? fillWithChildren( children ) : [],
			};
		} );
	};

	return fillWithChildren( termsByParent[ '0' ] || [] );
}
