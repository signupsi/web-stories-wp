<?php
/**
 * Class Story_Post_Type.
 *
 * @package   Google\Web_Stories
 * @copyright 2020 Google LLC
 * @license   https://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @link      https://github.com/google/web-stories-wp
 */

/**
 * Copyright 2020 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Google\Web_Stories;

use Google\Web_Stories\REST_API\Stories_Controller;
use WP_Post;
use WP_Screen;

/**
 * Class Story_Post_Type.
 */
class Story_Post_Type {
	/**
	 * The slug of the post type to store URLs that have AMP errors.
	 *
	 * @var string
	 */
	const POST_TYPE_SLUG = 'web-story';

	/**
	 * AMP Stories script handle.
	 *
	 * @var string
	 */
	const WEB_STORIES_SCRIPT_HANDLE = 'edit-story';

	/**
	 * AMP Stories style handle.
	 *
	 * @var string
	 */
	const WEB_STORIES_STYLE_HANDLE = 'edit-story';

	/**
	 * The rewrite slug for this post type.
	 *
	 * @var string
	 */
	const REWRITE_SLUG = 'stories';

	/**
	 * Registers the post type to store URLs with validation errors.
	 *
	 * @return void
	 */
	public static function register() {
		register_post_type(
			self::POST_TYPE_SLUG,
			[
				'labels'                => [
					'name'                     => _x( 'Stories', 'post type general name', 'web-stories' ),
					'singular_name'            => _x( 'Story', 'post type singular name', 'web-stories' ),
					'add_new'                  => _x( 'New', 'story', 'web-stories' ),
					'add_new_item'             => __( 'Add New Story', 'web-stories' ),
					'edit_item'                => __( 'Edit Story', 'web-stories' ),
					'new_item'                 => __( 'New Story', 'web-stories' ),
					'view_item'                => __( 'View Story', 'web-stories' ),
					'view_items'               => __( 'View Stories', 'web-stories' ),
					'search_items'             => __( 'Search Stories', 'web-stories' ),
					'not_found'                => __( 'No stories found.', 'web-stories' ),
					'not_found_in_trash'       => __( 'No stories found in Trash.', 'web-stories' ),
					'all_items'                => __( 'All Stories', 'web-stories' ),
					'archives'                 => __( 'Story Archives', 'web-stories' ),
					'attributes'               => __( 'Story Attributes', 'web-stories' ),
					'insert_into_item'         => __( 'Insert into story', 'web-stories' ),
					'uploaded_to_this_item'    => __( 'Uploaded to this story', 'web-stories' ),
					'featured_image'           => __( 'Featured Image', 'web-stories' ),
					'set_featured_image'       => __( 'Set featured image', 'web-stories' ),
					'remove_featured_image'    => __( 'Remove featured image', 'web-stories' ),
					'use_featured_image'       => __( 'Use as featured image', 'web-stories' ),
					'filter_items_list'        => __( 'Filter stories list', 'web-stories' ),
					'items_list_navigation'    => __( 'Stories list navigation', 'web-stories' ),
					'items_list'               => __( 'Stories list', 'web-stories' ),
					'item_published'           => __( 'Story published.', 'web-stories' ),
					'item_published_privately' => __( 'Story published privately.', 'web-stories' ),
					'item_reverted_to_draft'   => __( 'Story reverted to draft.', 'web-stories' ),
					'item_scheduled'           => __( 'Story scheduled', 'web-stories' ),
					'item_updated'             => __( 'Story updated.', 'web-stories' ),
					'menu_name'                => _x( 'Stories', 'admin menu', 'web-stories' ),
					'name_admin_bar'           => _x( 'Story', 'add new on admin bar', 'web-stories' ),
				],
				'menu_icon'             => 'dashicons-book',
				'taxonomies'            => [
					'post_tag',
					'category',
				],
				'supports'              => [
					'title', // Used for amp-story[title].
					'author',
					'editor',
					'excerpt',
					'thumbnail', // Used for poster images.
					'web-stories',
					'revisions', // Without this, the REST API will return 404 for an autosave request.
				],
				'rewrite'               => [
					'slug' => self::REWRITE_SLUG,
				],
				'public'                => true,
				'show_ui'               => true,
				'show_in_rest'          => true,
				'rest_controller_class' => Stories_Controller::class,
			]
		);

		add_action( 'admin_enqueue_scripts', [ __CLASS__, 'admin_enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ __CLASS__, 'wp_enqueue_scripts' ] );
		add_filter( 'show_admin_bar', [ __CLASS__, 'show_admin_bar' ] ); // phpcs:ignore WordPressVIPMinimum.UserExperience.AdminBarRemoval.RemovalDetected
		add_filter( 'replace_editor', [ __CLASS__, 'replace_editor' ], 10, 2 );
		add_filter( 'admin_body_class', [ __CLASS__, 'admin_body_class' ], 99 );
		add_filter( 'wp_kses_allowed_html', [ __CLASS__, 'filter_kses_allowed_html' ], 10, 2 );

		// Select the single-web-story.php template for Stories.
		add_filter( 'template_include', [ __CLASS__, 'filter_template_include' ] );

		add_action(
			'web_stories_story_head',
			static function () {
				// Theme support for title-tag is implied for stories. See _wp_render_title_tag().
				echo '<title>' . esc_html( wp_get_document_title() ) . '</title>' . "\n";
			},
			1
		);

		// todo: remove in order to not print any external scripts and styles.
		add_action( 'web_stories_story_head', 'wp_enqueue_scripts', 1 );

		add_filter(
			'the_content',
			static function ( $content ) {
				if ( is_singular( self::POST_TYPE_SLUG ) ) {
					remove_filter( 'the_content', 'wpautop' );
				}

				return $content;
			},
			0
		);

		add_action( 'web_stories_story_head', [ __CLASS__, 'print_schemaorg_metadata' ] );

		// @todo Check if there's something to skip in the new version.
		add_action( 'web_stories_story_head', 'rest_output_link_wp_head', 10, 0 );
		add_action( 'web_stories_story_head', 'wp_resource_hints', 2 );
		add_action( 'web_stories_story_head', 'feed_links', 2 );
		add_action( 'web_stories_story_head', 'feed_links_extra', 3 );
		add_action( 'web_stories_story_head', 'rsd_link' );
		add_action( 'web_stories_story_head', 'wlwmanifest_link' );
		add_action( 'web_stories_story_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
		add_action( 'web_stories_story_head', 'noindex', 1 );
		add_action( 'web_stories_story_head', 'wp_generator' );
		add_action( 'web_stories_story_head', 'rel_canonical' );
		add_action( 'web_stories_story_head', 'wp_shortlink_wp_head', 10, 0 );
		add_action( 'web_stories_story_head', 'wp_site_icon', 99 );
		add_action( 'web_stories_story_head', 'wp_oembed_add_discovery_links' );

		Media::init();
	}

	/**
	 * Filter if show admin bar on single post type.
	 *
	 * @param boolean $show Current value of filter.
	 *
	 * @return bool
	 */
	public static function show_admin_bar( $show ) {
		if ( is_singular( self::POST_TYPE_SLUG ) ) {
			$show = false;
		}

		return $show;
	}

	/**
	 * Highjack editor with custom editor.
	 *
	 * @param bool    $replace Bool if to replace editor or not.
	 * @param WP_Post $post    Current post object.
	 *
	 * @return bool
	 */
	public static function replace_editor( $replace, $post ) {
		if ( self::POST_TYPE_SLUG === get_post_type( $post ) ) {
			$replace = true;
			// In lieu of an action being available to actually load the replacement editor, include it here
			// after the current_screen action has occurred because the replace_editor filter fires twice.
			if ( did_action( 'current_screen' ) ) {
				require_once plugin_dir_path( WEBSTORIES_PLUGIN_FILE ) . 'includes/edit-story.php';
			}
		}

		return $replace;
	}

	/**
	 * Enqueue Google fonts.
	 */
	public static function wp_enqueue_scripts() {
		if ( is_singular( self::POST_TYPE_SLUG ) ) {
			$post = get_post();
			self::load_fonts( $post );
		}
	}

	/**
	 *
	 * Enqueue scripts for the element editor.
	 *
	 * @param string $hook The current admin page.
	 */
	public static function admin_enqueue_scripts( $hook ) {
		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen ) {
			return;
		}

		if ( self::POST_TYPE_SLUG !== $screen->post_type ) {
			return;
		}

		// Only output scripts and styles where in edit screens.
		if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
			return;
		}

		// Force media model to load.
		wp_enqueue_media();

		$asset_file   = plugin_dir_path( WEBSTORIES_PLUGIN_FILE ) . 'assets/js/' . self::WEB_STORIES_SCRIPT_HANDLE . '.asset.php';
		$asset        = is_readable( $asset_file ) ? require $asset_file : [];
		$dependencies = isset( $asset['dependencies'] ) ? $asset['dependencies'] : [];
		$version      = isset( $asset['version'] ) ? $asset['version'] : [];

		wp_enqueue_script(
			self::WEB_STORIES_SCRIPT_HANDLE,
			plugin_dir_url( WEBSTORIES_PLUGIN_FILE ) . 'assets/js/' . self::WEB_STORIES_SCRIPT_HANDLE . '.js',
			$dependencies,
			$version,
			false
		);

		wp_set_script_translations( self::WEB_STORIES_SCRIPT_HANDLE, 'web-stories' );

		$post             = get_post();
		$story_id         = ( $post ) ? $post->ID : null;
		$post_type_object = get_post_type_object( self::POST_TYPE_SLUG );
		$rest_base        = ! empty( $post_type_object->rest_base ) ? $post_type_object->rest_base : $post_type_object->name;
		$post_thumbnails  = get_theme_support( 'post-thumbnails' );

		self::load_admin_fonts( $post );

		// Media settings.
		$max_upload_size = wp_max_upload_size();
		if ( ! $max_upload_size ) {
			$max_upload_size = 0;
		}

		wp_localize_script(
			self::WEB_STORIES_SCRIPT_HANDLE,
			'ampStoriesEditSettings',
			[
				'id'     => 'edit-story',
				'config' => [
					'allowedMimeTypes' => self::get_allowed_mime_types(),
					'postType'         => self::POST_TYPE_SLUG,
					'postThumbnails'   => $post_thumbnails,
					'storyId'          => $story_id,
					'previewLink'      => get_preview_post_link( $story_id ),
					'maxUpload'        => $max_upload_size,
					'api'              => [
						'stories'  => sprintf( '/wp/v2/%s', $rest_base ),
						'media'    => '/wp/v2/media',
						'users'    => '/wp/v2/users',
						'statuses' => '/wp/v2/statuses',
						'fonts'    => '/amp/v1/fonts',
					],
				],
			]
		);

		wp_enqueue_style(
			self::WEB_STORIES_STYLE_HANDLE,
			plugin_dir_url( WEBSTORIES_PLUGIN_FILE ) . 'assets/css/' . self::WEB_STORIES_STYLE_HANDLE . '.css',
			[ 'wp-components' ],
			'1.0.0'
		);

		wp_styles()->add_data( self::WEB_STORIES_STYLE_HANDLE, 'rtl', 'replace' );
	}

	/**
	 * Returns a list of allowed mime types per media type (image, audio, video).
	 *
	 * @return array List of allowed mime types.
	 */
	protected static function get_allowed_mime_types() {
		$default_allowed_mime_types = [
			'image' => [
				'image/png',
				'image/jpeg',
				'image/jpg',
				'image/gif',
			],
			'audio' => [], // todo: support audio uploads.
			'video' => [
				'video/mp4',
			],
		];

		/**
		 * Filter list of allowed mime types.
		 *
		 * This can be used to add additionally supported formats, for example by plugins
		 * that do video transcoding.
		 *
		 * @since 1.3
		 *
		 * @param array Associative array of allowed mime types per media type (image, audio, video).
		 */
		$allowed_mime_types = apply_filters( 'web_stories_allowed_mime_types', $default_allowed_mime_types );

		foreach ( array_keys( $default_allowed_mime_types ) as $media_type ) {
			if ( ! is_array( $allowed_mime_types[ $media_type ] ) || empty( $allowed_mime_types[ $media_type ] ) ) {
				$allowed_mime_types[ $media_type ] = $default_allowed_mime_types[ $media_type ];
			}

			// Only add currently supported mime types.
			$allowed_mime_types[ $media_type ] = array_values( array_intersect( $allowed_mime_types[ $media_type ], wp_get_mime_types() ) );
		}

		return $allowed_mime_types;
	}

	/**
	 * Load font from story data.
	 *
	 * @param WP_Post $post Post Object.
	 */
	public static function load_fonts( $post ) {
		$post_story_data       = json_decode( $post->post_content_filtered, true );
		$post_story_data_pages = isset( $post_story_data['pages'] ) ? $post_story_data['pages'] : $post_story_data;
		$g_fonts               = [];
		if ( $post_story_data_pages ) {
			foreach ( $post_story_data_pages as $page ) {
				foreach ( $page['elements'] as $element ) {
					if ( ! isset( $element['fontFamily'] ) ) {
						continue;
					}

					$font = Fonts::get_font( $element['fontFamily'] );

					if ( $font && isset( $font['gfont'] ) && $font['gfont'] ) {
						if ( isset( $g_fonts[ $font['name'] ] ) && in_array( $element['fontWeight'], $g_fonts[ $font['name'] ], true ) ) {
							continue;
						}
						$g_fonts[ $font['name'] ][] = $element['fontWeight'];
					}
				}
			}

			if ( $g_fonts ) {
				$subsets        = Fonts::get_subsets();
				$g_font_display = '';
				foreach ( $g_fonts as $name => $numbers ) {
					$g_font_display .= $name . ':' . implode( ',', $numbers ) . '|';
				}

				$src = add_query_arg(
					[
						'family'  => rawurlencode( $g_font_display ),
						'subset'  => rawurlencode( implode( ',', $subsets ) ),
						'display' => 'swap',
					],
					Fonts::URL
				);
				wp_enqueue_style(
					self::WEB_STORIES_STYLE_HANDLE . '_fonts',
					$src,
					[],
					WEBSTORIES_VERSION
				);
			}
		}
	}

	/**
	 * Load font in admin from story data.
	 *
	 * @param WP_Post $post Post Object.
	 */
	public static function load_admin_fonts( $post ) {
		$post_story_data       = json_decode( $post->post_content_filtered, true );
		$post_story_data_pages = isset( $post_story_data['pages'] ) ? $post_story_data['pages'] : $post_story_data;
		$fonts                 = [ Fonts::get_font( 'Roboto' ) ];
		$font_slugs            = [ 'roboto' ];

		if ( $post_story_data_pages ) {
			foreach ( $post_story_data_pages as $page ) {
				foreach ( $page['elements'] as $element ) {
					if ( ! isset( $element['fontFamily'] ) ) {
						continue;
					}

					$font = Fonts::get_font( $element['fontFamily'] );
					if ( $font && ! in_array( $font['slug'], $font_slugs, true ) ) {
						$fonts[]      = $font;
						$font_slugs[] = $font['slug'];
					}
				}
			}
		}

		if ( $fonts ) {
			foreach ( $fonts as $font ) {
				if ( isset( $font['src'] ) && $font['src'] ) {
					wp_enqueue_style(
						$font['handle'],
						$font['src'],
						[],
						WEBSTORIES_VERSION
					);
				}
			}
		}
	}

	/**
	 * Filter the list of admin classes.
	 *
	 * @param string $class Current classes.
	 *
	 * @return string $class List of Classes.
	 */
	public static function admin_body_class( $class ) {
		$screen = get_current_screen();

		if ( ! $screen instanceof WP_Screen ) {
			return $class;
		}

		if ( self::POST_TYPE_SLUG !== $screen->post_type ) {
			return $class;
		}

		$class .= ' edit-story ';

		return $class;
	}

	/**
	 * Filter the allowed tags for KSES to allow for amp-story children.
	 *
	 * @param array $allowed_tags Allowed tags.
	 *
	 * @return array Allowed tags.
	 */
	public static function filter_kses_allowed_html( $allowed_tags ) {
		$story_components = [
			'amp-story'                 => [
				'background-audio'     => true,
				'live-story'           => true,
				'live-story-disabled'  => true,
				'poster-landscape-src' => true,
				'poster-portrait-src'  => true,
				'poster-square-src'    => true,
				'publisher'            => true,
				'publisher-logo-src'   => true,
				'standalone'           => true,
				'supports-landscape'   => true,
				'title'                => true,
			],
			'amp-story-page'            => [
				'auto-advance-after' => true,
				'background-audio'   => true,
				'id'                 => true,
			],
			'amp-story-page-attachment' => [
				'theme' => true,
			],
			'amp-story-grid-layer'      => [
				'position' => true,
				'template' => true,
			],
			'amp-story-cta-layer'       => [],
			'amp-img'                   => [
				'alt'                       => true,
				'attribution'               => true,
				'data-amp-bind-alt'         => true,
				'data-amp-bind-attribution' => true,
				'data-amp-bind-src'         => true,
				'data-amp-bind-srcset'      => true,
				'lightbox'                  => true,
				'lightbox-thumbnail-id'     => true,
				'media'                     => true,
				'noloading'                 => true,
				'object-fit'                => true,
				'object-position'           => true,
				'placeholder'               => true,
				'src'                       => true,
				'srcset'                    => true,
			],
			'amp-video'                 => [
				'album'                      => true,
				'alt'                        => true,
				'artist'                     => true,
				'artwork'                    => true,
				'attribution'                => true,
				'autoplay'                   => true,
				'controls'                   => true,
				'controlslist'               => true,
				'crossorigin'                => true,
				'data-amp-bind-album'        => true,
				'data-amp-bind-alt'          => true,
				'data-amp-bind-artist'       => true,
				'data-amp-bind-artwork'      => true,
				'data-amp-bind-attribution'  => true,
				'data-amp-bind-controls'     => true,
				'data-amp-bind-controlslist' => true,
				'data-amp-bind-loop'         => true,
				'data-amp-bind-poster'       => true,
				'data-amp-bind-preload'      => true,
				'data-amp-bind-src'          => true,
				'data-amp-bind-title'        => true,
				'disableremoteplayback'      => true,
				'dock'                       => true,
				'lightbox'                   => true,
				'lightbox-thumbnail-id'      => true,
				'loop'                       => true,
				'media'                      => true,
				'muted'                      => true,
				'noaudio'                    => true,
				'noloading'                  => true,
				'object-fit'                 => true,
				'object-position'            => true,
				'placeholder'                => true,
				'poster'                     => true,
				'preload'                    => true,
				'rotate-to-fullscreen'       => true,
				'src'                        => true,
			],
			'img'                       => [
				'alt'           => true,
				'attribution'   => true,
				'border'        => true,
				'decoding'      => true,
				'height'        => true,
				'importance'    => true,
				'intrinsicsize' => true,
				'ismap'         => true,
				'loading'       => true,
				'longdesc'      => true,
				'sizes'         => true,
				'src'           => true,
				'srcset'        => true,
				'srcwidth'      => true,
			],
		];

		array_merge( $allowed_tags, $story_components );

		foreach ( $allowed_tags as &$allowed_tag ) {
			$allowed_tag['animate-in']          = true;
			$allowed_tag['animate-in-duration'] = true;
			$allowed_tag['animate-in-delay']    = true;
			$allowed_tag['animate-in-after']    = true;
			$allowed_tag['layout']              = true;
		}

		return $allowed_tags;
	}

	/**
	 * Set template for web-story post type.
	 *
	 * @param string $template Template.
	 *
	 * @return string Template.
	 */
	public static function filter_template_include( $template ) {
		if ( is_singular( self::POST_TYPE_SLUG ) && ! is_embed() ) {
			$template = plugin_dir_path( WEBSTORIES_PLUGIN_FILE ) . 'includes/templates/single-web-story.php';
		}

		return $template;
	}

	/**
	 * Get the publisher logo.
	 *
	 * @link https://developers.google.com/search/docs/data-types/article#logo-guidelines
	 * @link https://amp.dev/documentation/components/amp-story/#publisher-logo-src-guidelines
	 *
	 * @return string Publisher logo image URL. WordPress logo if no site icon or custom logo defined, and no logo provided via 'amp_site_icon_url' filter.
	 */
	private static function get_publisher_logo() {
		$logo_image_url = null;

		// This should be square, at least 96px in width/height. The 512 is used because the site icon would have this size generated.
		$logo_width  = 512;
		$logo_height = 512;

		// Use the Custom Logo if set, but only if it is square.
		$custom_logo_id = get_theme_mod( 'custom_logo' );
		if ( has_custom_logo() && $custom_logo_id ) {
			$custom_logo_img = wp_get_attachment_image_src( $custom_logo_id, [ $logo_width, $logo_height ], false );
			if ( $custom_logo_img && ( $custom_logo_img[2] === $custom_logo_img[1] ) ) {
				$logo_image_url = $custom_logo_img[0];
			}
		}

		// Try Site Icon, though it is not ideal for non-Story because it should be square.
		$site_icon_id = get_option( 'site_icon' );
		if ( empty( $logo_image_url ) && $site_icon_id ) {
			$site_icon_src = wp_get_attachment_image_src( $site_icon_id, [ $logo_width, $logo_height ], false );
			if ( ! empty( $site_icon_src ) ) {
				$logo_image_url = $site_icon_src[0];
			}
		}

		// Fallback to serving the WordPress logo.
		if ( empty( $logo_image_url ) ) {
			$logo_image_url = plugin_dir_url( WEBSTORIES_PLUGIN_FILE ) . 'assets/images/fallback-wordpress-publisher-logo.png';
		}

		/**
		 * Filters the publisher's logo.
		 *
		 * This should point to a square image.
		 *
		 * @param string $logo_image_url URL to the publisher's logo.
		 */
		return apply_filters( 'web_stories_publisher_logo', $logo_image_url );
	}

	/**
	 * Prints the schema.org metadata on the single story template.
	 */
	public static function print_schemaorg_metadata() {
		$metadata = self::get_schemaorg_metadata();

		?>
		<script type="application/ld+json"><?php echo wp_json_encode( $metadata, JSON_UNESCAPED_UNICODE ); ?></script>
		<?php
	}

	/**
	 * Get schema.org metadata for the current query.
	 *
	 * @return array $metadata All schema.org metadata for the post.
	 */
	public static function get_schemaorg_metadata() {
		$metadata = [
			'@context'  => 'http://schema.org',
			'publisher' => [
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			],
		];

		$publisher_logo = self::get_publisher_logo();

		if ( $publisher_logo ) {
			$metadata['publisher']['logo'] = $publisher_logo;
		}

		$post = get_queried_object();

		$metadata = array_merge(
			$metadata,
			[
				'@type'            => 'BlogPosting',
				'mainEntityOfPage' => get_permalink(),
				'headline'         => get_the_title(),
				'datePublished'    => mysql2date( 'c', $post->post_date_gmt, false ),
				'dateModified'     => mysql2date( 'c', $post->post_modified_gmt, false ),
			]
		);

		$post_author = get_userdata( $post->post_author );

		if ( $post_author ) {
			$metadata['author'] = [
				'@type' => 'Person',
				'name'  => html_entity_decode( $post_author->display_name, ENT_QUOTES, get_bloginfo( 'charset' ) ),
			];
		}

		if ( has_post_thumbnail( $post->ID ) ) {
			$metadata['image'] = wp_get_attachment_image_url( get_post_thumbnail_id( $post->ID ), 'full' );
		}

		/**
		 * Filters the schema.org metadata for a given story.
		 *
		 * @param array $metadata The structured data.
		 * @param WP_Post $post The current post object.
		 */
		return apply_filters( 'web_stories_story_schema_metadata', $metadata, $post );
	}
}
