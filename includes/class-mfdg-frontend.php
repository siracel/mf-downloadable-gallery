<?php
/**
 * Frontend rendering + shortcode.
 *
 * @package MF_Downloadable_Gallery
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class MFDG_Frontend
 */
class MFDG_Frontend {

	/**
	 * Whether assets have been enqueued flag.
	 *
	 * @var bool
	 */
	private $enqueued = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_shortcode( 'mf_downloadable_gallery', array( $this, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
	}

	/**
	 * Register (not enqueue) assets; enqueued on demand when shortcode runs.
	 */
	public function register_assets() {
		wp_register_style( 'mfdg-frontend', MFDG_URL . 'assets/css/mfdg-frontend.css', array(), MFDG_VERSION );
		wp_register_script( 'mfdg-frontend', MFDG_URL . 'assets/js/mfdg-frontend.js', array( 'jquery' ), MFDG_VERSION, true );

		$s = MFDG_Settings::all();

		wp_localize_script(
			'mfdg-frontend',
			'mfdgFront',
			array(
				'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'mfdg_front' ),
				'successMessage' => $s['success_message'],
				'collectName'    => (int) $s['collect_name'],
				'consentEnabled' => (int) $s['consent_enabled'],
				'consentText'    => wp_kses( $s['consent_text'], array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ),
				'rememberVisitor' => (int) $s['remember_visitor'],
				'i18n'           => array(
					'invalidEmail' => __( 'Please enter a valid e-mail address.', 'mf-downloadable-gallery' ),
					'consentReq'   => __( 'Please accept the consent to continue.', 'mf-downloadable-gallery' ),
					'error'        => __( 'Something went wrong. Please try again.', 'mf-downloadable-gallery' ),
					'downloading'  => __( 'Preparing…', 'mf-downloadable-gallery' ),
					'submit'       => __( 'Download', 'mf-downloadable-gallery' ),
					'close'        => __( 'Close', 'mf-downloadable-gallery' ),
					'emailLabel'   => __( 'E-mail address', 'mf-downloadable-gallery' ),
					'nameLabel'    => __( 'Your name', 'mf-downloadable-gallery' ),
					'emailPh'      => __( 'you@example.com', 'mf-downloadable-gallery' ),
				),
			)
		);
	}

	/**
	 * Ensure assets are enqueued once.
	 */
	private function enqueue() {
		if ( $this->enqueued ) {
			return;
		}
		wp_enqueue_style( 'mfdg-frontend' );
		wp_enqueue_script( 'mfdg-frontend' );
		$this->enqueued = true;
	}

	/**
	 * Shortcode handler.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string
	 */
	public function shortcode( $atts ) {
		$defaults = array(
			'layout'   => 'grid',   // grid | grouped
			'category' => '',       // slug(s), comma separated
			'columns'  => MFDG_Settings::get( 'columns', 3 ),
			'orderby'  => 'date',   // date | title | menu_order
			'order'    => 'DESC',   // ASC | DESC
			'filter'   => 'yes',    // yes | no (category filter bar, grid layout only)
			'limit'    => -1,
			'ids'      => '',
		);
		$atts = shortcode_atts( $defaults, $atts, 'mf_downloadable_gallery' );

		$this->enqueue();

		// URL-driven category selection (from the filter bar links, progressive enhancement).
		$active_cat = isset( $_GET['mfdg_cat'] ) ? sanitize_title( wp_unslash( $_GET['mfdg_cat'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		ob_start();

		if ( 'grouped' === $atts['layout'] ) {
			echo $this->render_grouped( $atts ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		} else {
			echo '<div class="mfdg mfdg--grid-wrap">';

			if ( 'no' !== $atts['filter'] && '' === $atts['category'] ) {
				echo $this->render_filter_bar( $active_cat ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			$query_cat = $active_cat ? $active_cat : $atts['category'];

			echo '<div class="mfdg-grid" data-columns="' . esc_attr( $atts['columns'] ) . '" style="--mfdg-cols:' . esc_attr( $atts['columns'] ) . '">';
			echo $this->render_items( $atts, $query_cat ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '</div>';
			echo '</div>';
		}

		return ob_get_clean();
	}

	/**
	 * Render the category filter bar.
	 *
	 * @param string $active Active category slug.
	 * @return string
	 */
	private function render_filter_bar( $active = '' ) {
		$terms = get_terms(
			array(
				'taxonomy'   => MFDG_CPT::TAXONOMY,
				'hide_empty' => true,
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return '';
		}

		$html  = '<div class="mfdg-filter" role="tablist">';
		$html .= sprintf(
			'<a href="%s" class="mfdg-filter__btn%s" data-cat="">%s</a>',
			esc_url( remove_query_arg( 'mfdg_cat' ) ),
			'' === $active ? ' is-active' : '',
			esc_html__( 'All', 'mf-downloadable-gallery' )
		);

		foreach ( $terms as $term ) {
			$html .= sprintf(
				'<a href="%s" class="mfdg-filter__btn%s" data-cat="%s">%s</a>',
				esc_url( add_query_arg( 'mfdg_cat', $term->slug ) ),
				$active === $term->slug ? ' is-active' : '',
				esc_attr( $term->slug ),
				esc_html( $term->name )
			);
		}

		$html .= '</div>';
		return $html;
	}

	/**
	 * Build a WP_Query for files.
	 *
	 * @param array  $atts     Shortcode atts.
	 * @param string $category Category slug filter.
	 * @return WP_Query
	 */
	private function get_query( $atts, $category = '' ) {
		$allowed_orderby = array( 'date', 'title', 'menu_order', 'rand' );
		$orderby         = in_array( $atts['orderby'], $allowed_orderby, true ) ? $atts['orderby'] : 'date';
		$order           = ( 'ASC' === strtoupper( $atts['order'] ) ) ? 'ASC' : 'DESC';

		$args = array(
			'post_type'      => MFDG_CPT::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => (int) $atts['limit'],
			'orderby'        => $orderby,
			'order'          => $order,
		);

		if ( 'menu_order' === $orderby ) {
			$args['orderby'] = array(
				'menu_order' => $order,
				'date'       => 'DESC',
			);
		}

		if ( ! empty( $atts['ids'] ) ) {
			$args['post__in'] = array_map( 'absint', explode( ',', $atts['ids'] ) );
			$args['orderby']  = 'post__in';
		}

		if ( '' !== $category ) {
			$slugs             = array_map( 'sanitize_title', explode( ',', $category ) );
			$args['tax_query'] = array(
				array(
					'taxonomy' => MFDG_CPT::TAXONOMY,
					'field'    => 'slug',
					'terms'    => $slugs,
				),
			);
		}

		return new WP_Query( $args );
	}

	/**
	 * Render items grid (cards). Public so AJAX can reuse.
	 *
	 * @param array  $atts     Shortcode atts.
	 * @param string $category Category slug filter.
	 * @return string
	 */
	public function render_items( $atts, $category = '' ) {
		$query = $this->get_query( $atts, $category );

		if ( ! $query->have_posts() ) {
			return '<p class="mfdg-empty">' . esc_html__( 'No files available yet.', 'mf-downloadable-gallery' ) . '</p>';
		}

		$html = '';
		while ( $query->have_posts() ) {
			$query->the_post();
			$html .= $this->render_card( get_the_ID() );
		}
		wp_reset_postdata();

		return $html;
	}

	/**
	 * Render items grouped by category.
	 *
	 * @param array $atts Shortcode atts.
	 * @return string
	 */
	private function render_grouped( $atts ) {
		$term_args = array(
			'taxonomy'   => MFDG_CPT::TAXONOMY,
			'hide_empty' => true,
		);

		if ( '' !== $atts['category'] ) {
			$term_args['slug'] = array_map( 'sanitize_title', explode( ',', $atts['category'] ) );
		}

		$terms = get_terms( $term_args );
		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return '<div class="mfdg"><p class="mfdg-empty">' . esc_html__( 'No categories available yet.', 'mf-downloadable-gallery' ) . '</p></div>';
		}

		$html = '<div class="mfdg mfdg--grouped">';
		foreach ( $terms as $term ) {
			$section = $this->render_items( $atts, $term->slug );
			if ( false !== strpos( $section, 'mfdg-empty' ) ) {
				continue;
			}
			$html .= '<section class="mfdg-group" id="mfdg-cat-' . esc_attr( $term->slug ) . '">';
			$html .= '<h3 class="mfdg-group__title">' . esc_html( $term->name ) . '</h3>';
			if ( $term->description ) {
				$html .= '<p class="mfdg-group__desc">' . esc_html( $term->description ) . '</p>';
			}
			$html .= '<div class="mfdg-grid" style="--mfdg-cols:' . esc_attr( $atts['columns'] ) . '">' . $section . '</div>';
			$html .= '</section>';
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Render a single file card.
	 *
	 * @param int $post_id Post id.
	 * @return string
	 */
	private function render_card( $post_id ) {
		$file_id = (int) get_post_meta( $post_id, '_mfdg_file_id', true );
		if ( ! $file_id ) {
			return '';
		}

		$require_email = '1' === (string) get_post_meta( $post_id, '_mfdg_require_email', true );
		$file_url      = wp_get_attachment_url( $file_id );
		$title         = get_the_title( $post_id );
		$excerpt       = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : '';

		$s            = MFDG_Settings::all();
		$button_label = $require_email ? $s['gated_button_label'] : $s['button_label'];

		// Category names for the lead record.
		$terms     = get_the_terms( $post_id, MFDG_CPT::TAXONOMY );
		$cat_names = ( $terms && ! is_wp_error( $terms ) ) ? wp_list_pluck( $terms, 'name' ) : array();
		$cat_str   = implode( ', ', $cat_names );

		// Cover (featured image), rendered in a 3:4 box.
		$cover = has_post_thumbnail( $post_id )
			? get_the_post_thumbnail( $post_id, 'medium', array( 'class' => 'mfdg-card__img', 'loading' => 'lazy' ) )
			: '<span class="mfdg-card__placeholder" aria-hidden="true"></span>';

		$suggested_name = sanitize_file_name( $title );

		ob_start();
		?>
		<article class="mfdg-card">
			<div class="mfdg-card__cover"><?php echo $cover; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
			<div class="mfdg-card__body">
				<h4 class="mfdg-card__title"><?php echo esc_html( $title ); ?></h4>
				<?php if ( $excerpt ) : ?>
					<p class="mfdg-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
				<?php endif; ?>

				<?php if ( $require_email ) : ?>
					<button type="button"
						class="mfdg-btn mfdg-btn--gated mfdg-open-modal"
						data-file-id="<?php echo esc_attr( $post_id ); ?>"
						data-file-url="<?php echo esc_url( $file_url ); ?>"
						data-file-title="<?php echo esc_attr( $title ); ?>"
						data-category="<?php echo esc_attr( $cat_str ); ?>"
						data-filename="<?php echo esc_attr( $suggested_name ); ?>">
						<?php echo esc_html( $button_label ); ?>
					</button>
				<?php else : ?>
					<a class="mfdg-btn mfdg-btn--direct mfdg-direct"
						href="<?php echo esc_url( $file_url ); ?>"
						download="<?php echo esc_attr( $suggested_name ); ?>"
						data-file-id="<?php echo esc_attr( $post_id ); ?>"
						rel="nofollow noopener">
						<?php echo esc_html( $button_label ); ?>
					</a>
				<?php endif; ?>
			</div>
		</article>
		<?php
		return ob_get_clean();
	}
}
