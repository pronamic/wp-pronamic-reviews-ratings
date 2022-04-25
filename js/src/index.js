/**
 * WordPress dependencies.
 */
import { registerBlockType } from '@wordpress/blocks';

/**
 * Internal dependencies.
 */
import * as ReviewAuthor from './review-author';
import * as ReviewsRatingsValue from './rating-value';
import * as ReviewsRatingsCount from './rating-count';
import * as ReviewsRatingsStars from './rating-stars';
import * as ReviewsRatingsSubStars from './rating-sub-stars';
import * as ReviewsRatingsReviewedObjectTitle from './rating-reviewed-object-title';
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
	ReviewAuthor,
	ReviewsRatingsValue,
	ReviewsRatingsCount,
	ReviewsRatingsStars,
    ReviewsRatingsSubStars,
	ReviewsRatings,
    ReviewsRatingsReviewedObjectTitle
].forEach( registerBlock );
