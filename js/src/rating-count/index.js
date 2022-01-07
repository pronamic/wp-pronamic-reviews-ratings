/**
 * WordPress dependencies.
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies.
 */
import edit from './edit';
import metadata from './block';
import save from './save';

const { attributes, category, name } = metadata;

export { metadata, name };

// Settings.
export const settings = {
	title: __( 'Rating count', 'pronamic_reviews_ratings' ),
	description: __( 'Displays rating count.', 'pronamic_reviews_ratings' ),
	category,
	attributes,
	edit,
	save
};