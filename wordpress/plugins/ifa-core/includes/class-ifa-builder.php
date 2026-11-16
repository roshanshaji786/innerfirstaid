<?php
/**
 * One-click page builder: creates all pages as Elementor documents.
 *
 * @package ifa-core
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class IFA_Builder
 */
class IFA_Builder {

	/**
	 * Singleton.
	 *
	 * @var IFA_Builder|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return IFA_Builder
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'admin_post_ifa_build_pages', array( $this, 'admin_build' ) );
	}

	/**
	 * Admin-post handler for the "Build pages" button.
	 */
	public function admin_build() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Permission denied', 'ifa-core' ) );
		}
		check_admin_referer( 'ifa_build_pages' );

		$result = self::build_all();

		$url = add_query_arg(
			array(
				'page'   => 'ifa-settings',
				'ifa_built' => $result ? '1' : '0',
			),
			admin_url( 'options-general.php' )
		);
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Build all pages. Returns true on success.
	 *
	 * @return bool
	 */
	public static function build_all() {
		if ( ! did_action( 'elementor/loaded' ) ) {
			return false;
		}

		$built = array();

		// Home (EN).
		$home = self::upsert_page(
			array(
				'post_name' => 'home',
				'post_title' => 'Inner First Aid — Free Guide & 21-Day Program',
				'lang'      => 'en',
				'parent'    => 0,
			),
			self::page_data( 'home', 'en' )
		);
		$built['home'] = $home;

		// Home (SL).
		$sl = self::upsert_page(
			array(
				'post_name' => 'sl',
				'post_title' => 'Inner First Aid — Brezplacen vodic in 21-dnevni program',
				'lang'      => 'sl',
				'parent'    => 0,
			),
			self::page_data( 'home', 'sl' )
		);
		$built['sl'] = $sl;

		// Privacy / Terms EN.
		$built['privacy_en'] = self::upsert_page(
			array( 'post_name' => 'privacy', 'post_title' => 'Privacy Policy', 'lang' => 'en', 'parent' => 0 ),
			self::page_data( 'privacy', 'en' )
		);
		$built['terms_en'] = self::upsert_page(
			array( 'post_name' => 'terms', 'post_title' => 'Terms of Service', 'lang' => 'en', 'parent' => 0 ),
			self::page_data( 'terms', 'en' )
		);

		// Privacy / Terms SL (children of the SL home so they live under /sl/).
		$built['privacy_sl'] = self::upsert_page(
			array( 'post_name' => 'privacy', 'post_title' => 'Politika zasebnosti', 'lang' => 'sl', 'parent' => $sl ),
			self::page_data( 'privacy', 'sl' )
		);
		$built['terms_sl'] = self::upsert_page(
			array( 'post_name' => 'terms', 'post_title' => 'Pogoji uporabe', 'lang' => 'sl', 'parent' => $sl ),
			self::page_data( 'terms', 'sl' )
		);

		// Front page + pretty permalinks.
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home );
		if ( '' === get_option( 'permalink_structure' ) ) {
			update_option( 'permalink_structure', '/%postname%/' );
		}
		flush_rewrite_rules();

		// Set a proper site title unless the owner already chose one.
		$blogname = get_option( 'blogname' );
		if ( in_array( $blogname, array( '', 'My Blog', 'WordPress', 'Test Blog', 'A WordPress Site', 'Blog' ), true ) ) {
			update_option( 'blogname', 'Inner First Aid' );
		}

		// NOTE: page URL settings (en_url, sl_url, privacy_url, terms_url) are
		// intentionally reset to empty so the front end always derives them from
		// the current site URL (home_url()). This keeps everything working after
		// a domain change or site move (and clears stale values from older
		// versions of the plugin). Users can still set custom URLs manually in
		// Settings > Inner First Aid after building.
		ifa_update_option( 'en_url', '' );
		ifa_update_option( 'sl_url', '' );
		ifa_update_option( 'privacy_url', '' );
		ifa_update_option( 'terms_url', '' );

		return ! in_array( 0, array_values( $built ), true );
	}

	/**
	 * Create or update a page and write Elementor data.
	 *
	 * @param array $args     Post args (post_name, post_title, lang, parent).
	 * @param array $elements Elementor element tree.
	 * @return int Post ID.
	 */
	public static function upsert_page( $args, $elements ) {
		$existing = get_page_by_path( $args['post_name'], OBJECT, 'page' );
		if ( $existing && (int) $existing->post_parent !== (int) $args['parent'] ) {
			// Find within the right parent.
			$existing = self::find_page( $args['post_name'], $args['parent'] );
		}

		$post_data = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $args['post_title'],
			'post_name'    => $args['post_name'],
			'post_parent'  => (int) $args['parent'],
			'post_content' => '',
		);

		if ( $existing ) {
			$post_data['ID'] = $existing->ID;
			wp_update_post( $post_data );
			$post_id = (int) $existing->ID;
		} else {
			$post_id = (int) wp_insert_post( $post_data );
		}

		if ( ! $post_id ) {
			return 0;
		}

		// Elementor document meta.
		update_post_meta( $post_id, '_elementor_edit_mode', 'builder' );
		update_post_meta( $post_id, '_elementor_template_type', 'wp-page' );
		update_post_meta( $post_id, '_elementor_version', defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '3.0.0' );
		update_post_meta( $post_id, '_elementor_data', wp_slash( wp_json_encode( $elements ) ) );
		update_post_meta( $post_id, '_elementor_page_settings', array() );
		delete_post_meta( $post_id, '_elementor_css' );

		return $post_id;
	}

	/**
	 * Find a page by slug within a parent.
	 *
	 * @param string $slug   Slug.
	 * @param int    $parent Parent id.
	 * @return WP_Post|null
	 */
	private static function find_page( $slug, $parent ) {
		$pages = get_posts(
			array(
				'post_type'   => 'page',
				'name'        => $slug,
				'post_parent' => (int) $parent,
				'post_status' => 'any',
				'numberposts' => 1,
			)
		);
		return $pages ? $pages[0] : null;
	}

	/**
	 * Build the Elementor element tree for a page.
	 *
	 * @param string $page 'home'|'privacy'|'terms'.
	 * @param string $lang 'en'|'sl'.
	 * @return array
	 */
	public static function page_data( $page, $lang ) {
		$section_id = function () {
			return self::eid();
		};

		if ( 'privacy' === $page ) {
			return array(
				self::section(
					array(),
					array(
						self::column(
							array(),
							array(
								self::widget(
									'heading',
									array(
										'title'       => 'sl' === $lang ? 'Politika zasebnosti' : 'Privacy Policy',
										'header_size' => 'h1',
										'align'       => 'left',
									)
								),
								self::widget( 'text-editor', array( 'editor' => self::privacy_text( $lang ) ) ),
							)
						),
					)
				),
			);
		}

		if ( 'terms' === $page ) {
			return array(
				self::section(
					array(),
					array(
						self::column(
							array(),
							array(
								self::widget(
									'heading',
									array(
										'title'       => 'sl' === $lang ? 'Pogoji uporabe' : 'Terms of Service',
										'header_size' => 'h1',
										'align'       => 'left',
									)
								),
								self::widget( 'text-editor', array( 'editor' => self::terms_text( $lang ) ) ),
							)
						),
					)
				),
			);
		}

		// Home page composition.
		$data = array(
			self::section(
				array( 'layout' => 'full_width' ),
				array(
					self::column(
						array(),
						array(
							self::widget( 'ifa-hero', self::hero_settings( $lang ) ),
						)
					),
				)
			),
			self::section(
				array(),
				array(
					self::column(
						array(),
						array(
							self::widget( 'ifa-social-proof', self::social_settings( $lang ) ),
						)
					),
				)
			),
			self::section(
				array( 'section_id' => 'programs' ),
				array(
					self::column(
						array(),
						array(
							self::widget( 'ifa-programs', self::programs_settings( $lang ) ),
						)
					),
				)
			),
			self::section(
				array(),
				array(
					self::column(
						array(),
						array(
							self::widget( 'ifa-how-it-works', self::steps_settings( $lang ) ),
						)
					),
				)
			),
			self::section(
				array(),
				array(
					self::column(
						array(),
						array(
							self::widget( 'ifa-pricing', self::pricing_settings( $lang ) ),
						)
					),
				)
			),
		);

		return $data;
	}

	/**
	 * Section element.
	 *
	 * @param array $settings Settings.
	 * @param array $elements Children.
	 * @return array
	 */
	private static function section( $settings, $elements ) {
		return array(
			'id'       => self::eid(),
			'elType'   => 'section',
			'settings' => array_merge( array( 'gap' => 'no' ), $settings ),
			'elements' => $elements,
		);
	}

	/**
	 * Column element.
	 *
	 * @param array $settings Settings.
	 * @param array $elements Children.
	 * @return array
	 */
	private static function column( $settings, $elements ) {
		return array(
			'id'       => self::eid(),
			'elType'   => 'column',
			'settings' => array_merge( array( '_column_size' => 100 ), $settings ),
			'elements' => $elements,
		);
	}

	/**
	 * Widget element.
	 *
	 * @param string $type     Widget type.
	 * @param array  $settings Settings.
	 * @return array
	 */
	private static function widget( $type, $settings ) {
		return array(
			'id'         => self::eid(),
			'elType'     => 'widget',
			'widgetType' => $type,
			'settings'   => $settings,
			'elements'   => array(),
		);
	}

	/**
	 * Unique element id (Elementor style).
	 *
	 * @return string
	 */
	private static function eid() {
		return wp_generate_password( 5, false, false );
	}

	/**
	 * Hero widget settings.
	 *
	 * @param string $lang Language.
	 * @return array
	 */
	private static function hero_settings( $lang ) {
		if ( 'sl' === $lang ) {
			return array(
				'lang'            => 'sl',
				'badge'           => 'Mednarodna platforma za psiholosko prvo pomoc',
				'headline'        => 'Ko te boli - tu je korak za korakom.',
				'subheadline'     => 'Brezplacni vodic: 3 napake, ki podaljsajo bolecino. Vpisi email in ga prejmi takoj.',
				'form_button'     => 'Prenesi brezplacni vodic ->',
				'form_note'       => 'Brezplacno | Anonimno | Brez kreditne kartice',
				'form_placeholder'=> 'Vpisi email',
				'success_text'    => 'Poslano! Preveri svoj nabiralnik.',
				'error_text'      => 'Nekaj je slo narobe. Preveri email in poskusi znova.',
				'invalid_text'    => 'Vpisi veljaven email naslov.',
				'bg_image'        => self::hero_image_id(),
			);
		}

		return array(
			'lang'            => 'en',
			'badge'           => 'The international platform for psychological first aid',
			'headline'        => 'When it hurts - here is step by step.',
			'subheadline'     => 'Free guide: 3 mistakes that prolong the pain. Enter your email and receive it now.',
			'form_button'     => 'Get free guide ->',
			'form_note'       => 'Free | Anonymous | No credit card',
			'form_placeholder'=> 'Enter your email',
			'success_text'    => 'Sent! Check your inbox.',
			'error_text'      => 'Something went wrong. Please check the email and try again.',
			'invalid_text'    => 'Please enter a valid email address.',
			'bg_image'        => self::hero_image_id(),
		);
	}

	/**
	 * Social proof widget settings.
	 *
	 * @param string $lang Language.
	 * @return array
	 */
	private static function social_settings( $lang ) {
		if ( 'sl' === $lang ) {
			return array(
				'items' => array(
					array( 'title' => 'Psiholosko preverjeno', 'subtitle' => 'Strokovno preverjena vsebina' ),
					array( 'title' => '21-dnevni program', 'subtitle' => 'En email na dan' ),
					array( 'title' => 'Anonimno', 'subtitle' => 'Nihce ne ve. Samo ti.' ),
				),
			);
		}
		return array(
			'items' => array(
				array( 'title' => 'Psychologist verified', 'subtitle' => 'Evidence-based content' ),
				array( 'title' => '21-day program', 'subtitle' => 'One email per day' ),
				array( 'title' => 'Anonymous', 'subtitle' => 'Nobody knows. Only you.' ),
			),
		);
	}

	/**
	 * Programs widget settings.
	 *
	 * @param string $lang Language.
	 * @return array
	 */
	private static function programs_settings( $lang ) {
		if ( 'sl' === $lang ) {
			return array(
				'label'            => 'Programi',
				'card1_title'      => 'Po razhodu',
				'card1_text'       => 'Ko misli nanj/o ne gredo stran.',
				'card1_btn'        => 'Zacni program ->',
				'card1_btn_female' => 'Za zenske',
				'card1_btn_male'   => 'Za moske',
				'card1_mode'       => 'gender',
				'card1_link_source'=> 'site_default',
				'card2_title'      => 'Nespecnost',
				'card2_text'       => 'Tvoja pot do mirnega spanja.',
				'card2_btn'        => 'Kmalu',
			);
		}
		return array(
			'label'            => 'Programs',
			'card1_title'      => 'After a breakup',
			'card1_text'       => "When thoughts of them won't go away.",
			'card1_btn'        => 'Start program ->',
			'card1_mode'       => 'direct',
			'card1_link_source'=> 'site_default',
			'card2_title'      => 'Insomnia',
			'card2_text'       => 'Your path to restful sleep.',
			'card2_btn'        => 'Coming soon',
		);
	}

	/**
	 * How it works widget settings.
	 *
	 * @param string $lang Language.
	 * @return array
	 */
	private static function steps_settings( $lang ) {
		if ( 'sl' === $lang ) {
			return array(
				'label' => 'Kako deluje',
				'steps' => array(
					array( 'num' => '1', 'title' => 'Vpisi email', 'text' => 'Dobis brezplacni vodic takoj.' ),
					array( 'num' => '2', 'title' => 'Preberi', 'text' => '5 minut. 3 napake, ki jih verjetno delas.' ),
					array( 'num' => '3', 'title' => 'Zacni 21-dnevni program', 'text' => 'EUR 39.90. En email na dan. Korak za korakom.' ),
				),
			);
		}
		return array(
			'label' => 'How it works',
			'steps' => array(
				array( 'num' => '1', 'title' => 'Enter your email', 'text' => 'Get the free guide instantly.' ),
				array( 'num' => '2', 'title' => 'Read it', 'text' => "5 minutes. 3 mistakes you're probably making." ),
				array( 'num' => '3', 'title' => 'Start the 21-day program', 'text' => 'EUR 39.90. One email per day. Step by step.' ),
			),
		);
	}

	/**
	 * Pricing widget settings.
	 *
	 * @param string $lang Language.
	 * @return array
	 */
	private static function pricing_settings( $lang ) {
		if ( 'sl' === $lang ) {
			return array(
				'title'      => '21-dnevni program za okrevanje po razhodu',
				'price_old'  => 'EUR 110.90',
				'price_new'  => 'EUR 39.90',
				'price_note' => '| Takojsen dostop',
				'btn_text'   => 'Zacni zdaj ->',
				'note'       => 'Anonimno | Takojsen dostop | Brez tveganja',
				'link_source'=> 'site_default',
			);
		}
		return array(
			'title'      => '21-day breakup recovery program',
			'price_old'  => 'EUR 110.90',
			'price_new'  => 'EUR 39.90',
			'price_note' => '| Instant access',
			'btn_text'   => 'Start now ->',
			'note'       => 'Anonymous | Instant access | No risk',
			'link_source'=> 'site_default',
		);
	}

	/**
	 * Privacy page body text.
	 *
	 * @param string $lang Language.
	 * @return string (HTML, allowed subset)
	 */
	private static function privacy_text( $lang ) {
		if ( 'sl' === $lang ) {
			return '<p>Inner First Aid zbira email naslov, ki ga vpises, da ti lahko posljemo brezplacni vodic, program ske email-e in sorodne vsebine, ki si jih zahtevas.</p>
<p>Piskotke in analitiko uporabljamo samo po tvojem soglasju. Piskotke lahko zavrnes v pasici.</p>
<p>Tvojih osebnih podatkov ne prodajamo. Placila obdeluje Stripe, ko se odlocis za nakup programa.</p>
<p>Za izbris ali popravek tvojega email zapisa kontaktiraj ekipo Inner First Aid preko emaila, uporabljenega pri nakupu ali prejemu vodica.</p>';
		}
		return '<p>Inner First Aid collects the email address you submit so we can send the free guide, program emails, and related updates you request.</p>
<p>We use cookies and analytics only after consent. You can decline cookies in the banner.</p>
<p>We do not sell your personal information. Payment processing is handled by Stripe when you choose to buy a program.</p>
<p>To request deletion or correction of your email record, contact the Inner First Aid team through the email address used for your purchase or guide delivery.</p>';
	}

	/**
	 * Terms page body text.
	 *
	 * @param string $lang Language.
	 * @return string (HTML, allowed subset)
	 */
	private static function terms_text( $lang ) {
		if ( 'sl' === $lang ) {
			return '<p>Ta program je informativne in izobrazevalne narave in ne predstavlja medicinskega, psiholoskega ali terapevtskega nasveta.</p>
<p>Z nakupom in uporabo programa se strinjas, da je uporaba na lastno odgovornost in da Inner First Aid ne odgovarja za rezultate.</p>
<p>Placila potekajo preko Stripe in so dokoncna. Po prejemu programa ni vracij.</p>';
		}
		return '<p>This program is informational and educational and does not constitute medical, psychological or therapeutic advice.</p>
<p>By purchasing and using the program you agree that you use it at your own responsibility and that Inner First Aid is not liable for results.</p>
<p>Payments are processed via Stripe and are final. No refunds after the program has been delivered.</p>';
	}

	/**
	 * Hero image ID (theme asset imported as attachment).
	 *
	 * @return int
	 */
	public static function hero_image_id() {
		$id = (int) get_option( 'ifa_hero_image_id', 0 );
		if ( $id && 'attachment' === get_post_type( $id ) ) {
			return $id;
		}

		$file = get_template_directory() . '/assets/img/hero.jpg';
		if ( ! file_exists( $file ) ) {
			return 0;
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$id = media_handle_sideload(
			array(
				'name'     => 'ifa-hero.jpg',
				'tmp_name' => $file,
			),
			0
		);

		if ( is_wp_error( $id ) ) {
			return 0;
		}

		update_option( 'ifa_hero_image_id', (int) $id );
		return (int) $id;
	}
}
