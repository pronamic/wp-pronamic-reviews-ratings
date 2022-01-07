/**
 * WordPress dependencies.
 */
import ServerSideRender from '@wordpress/server-side-render';

const RatingsEdit = () => (
	<ServerSideRender block="pronamic-reviews-ratings/ratings" />
);

export default RatingsEdit;