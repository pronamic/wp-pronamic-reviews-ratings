/**
 * WordPress dependencies.
 */
import ServerSideRender from '@wordpress/server-side-render';

const RatingStarsEdit = () => (
	<ServerSideRender block="pronamic-reviews-ratings/rating-stars" />
);

export default RatingStarsEdit;