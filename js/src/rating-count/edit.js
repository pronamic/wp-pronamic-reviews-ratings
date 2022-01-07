/**
 * WordPress dependencies.
 */
import ServerSideRender from '@wordpress/server-side-render';

const RatingCountEdit = () => (
	<ServerSideRender block="pronamic-reviews-ratings/rating-count" />
);

export default RatingCountEdit;