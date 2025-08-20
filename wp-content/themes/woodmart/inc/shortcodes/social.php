<?php if ( ! defined( 'WOODMART_THEME_DIR' ) ) exit( 'No direct script access allowed' );

/**
* ------------------------------------------------------------------------------------------------
* Share and follow buttons shortcode
* ------------------------------------------------------------------------------------------------
*/

if( ! function_exists( 'woodmart_shortcode_social' )) {
	function woodmart_shortcode_social( $atts, $content = '' ) {
		$classes = apply_filters( 'vc_shortcodes_css_class', '', '', $atts );

		$links_atts = array(
			'fb_link'         => '',
			'twitter_link'    => '',
			'zalo_link'    => '',
			'isntagram_link'  => '',
			'threads_link'    => '',
			'pinterest_link'  => '',
			'youtube_link'    => '',
			'tumblr_link'     => '',
			'linkedin_link'   => '',
			'vimeo_link'      => '',
			'flickr_link'     => '',
			'github_link'     => '',
			'dribbble_link'   => '',
			'behance_link'    => '',
			'soundcloud_link' => '',
			'spotify_link'    => '',
			'ok_link'         => '',
			'vk_link'         => '',
			'whatsapp_link'   => '',
			'snapchat_link'   => '',
			'tg_link'         => '',
			'viber_link'      => '',
			'tiktok_link'     => '',
			'discord_link'    => '',
			'yelp_link'       => '',
		);

		$default_atts = array(
			'show_label'          => 'no',
			'label_text'          => esc_html__( 'Share: ', 'woodmart' ),
			'is_element'          => false,
			'layout'              => '',
			'type'                => 'share',
			'social_links_source' => 'theme_settings',
			'align'               => 'center',
			'tooltip'             => 'no',
			'style'               => 'default',
			'size'                => 'default',
			'form'                => 'circle',
			'color'               => '',
			'css_animation'       => 'none',
			'el_class'            => '',
			'el_id'               => '',
			'title_classes'       => '',
			'page_link'           => false,
			'elementor'           => false,
			'sticky'              => false,
			'css'                 => '',
		);

		$atts = shortcode_atts( array_merge( $default_atts, $links_atts ), $atts );

		if ( 'follow' === $atts['type'] && 'theme_settings' === $atts['social_links_source'] ) {
			foreach ( array_keys( $links_atts ) as $link_option_name ) {
				$atts[ $link_option_name ] = woodmart_get_opt( $link_option_name, '' );
			}
		}

		extract( $atts );

		$target        = '_blank';
		$title_classes = $title_classes ? ' ' . $title_classes : '';
		$classes      .= ' wd-social-icons';

		if ( function_exists( 'vc_shortcode_custom_css_class' ) ) {
			$classes .= ' ' . vc_shortcode_custom_css_class( $css );
		}

		$classes .= woodmart_get_old_classes( ' woodmart-social-icons' );
		$classes .= ! empty( $layout ) ? ' wd-layout-' . $layout : '';
		$classes .= $style ? ' wd-style-' . $style : '';
		$classes .= $size ? ' wd-size-' . $size : '';
		$classes .= ' social-' . $type;
		$classes .= $form ? ' wd-shape-' . $form : '';
		$classes .= ( $el_class ) ? ' ' . $el_class : '';

		if ( $color ) {
			$classes .= ' color-scheme-' . $color;
		}

		$classes .= woodmart_get_css_animation( $css_animation );

		if ( $align ) {
			$classes .= ' text-' . $align;
		}

		$thumb_id   = get_post_thumbnail_id();
		$thumb_url  = wp_get_attachment_image_src( $thumb_id, 'thumbnail-size', true );
		$page_title = get_the_title();

		if ( ! $page_link ) {
			$page_link = get_the_permalink();
		}

		if ( woodmart_woocommerce_installed() ) {
			if ( is_shop() ) {
				$page_link = get_permalink( get_option( 'woocommerce_shop_page_id' ) );
			} elseif ( is_product_category() || is_category() ) {
				$page_link = get_category_link( get_queried_object()->term_id );
			} elseif ( is_tax() ) {
				$page_link = get_term_link( get_queried_object()->term_id );
			}
		}

		if ( is_home() && ! is_front_page() ) {
			$page_link = get_permalink( get_option( 'page_for_posts' ) );
		}

		if ( ! $elementor ) {
			ob_start();
		}

		woodmart_enqueue_inline_style( 'social-icons' );

		if ( 'default' !== $style ) {
			woodmart_enqueue_inline_style( 'social-icons-styles' );
		}
		?>
			<div
			<?php if ( $el_id ) : ?>
			id="<?php echo esc_attr( $el_id ); ?>"
			<?php endif ?>
			class="<?php echo esc_attr( $classes ); ?>">
				<?php echo do_shortcode( $content ); ?>

				<?php if ( 'yes' === $show_label && $label_text ) : ?>
					<span class="wd-label<?php echo esc_attr( $title_classes ); ?>"><?php echo esc_html( $label_text ); ?></span>
				<?php endif; ?>

				<?php if ( ( $type == 'share' && woodmart_get_opt('share_fb') ) || ( $type == 'follow' && $fb_link != '')): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $fb_link ) : 'https://www.facebook.com/sharer/sharer.php?u=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-facebook" aria-label="<?php esc_attr_e( 'Facebook social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Facebook', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( ( $type == 'share' && woodmart_get_opt('share_twitter') ) || ( $type == 'follow' && $twitter_link != '')): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $twitter_link ) : 'https://x.com/share?url=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-twitter" aria-label="<?php esc_attr_e( 'X social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('X', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( 'follow' === $type && '' !== $zalo_link ) : ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo esc_url( $zalo_link ); ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php echo 'yes' === $tooltip ? 'wd-tooltip' : ''; ?> wd-social-icon social-zalo" aria-label="<?php esc_attr_e( 'Bluesky social link', 'woodmart' ); ?>">
						<span class="wd-icon"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="24" viewBox="0 0 24 24"> <image id="zalo" width="24" height="24" xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFgAAABYCAYAAABxlTA0AAAACXBIWXMAAAsTAAALEwEAmpwYAAAFGmlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSLvu78iIGlkPSJXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQiPz4gPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iQWRvYmUgWE1QIENvcmUgNi4wLWMwMDIgNzkuMTY0NDYwLCAyMDIwLzA1LzEyLTE2OjA0OjE3ICAgICAgICAiPiA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPiA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIiB4bWxuczp4bXA9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC8iIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgeG1sbnM6cGhvdG9zaG9wPSJodHRwOi8vbnMuYWRvYmUuY29tL3Bob3Rvc2hvcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RFdnQ9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZUV2ZW50IyIgeG1wOkNyZWF0b3JUb29sPSJBZG9iZSBQaG90b3Nob3AgMjEuMiAoTWFjaW50b3NoKSIgeG1wOkNyZWF0ZURhdGU9IjIwMjAtMTItMTFUMjM6Mzg6MDcrMDc6MDAiIHhtcDpNb2RpZnlEYXRlPSIyMDIyLTAzLTAzVDE2OjEwOjU4KzA3OjAwIiB4bXA6TWV0YWRhdGFEYXRlPSIyMDIyLTAzLTAzVDE2OjEwOjU4KzA3OjAwIiBkYzpmb3JtYXQ9ImltYWdlL3BuZyIgcGhvdG9zaG9wOkNvbG9yTW9kZT0iMyIgcGhvdG9zaG9wOklDQ1Byb2ZpbGU9InNSR0IgSUVDNjE5NjYtMi4xIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjcyNjczYjExLTQwMWIt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"/></svg></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e( 'Zalo', 'woodmart' ); ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( ( $type == 'share' && woodmart_get_opt('share_email') ) || ( $type == 'follow' && woodmart_get_opt( 'social_email_links' ) ) ): ?>
					<a rel="noopener noreferrer nofollow" href="mailto:<?php echo '?subject=' . esc_html__('Check%20this%20', 'woodmart') . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-email" aria-label="<?php esc_attr_e( 'Email social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Email', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $isntagram_link != ''): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $isntagram_link ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-instagram" aria-label="<?php esc_attr_e( 'Instagram social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Instagram', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( 'follow' === $type && '' !== $threads_link ) : ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo esc_url( $threads_link ); ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php echo 'yes' === $tooltip ? 'wd-tooltip' : ''; ?> wd-social-icon social-threads" aria-label="<?php esc_attr_e( 'Threads social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e( 'Threads', 'woodmart' ); ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $youtube_link != ''): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $youtube_link ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-youtube" aria-label="<?php esc_attr_e( 'YouTube social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('YouTube', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( ( $type == 'share' && woodmart_get_opt('share_pinterest') ) || ( $type == 'follow' && $pinterest_link != '' ) ): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $pinterest_link ) : 'https://pinterest.com/pin/create/button/?url=' . $page_link . '&media=' . $thumb_url[0] . '&description=' . urlencode( $page_title ); ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-pinterest" aria-label="<?php esc_attr_e( 'Pinterest social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Pinterest', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $tumblr_link != ''): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $tumblr_link ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-tumblr" aria-label="<?php esc_attr_e( 'Tumblr social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Tumblr', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( ( $type == 'share' && woodmart_get_opt('share_linkedin') ) || ( $type == 'follow' && $linkedin_link != '' ) ): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $linkedin_link ) : 'https://www.linkedin.com/shareArticle?mini=true&url=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-linkedin" aria-label="<?php esc_attr_e( 'Linkedin social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('linkedin', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $vimeo_link != ''): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $vimeo_link ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-vimeo" aria-label="<?php esc_attr_e( 'Vimeo social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Vimeo', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $flickr_link != ''): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $flickr_link ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-flickr" aria-label="<?php esc_attr_e( 'Flickr social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Flickr', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $github_link != ''): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $github_link ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-github" aria-label="<?php esc_attr_e( 'GitHub social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('GitHub', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $dribbble_link != ''): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $dribbble_link ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-dribbble" aria-label="<?php esc_attr_e( 'Dribbble social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Dribbble', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $behance_link != ''): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $behance_link ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-behance" aria-label="<?php esc_attr_e( 'Behance social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Behance', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $soundcloud_link != ''): ?>
						<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $soundcloud_link ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-soundcloud" aria-label="<?php esc_attr_e( 'Soundcloud social link', 'woodmart' ); ?>">
							<span class="wd-icon"></span>
							<?php if ( $sticky ) : ?>
								<span class="wd-icon-name"><?php esc_html_e('Soundcloud', 'woodmart') ?></span>
							<?php endif; ?>
						</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $spotify_link != ''): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $spotify_link ) : '' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-spotify" aria-label="<?php esc_attr_e( 'Spotify social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Spotify', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( ( $type == 'share' && woodmart_get_opt('share_ok') ) || ( $type == 'follow' && $ok_link != '' ) ): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $ok_link ) : 'https://connect.ok.ru/offer?url=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-ok" aria-label="<?php esc_attr_e( 'Odnoklassniki social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Odnoklassniki', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'share' && woodmart_get_opt('share_whatsapp') || ( $type == 'follow' && $whatsapp_link != '' ) ): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $whatsapp_link ) : 'https://api.whatsapp.com/send?text=' . urlencode( $page_link ); ?>" target="<?php echo esc_attr( $target ); ?>" class="wd-hide-md <?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-whatsapp" aria-label="<?php esc_attr_e( 'WhatsApp social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('WhatsApp', 'woodmart') ?></span>
						<?php endif; ?>
					</a>

					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $whatsapp_link ) : 'whatsapp://send?text=' . urlencode( $page_link ); ?>" target="<?php echo esc_attr( $target ); ?>" class="wd-hide-lg <?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-whatsapp" aria-label="<?php esc_attr_e( 'WhatsApp social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('WhatsApp', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'share' && woodmart_get_opt('share_vk') || ( $type == 'follow' && $vk_link != '' ) ): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $vk_link ) : 'https://vk.com/share.php?url=' . $page_link . '&image=' . $thumb_url[0] . '&title=' . $page_title; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-vk" aria-label="<?php esc_attr_e( 'VK social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('VK', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $snapchat_link != '' ): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo esc_url( $snapchat_link ); ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-snapchat" aria-label="<?php esc_attr_e( 'Snapchat social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Snapchat', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $tiktok_link != '' ): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo esc_url( $tiktok_link ); ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-tiktok" aria-label="<?php esc_attr_e( 'TikTok social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('TikTok', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && $discord_link != '' ): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo esc_url( $discord_link ); ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-discord" aria-label="<?php esc_attr_e( 'Discord social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Discord', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'follow' && '' !== $yelp_link ) : ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo esc_url( $yelp_link ); ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php echo 'yes' === $tooltip ? 'wd-tooltip' : ''; ?> wd-social-icon social-yelp" aria-label="<?php esc_attr_e( 'Yelp social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e( 'Yelp', 'woodmart' ); ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( $type == 'share' && woodmart_get_opt('share_tg') || ( $type == 'follow' && $tg_link != '' ) ): ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo 'follow' === $type ? esc_url( $tg_link ) : 'https://telegram.me/share/url?url=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-tg" aria-label="<?php esc_attr_e( 'Telegram social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Telegram', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

				<?php if ( ( $type == 'share' && woodmart_get_opt( 'share_viber' ) ) || ( $type == 'follow' && $viber_link ) ) : ?>
					<a rel="noopener noreferrer nofollow" href="<?php echo $type == 'follow' ? $viber_link : 'viber://forward?text=' . $page_link; ?>" target="<?php echo esc_attr( $target ); ?>" class="<?php if( $tooltip == "yes" ) echo 'wd-tooltip'; ?> wd-social-icon social-viber" aria-label="<?php esc_attr_e( 'Viber social link', 'woodmart' ); ?>">
						<span class="wd-icon"></span>
						<?php if ( $sticky ) : ?>
							<span class="wd-icon-name"><?php esc_html_e('Viber', 'woodmart') ?></span>
						<?php endif; ?>
					</a>
				<?php endif ?>

			</div>

		<?php
		if ( ! $elementor ) {
			$output = ob_get_contents();
			ob_end_clean();

			return $output;
		}
	}
}
