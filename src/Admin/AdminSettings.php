<?php
/**
 * Admin settings.
 *
 * @author    Pronamic <info@pronamic.eu>
 * @copyright 2005-2021 Pronamic
 * @license   GPL-3.0-or-later
 * @package   Pronamic\WordPress\ReviewsRatings
 */

namespace Pronamic\WordPress\ReviewsRatings\Admin;

use Pronamic\WordPress\ReviewsRatings\Plugin;
use Pronamic\WordPress\ReviewsRatings\Util;

/**
 * Admin settings.
 *
 * @author  Reüel van der Steege
 * @version 2.0.0
 * @since   2.0.0
 */
class AdminSettings {
	/**
	 * Plugin.
	 *
	 * @var Plugin
	 */
	private $plugin;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin Plugin.
	 * @return void
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;

		// Actions.
		\add_action( 'admin_init', array( $this, 'admin_init' ) );
		\add_action( 'admin_menu', array( $this, 'add_options_page' ) );

		// Filters.
		\add_filter( 'pre_update_option_pronamic_reviews_ratings_types', array( $this, 'pre_update_types' ), 10 );
	}

	/**
	 * Admin init.
	 *
	 * @return void
	 */
	public function admin_init() {
		// Supported post types settings section.
		\add_settings_section(
			'pronamic_reviews_ratings',
			__( 'General', 'pronamic_reviews_ratings' ),
			array( $this, 'settings_section' ),
			'pronamic-reviews-ratings'
		);

		// Settings field.
		\add_settings_field(
			'pronamic_reviews_ratings_types',
			__( 'Rating Types', 'pronamic_reviews_ratings' ),
			array( $this, 'input_rating_types' ),
			'pronamic-reviews-ratings',
			'pronamic_reviews_ratings',
			array(
				'label_for' => 'pronamic_reviews_ratings_types',
			)
		);
	}

	/**
	 * Add options page.
	 *
	 * @return void
	 */
	public function add_options_page() {
		\add_options_page(
			\__( 'Reviews', 'pronamic_reviews_ratings' ),
			\__( 'Reviews', 'pronamic_reviews_ratings' ),
			'manage_options',
			'pronamic-reviews-ratings',
			array( $this, 'render_options_page' )
		);
	}

	/**
	 * Render options page.
	 *
	 * @return void
	 */
	public function render_options_page() {
		global $pronamic_reviews_ratings_plugin;

		require_once $pronamic_reviews_ratings_plugin->dir_path . '/views/admin/page-settings.php';
	}

	/**
	 * Settings section.
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	public function settings_section( $args ) {
		switch ( $args['id'] ) {
			default:
		}
	}

	/**
	 * Input element text.
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	public function input_element( $args ) {
		$defaults = array(
			'type'        => 'text',
			'classes'     => 'regular-text',
			'description' => '',
			'options'     => null,
		);

		$args = \wp_parse_args( $args, $defaults );

		$name  = $args['label_for'];
		$value = \get_option( $name );

		$atts = array(
			'name'  => $name,
			'id'    => $name,
			'type'  => $args['type'],
			'class' => $args['classes'],
		);

		switch ( $args['type'] ) {
			case 'select':
				\printf(
					'<select %s>',
					// @codingStandardsIgnoreStart
					Util::array_to_html_attributes( $atts )
					// @codingStandardsIgnoreEn
				);

				$options = $args['options'];

				if ( \is_array( $options ) ) {
					foreach ( $options as $option ) {
						$value = \array_key_exists( 'value', $option ) ? $option['value'] : '';

						printf(
							'<option value="%s"%s> %s',
							\esc_attr( $value ),
							\selected(  $args['value'], $value, false ),
							\esc_html( $option['label'] )
						);
					}
				}

				echo '</select>';

				break;
			case 'text':
				$atts['value'] = $value;

				\printf(
					'<input %s />',
					// @codingStandardsIgnoreStart
					Util::array_to_html_attributes( $atts )
				// @codingStandardsIgnoreEn
				);

				break;
		}

		if ( ! empty( $args['description'] ) ) {
			\printf(
				'<p class="description">%s</p>',
				\esc_html( $args['description'] )
			);
		}
	}

	/**
	 * Input rating types.
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	public function input_rating_types( $args ) {
		// Rating types.
		$rating_types = $this->plugin->get_rating_types();

		// Post type options.
		$post_types = array();

		$types = \get_post_types( array( 'public' => true ), 'objects' );

		foreach ( $types as $post_type ) {
			// Ignore own review post type.
			if ( 'pronamic_review' === $post_type->name ) {
				continue;
			}

			// Add post type.
			$post_types[] = array(
				'name'  => $post_type->name,
				'label' => $post_type->label,
			);
		}

		?>

		<table class="widefat fixed">
			<thead>
				<tr>
					<th><?php \esc_html_e( 'Name', 'pronamic_reviews_ratings' ); ?></th>
					<th><?php \esc_html_e( 'Label', 'pronamic_reviews_ratings' ); ?></th>
					<th><?php \esc_html_e( 'Restrict to Post Type', 'pronamic_reviews_ratings' ); ?></th>
				</tr>
			</thead>

			<?php

			$defaults = array(
				'type'        => 'text',
				'classes'     => 'regular-text',
				'description' => '',
				'options'     => null,
			);

			$args = \wp_parse_args( $args, $defaults );

			$alternate = true;

			// Add empty rating type for new item input fields.
			$rating_types[] = array(
				'name'       => '',
				'label'      => '',
				'post_types' => array(),
			);

			foreach ( $rating_types as $type ) {
				$rating_type = $type['name'];

				$input_name = sprintf(
					'%s[%s]',
					$args['label_for'],
					empty( $rating_type ) ? '_new' : $rating_type
				);

				?>

				<tr<?php echo $alternate ? ' class="alternate"' : ''; ?>>
					<td style="vertical-align: top;">
						<?php

						$name = sprintf( '%s[name]', $input_name );

						$atts = array(
							'name'        => $name,
							'id'          => \strtr( $name, array( '[' => '_', ']' => '_' ) ),
							'type'        => 'text',
							'class'       => '',
							'value'       => \array_key_exists( 'name', $type ) ? $rating_type : ( empty( $rating_type ) ? '' : $rating_type ),
						);

						if ( \array_key_exists( 'edit_name_disabled', $type ) && true === $type['edit_name_disabled'] ) {
							$atts['disabled'] = 'disabled';
						}

						\printf(
							'<code>_pronamic_rating_value_</code><input %s />',
							Util::array_to_html_attributes( $atts )
						);

						?>
					</td>

					<td style="vertical-align: top;">
						<?php

						$name = sprintf( '%s[label]', $input_name );

						$atts = array(
							'name'        => $name,
							'id'          => \strtr( $name, array( '[' => '_', ']' => '_' ) ),
							'type'        => 'text',
							'class'       => 'regular-text',
							'value'       => \array_key_exists( 'label', $type ) ? $type['label'] : '',
							'placeholder' => \array_key_exists( 'label', $type ) ? '' : __( 'New rating type…', 'pronamic_reviews_ratings' ),
						);

						\printf(
							'<input %s />',
							Util::array_to_html_attributes( $atts )
						);

						?>
					</td>

					<td>
						<?php

						$name = sprintf( '%s[post_types][]', $input_name );

						$atts = array(
							'name'     => $name,
							'id'       => \strtr( $name, array( '[' => '_', ']' => '_' ) ),
							'multiple' => 'multiple',
							'size'     => min( 5, count( $post_types ) ),
						);

						\printf(
							'<select %s>',
							Util::array_to_html_attributes( $atts )
						);

						foreach ( $post_types as $post_type ) {
							// Selected.
							$supported_post_types = \wp_list_filter(
								\pronamic_get_rating_types( $post_type['name'] ),
								array(
									'name' => $rating_type,
								)
							);

							$selected = ! empty( $supported_post_types );

							printf(
								'<option value="%s"%s> %s</option>',
								\esc_attr( $post_type['name'] ),
								\selected( $selected, true, false ),
								\esc_html( $post_type['label'] )
							);
						}

						echo '</select>';

						?>
					</td>
				</tr>

				<?php

				// Alternate rows.
				$alternate = ! $alternate;
			}

		?>

		</table>

		<?php

		if ( ! empty( $args['description'] ) ) {
			\printf(
				'<p class="description">%s</p>',
				\esc_html( $args['description'] )
			);
		}
	}

	/**
	 * Filter rating types option on update.
	 *
	 * @param array[] $value Rating types update value.
	 * @return array
	 */
	public function pre_update_types( $value ) {
		if ( ! is_array( $value ) ) {
			$value = array();
		}

		foreach ( $value as $key => $rating_type ) {
			$name = \array_key_exists( 'name', $rating_type ) ? trim( $rating_type['name'] ) : '';

			if ( $key !== $name && ! empty( $name ) ) {
				$value[ $name ] = $rating_type;

				unset( $value[ $key ] );
			}

			if ( empty( $name ) && \array_key_exists( $key, $value ) ) {
				unset( $value[ $key ] );
			}
		}

		return $value;
	}

	/**
	 * Input element checkboxes.
	 *
	 * @param array $args Arguments.
	 * @return void
	 */
	public function input_select( $args ) {
		$defaults = array(
			'type'        => 'select',
			'classes'     => 'regular-text',
			'description' => '',
			'options'     => array(),
		);

		$args = \wp_parse_args( $args, $defaults );

		$name  = $args['label_for'];
		$value = \get_option( $name );

		$atts = array(
			'name'  => $name,
			'id'    => $name,
			'type'  => $args['type'],
			'class' => $args['classes'],
			'value' => $value,
		);

		\printf(
			'<input %s />',
			// @codingStandardsIgnoreStart
			Util::array_to_html_attributes( $atts )
			// @codingStandardsIgnoreEn
		);

		if ( ! empty( $args['description'] ) ) {
			\printf(
				'<p class="description">%s</p>',
				\esc_html( $args['description'] )
			);
		}
	}
}
