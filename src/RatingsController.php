<?php
/**
 * Ratings controller
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings;

/**
 * Ratings controller
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   2.0.0
 */
class RatingsController {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Construct comments module.
	 *
	 * @param Plugin $plugin Plugin.
	 * @return void
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		\add_action( 'save_post', array( $this, 'update_comments_rating' ) );
		\add_action( 'save_post_pronamic_review', array( $this, 'update_review_rating_score' ) );
		\add_action( 'save_post_pronamic_review', array( $this, 'update_object_ratings' ), 15 );
		\add_action( 'trash_post_pronamic_review', array( $this, 'update_object_ratings' ), 15 );
		\add_action( 'untrash_post_pronamic_review', array( $this, 'update_object_ratings' ), 15 );

		// Filters.
		\add_filter( 'posts_clauses', array( $this, 'posts_clauses' ), 10, 2 );
		\add_filter( 'the_content', array( $this, 'object_content_ratings' ) );
		\add_filter( 'render_block_data', array( $this, 'render_query_block_data' ), 10, 2 );
		\add_filter( 'pre_get_posts', array( $this, 'pre_get_posts_pronamic_review_object' ) );
	}

	/**
	 * Update review rating score based on ratings.
	 *
	 * @param int $post_id Review post ID.
	 * @return void
	 */
	public function update_review_rating_score( $post_id ) {
		$object_post_id = \get_post_meta( $post_id, '_pronamic_review_object_post_id', true );

		$rating_types = \pronamic_get_rating_types( \get_post_type( $object_post_id ) );

		$ratings = array();

		foreach ( $rating_types as $type ) {
			$value = \get_post_meta( $post_id, '_pronamic_rating_value_' . $type['name'], true );

			if ( ! empty( $value ) ) {
				$ratings[] = $value;
			}
		}

		$num_ratings = count( $ratings );

		if ( 0 === $num_ratings ) {
			\delete_post_meta( $post_id, '_pronamic_rating' );

			return;
		}

		$rating = array_sum( $ratings ) / $num_ratings;

		\update_post_meta( $post_id, '_pronamic_rating', $rating );
	}

	/**
	 * Create review post.
	 *
	 * @param array<string, mixed> $args Post arguments.
	 * @return int|\WP_Error
	 */
	public function create_review_post( $args ) {
		$defaults = array(
			'post_title'     => null,
			'post_name'      => null,
			'post_content'   => null,
			'post_status'    => 'draft',
			'post_type'      => 'pronamic_review',
			'comment_status' => 'closed',
		);

		$args = wp_parse_args( $args, $defaults );

		return wp_insert_post( $args, true );
	}

	/**
	 * Update object ratings when a review is saved.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function update_object_ratings( $post_id ) {
		global $wpdb;

		// Check object post ID.
		$object_post_id = \get_post_meta( $post_id, '_pronamic_review_object_post_id', true );

		if ( empty( $object_post_id ) ) {
			return;
		}

		// Check post type support.
		$post_type = \get_post_type( $object_post_id );

		if ( ! \post_type_supports( $post_type, 'pronamic_ratings' ) ) {
			return;
		}

		// Update post.
		$query = $wpdb->prepare(
			"
				SELECT
					$wpdb->postmeta.meta_key,
					COUNT( $wpdb->postmeta.meta_key ) as rating_count,
					SUM( $wpdb->postmeta.meta_value) / COUNT( $wpdb->postmeta.meta_key ) as rating_value
				FROM $wpdb->postmeta
					LEFT JOIN $wpdb->posts ON $wpdb->posts.ID = $wpdb->postmeta.post_id
					LEFT JOIN $wpdb->postmeta AS postmeta_object_id ON postmeta_object_id.post_id = $wpdb->posts.ID
				WHERE
					$wpdb->postmeta.meta_key LIKE %s
						AND
					$wpdb->posts.post_status = %s
						AND
					postmeta_object_id.meta_key = %s
						AND
					postmeta_object_id.meta_value = %d
				GROUP BY
					$wpdb->postmeta.meta_key
				;",
			'_pronamic_rating_value_%',
			'publish',
			'_pronamic_review_object_post_id',
			$object_post_id
		);

		$results = $wpdb->get_results(
			$query
		);

		if ( empty( $results ) ) {
			\update_post_meta( $object_post_id, '_pronamic_rating_count', 0 );
			\update_post_meta( $object_post_id, '_pronamic_rating_value', 0 );

			$rating_types = \pronamic_get_rating_types( $post_type );

			foreach ( $rating_types as $type ) {
				\delete_post_meta( $object_post_id, '_pronamic_rating_count_' . $type['name'] );
				\delete_post_meta( $object_post_id, '_pronamic_rating_value_' . $type['name'] );
			}
		} else {
			$rating_count = 0;
			$rating_value = 0;

			foreach ( $results as $result ) {
				$meta_key_value = $result->meta_key;
				$meta_key_count = \str_replace( '_pronamic_rating_value_', '_pronamic_rating_count_', $result->meta_key );

				\update_post_meta( $object_post_id, $meta_key_value, $result->rating_value );
				\update_post_meta( $object_post_id, $meta_key_count, $result->rating_count );

				$rating_count += $result->rating_count;
				$rating_value += $result->rating_value;
			}

			$rating_count = $rating_count / count( $results );

			\update_post_meta( $object_post_id, '_pronamic_rating_count', $rating_count );
			\update_post_meta( $object_post_id, '_pronamic_rating_value', $rating_value / count( $results ) );
		}

		// Sync ratings to custom table.
		//$this->sync_rating_to_table( $post_id );
	}

	/**
	 * Save post.
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function update_comments_rating( $post_id ) {
		global $wpdb;

		// Check post type support.
		$post_type = \get_post_type( $post_id );

		if ( ! \post_type_supports( $post_type, 'pronamic_ratings' ) ) {
			return;
		}

		// Update post.
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"
			SELECT
				meta_key,
				COUNT(meta_value) AS rating_count,
				SUM(meta_value) / COUNT( meta_value ) AS rating_value
			FROM
				$wpdb->commentmeta
					LEFT JOIN
				$wpdb->comments
						ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
			WHERE
				meta_key LIKE %s
					AND
				comment_post_ID = %d
					AND
				comment_approved = '1'
					AND
				meta_value > 0
			GROUP BY
				meta_key
			;",
				'_pronamic_rating_value%',
				$post_id
			)
		);

		if ( empty( $results ) ) {
			\update_post_meta( $post_id, '_pronamic_rating_value', 0 );
			\update_post_meta( $post_id, '_pronamic_rating_count', 0 );
		} else {
			foreach ( $results as $result ) {
				$meta_key_value = $result->meta_key;
				$meta_key_count = str_replace( '_pronamic_rating_value', '_pronamic_rating_count', $meta_key_value );

				\update_post_meta( $post_id, $meta_key_value, $result->rating_value );
				\update_post_meta( $post_id, $meta_key_count, $result->rating_count );
			}
		}

		// Sync ratings to custom table.
		$this->sync_rating_to_table( $post_id );
	}

	/**
	 * Sync ratings to custom table.
	 *
	 * @param int $post_id Post ID.
	 * @return int|string|null
	 */
	function sync_rating_to_table( $post_id ) {
		// Sync locations.
		global $wpdb;

		$rating_id = $wpdb->get_var( $wpdb->prepare( "SELECT rating_id FROM $wpdb->pronamic_post_ratings WHERE post_id = %d;", $post_id ) );

		$format = array(
			'post_id'      => '%d',
			'rating_value' => '%f',
			'rating_count' => '%d',
		);

		$data = array(
			'post_id'      => $post_id,
			'rating_value' => \get_post_meta( $post_id, '_pronamic_rating_value', true ),
			'rating_count' => \get_post_meta( $post_id, '_pronamic_rating_count', true ),
		);

		if ( $rating_id ) {
			$result = $wpdb->update( $wpdb->pronamic_post_ratings, $data, array( 'rating_id' => $rating_id ), $format );
		} else {
			$result = $wpdb->insert( $wpdb->pronamic_post_ratings, $data, $format );

			if ( $result ) {
				$rating_id = $wpdb->insert_id;
			}
		}

		return $rating_id;
	}

	/**
	 * Posts clauses
	 *
	 * @param array     $pieces Query pieces.
	 * @param \WP_Query $query  WordPress query.
	 * @return array
	 * @link http://codex.wordpress.org/WordPress_Query_Vars
	 * @link http://codex.wordpress.org/Custom_Queries
	 */
	function posts_clauses( $pieces, $query ) {
		global $wpdb;

		// Fields.
		$fields = '';

		if ( '' == $query->get( 'fields' ) ) {
			$fields = ',
			rating.rating_id AS rating_id,
			rating.rating_value AS rating_value,
			rating.rating_count AS rating_count
			';
		}

		// Join.
		$join = "
		LEFT JOIN
			$wpdb->pronamic_post_ratings AS rating
				ON $wpdb->posts.ID = rating.post_id
		";

		// Order by.
		$orderby = $pieces['orderby'];

		// Order.
		$order = $query->get( 'order' );

		switch ( $query->get( 'orderby' ) ) {
			case 'rating':
				$orderby = 'rating_value ' . $order;

				break;
		}

		// Pieces.
		$pieces['fields'] .= $fields;
		$pieces['join']   .= $join;

		$pieces['orderby'] = $orderby;

		return $pieces;
	}

	/**
	 * The object ratings content.
	 *
	 * @param string $content Object post content.
	 * @return string
	 */
	public function object_content_ratings( $content ) {
		if ( ! \post_type_supports( \get_post_type(), 'pronamic_ratings' ) ) {
			return $content;
		}

		\ob_start();

		require __DIR__ . '/../views/object-ratings.php';

		$ratings_content = \ob_get_clean();

		return $content . \PHP_EOL . $ratings_content;
	}

	/**
	 * Render Query block data.
	 *
	 * @param array $parsed_block Parsed block.
	 * @return array
	 */
	public function render_query_block_data( $parsed_block ) {
		// Check Query block.
		if ( ! \array_key_exists( 'blockName', $parsed_block ) ) {
			return $parsed_block;
		}

		if ( 'core/query' !== $parsed_block['blockName'] ) {
			return $parsed_block;
		}

		// Check query post type.
		if ( ! isset( $parsed_block['attrs']['query']['postType'] ) ) {
			return $parsed_block;
		}

		if ( 'pronamic_review' !== $parsed_block['attrs']['query']['postType'] ) {
			return $parsed_block;
		}

		// Determine post type ratings support.
		$object_post_id = \get_the_ID();

		$post_type = \get_post_type( $object_post_id );

		if ( ! \post_type_supports( $post_type, 'pronamic_ratings' ) ) {
			return $parsed_block;
		}

		// Set search query.
		if ( ! \array_key_exists( 'search', $parsed_block['attrs']['query'] ) ) {
			$parsed_block['attrs']['query']['search'] = '';
		}

		$parsed_block['attrs']['query']['search'] .= sprintf( '[pronamic_review_object_post_id=%d]', $object_post_id );

		return $parsed_block;
	}

	/**
	 * Set meta query from object post ID in search query.
	 *
	 * @param \WP_Query $query Query.
	 * @return void
	 */
	public function pre_get_posts_pronamic_review_object( \WP_Query $query ) {
		// Check search.
		if ( ! $query->is_search() ) {
			return;
		}

		// Check search query.
		$search = $query->get( 's' );

		$pattern = '/\[pronamic_review_object_post_id=(?<object_post_id>\d+)\]/';

		\preg_match( $pattern, $search, $matches );

		if ( ! \array_key_exists( 'object_post_id', $matches ) ) {
			return;
		}

		// Cleanup search query.
		$query->set( 's', \preg_replace( $pattern, '', $search ) );

		// Add meta query for review object ID.
		$meta_query = $query->get( 'meta_query' );

		if ( ! \is_array( $meta_query ) ) {
			$meta_query = array();
		}

		$meta_query[] = array(
			'key' => '_pronamic_review_object_post_id',
			'value' => $matches['object_post_id'],
		);

		$query->set( 'meta_query', $meta_query );
	}
}
