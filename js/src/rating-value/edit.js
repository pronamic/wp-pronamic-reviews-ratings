/**
 * WordPress dependencies.
 */
import ServerSideRender from '@wordpress/server-side-render';

const RatingValueEdit = () => (
	<ServerSideRender block="pronamic-reviews-ratings/rating-value" />
);

export default RatingValueEdit;