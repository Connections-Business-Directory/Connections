/**
 * External dependencies
 */
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { withSelect } from '@wordpress/data';
import { Spinner, TreeSelect } from '@wordpress/components';

/**
 * Internal dependencies
 */
// import { TreeSelect } from '@wordpress/components';

// Import components
import { buildTermsTree } from '../../utils/terms';

function PageSelect( {
	                     postType,
	                     postTypeMeta,
	                     label,
	                     value,
	                     noOptionLabel,
	                     options,
	                     onChange,
	                     ...props
                     } ) {

	if ( null === options ) {
		return (
			<p>
				<Spinner />
				{ __( 'Loading Data', 'connections' ) }
			</p>
		);
	}

	// const { getPostType } = select( 'core' );

	// const postTypeMeta   = getPostType( postType );
	const isHierarchical = get( postTypeMeta, [ 'hierarchical' ], false );
	const pageItems      = options || [];
	let   pagesTree      = [];

	if ( ! pageItems.length ) {

		return null;
	}

	if ( isHierarchical ) {

		pagesTree = buildTermsTree( pageItems.map( ( item ) => ({
			id:     item.id,
			parent: item.parent,
			name:   item.title.raw ? item.title.raw : `#${item.id} (${__( 'no title' )})`,
		}) ) );

	} else {

		pagesTree = pageItems.map( ( item ) => ({
			id:     item.id,
			name:   item.title.raw ? item.title.raw : `#${item.id} (${__( 'no title' )})`,
		}) );
	}

	return (
		<TreeSelect
			className="connections-directory--attributes__home_id"
			label={label}
			noOptionLabel={noOptionLabel}
			tree={pagesTree}
			selectedId={value}
			onChange={onChange}
			{...props}
		/>
	);
}

const applyWithSelect = withSelect( ( select, ownProps ) => {

	const { getEntityRecords, getPostType } = select( 'core' );
	const { getCurrentPostId }              = select( 'core/editor' );

	const postType     = typeof ownProps.postType === 'undefined' ? 'page' : ownProps.postType;
	const postTypeMeta = getPostType( postType );
	const postId       = getCurrentPostId();

	const query = {
		per_page:       -1,
		exclude:        postId,
		parent_exclude: postId,
		orderby:        'title',
		order:          'asc',
		_fields:        'id,parent,title',
	};

	return {
		options: getEntityRecords( 'postType', postType, query ),
		postTypeMeta
	};
} );

// const renderedSelect = applyWithSelect( PageSelect );

// export { renderedSelect as PageSelect };
export default applyWithSelect( PageSelect );
