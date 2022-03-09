/**
 * WordPress dependencies.
 */
import ServerSideRender from '@wordpress/server-side-render';

const ReviewAuthorEdit = () => (
	<ServerSideRender block="pronamic-reviews-ratings/review-author" />
);

export default ReviewAuthorEdit;