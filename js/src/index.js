/**
 * WordPress dependencies.
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies.
 */
import * as ReviewsRatingsValue from './rating-value';
import * as ReviewsRatingsCount from './rating-count';
import * as ReviewsRatingsStars from './rating-stars';
import * as ReviewsRatings from './ratings';

/**
 * Register block.
 *
 * @param {Object} block Block to register.
 * @return void
 */
const registerBlock = ( block ) => {
	if ( ! block ) {
		return;
	}

	const { name, settings } = block;

	registerBlockType( name, settings );
};

// Register blocks.
[
	ReviewsRatingsValue,
	ReviewsRatingsCount,
	ReviewsRatingsStars,
	ReviewsRatings
].forEach( registerBlock );
