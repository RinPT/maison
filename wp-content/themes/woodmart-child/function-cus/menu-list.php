<?php
// Global variables to track assets
global $menu_list_assets_loaded, $menu_list_script_loaded;

// Render HTML function
function render_menu_list($atts) {
    $atts = shortcode_atts([
        'category'       => '',
        'id'             => '',
        'posts_per_page' => 5,
    ], $atts, 'menu_list');

    $post_ids = [];
    if (!empty($atts['id'])) {
        $post_ids = array_filter(array_map('intval', explode(',', $atts['id'])));
    }

    $tax_query = [];
    if (!empty($atts['category'])) {
        $term_ids = array_filter(array_map('intval', explode(',', $atts['category'])));
        if (!empty($term_ids)) {
            $tax_query[] = [
                'taxonomy' => 'menu-category',
                'field'    => 'term_id',
                'terms'    => $term_ids,
            ];
        }
    }

    $args = [
        'post_type'      => 'menu',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'no_found_rows'  => false,
    ];

    if (!empty($post_ids)) {
        $args['post__in'] = $post_ids;
        $args['orderby']  = 'post__in';
    } else {
        $args['orderby'] = 'date';
        $args['order']   = 'DESC';
    }

    if (!empty($tax_query)) {
        $args['tax_query'] = $tax_query;
    }

    $q = new WP_Query($args);
    $posts_per_page = intval($atts['posts_per_page']);
    $total_pages = $posts_per_page > 0 ? ceil($q->found_posts / $posts_per_page) : 1;

    ob_start();

    if (!$q->have_posts()) {
        echo '<div class="menu-list empty">Không có thực đơn phù hợp.</div>';
    } else {
        echo '<div class="menu-list-wrap">';

        $post_index = 0;
        while ($q->have_posts()) {
            $q->the_post();

            $current_page = $posts_per_page > 0 ? intval(floor($post_index / $posts_per_page)) + 1 : 1;
            $display_style = ($current_page == 1) ? '' : ' style="display:none;"';

            $subtitle_menu = get_field('subtitle_menu');
            $subtitle_menu_desc = get_field('subtitle_menu_desc');

            echo '<article class="menu-item menu-page-' . $current_page . '"' . $display_style . ' itemscope itemtype="https://schema.org/Menu">';
            echo '<div class="menu-item__header">';
            echo '<h3 class="menu-item__static_title">Menu</h3>';
            echo '<div class="menu-item__group-title"><h3 class="menu-item__title" itemprop="name">' . esc_html(get_the_title()) . '</h3>';
            if ($subtitle_menu) echo '<div class="menu-item__subtitle">' . esc_html($subtitle_menu) . '</div>';
            if ($subtitle_menu_desc) echo '<div class="menu-item__desc">' . wp_kses_post($subtitle_menu_desc) . '</div></div>';
            echo '</div>';

            if (have_rows('menu_detail')) {
                echo '<div class="menu-item__details">';
                while (have_rows('menu_detail')) {
                    the_row();
                    $menu_name = get_sub_field('menu_name');
                    echo '<section class="menu-detail" itemprop="hasMenuSection" itemscope itemtype="https://schema.org/MenuSection">';
                    if ($menu_name) echo '<h3 class="menu-detail__title" itemprop="name">' . esc_html($menu_name) . '</h3>';

                    if (have_rows('menu_list')) {
                        echo '<ul class="menu-detail__list">';
                        while (have_rows('menu_list')) {
                            the_row();
                            $name   = get_sub_field('menu_list_name');
                            $price  = get_sub_field('menu_list_price');
                            $detail = get_sub_field('menu_list_detail');

                            echo '<li class="menu-detail__item" itemprop="hasMenuItem" itemscope itemtype="https://schema.org/MenuItem">';
                            echo '<div class="menu-detail__item-header">';
                            if ($name) echo '<span class="menu-detail__item-name" itemprop="name">' . esc_html($name) . '</span>';
                            if ($price !== '') {
                                echo '<span class="menu-detail__item-price" itemprop="offers" itemscope itemtype="https://schema.org/Offer">';
                                echo '<meta itemprop="priceCurrency" content="VND" />';
                                echo '<span itemprop="price">' . esc_html($price) . '</span>';
                                echo '</span>';
                            }
                            echo '</div>';
                            if ($detail) echo '<div class="menu-detail__item-desc" itemprop="description">' . wp_kses_post($detail) . '</div>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    }
                    echo '</section>';
                }
                echo '</div>';
            }
            echo '</article>';

            $post_index++;
        }
        echo '</div>';

        if ($total_pages > 1) {
            echo '<div class="menu-pagination">';
            echo '<a href="#" data-page="prev" class="prev page-numbers" style="display:none;">« Trước</a>';

            for ($i = 1; $i <= $total_pages; $i++) {
                $class = $i === 1 ? 'page-numbers current' : 'page-numbers';
                echo '<a href="#" data-page="' . $i . '" class="' . $class . '">' . $i . '</a>';
            }

            echo '<a href="#" data-page="next" class="next page-numbers"' . ($total_pages <= 1 ? ' style="display:none;"' : '') . '>Tiếp »</a>';
            echo '</div>';
        }
    }

    wp_reset_postdata();
    return ob_get_clean();
}

// Main shortcode
add_shortcode('menu_list', function($atts){
    global $menu_list_assets_loaded, $menu_list_script_loaded;

    static $instance_counter = 0;
    $instance_counter++;
    $unique_id = 'menu-list-' . $instance_counter;

    $atts = shortcode_atts([
        'category'       => '',
        'id'             => '',
        'posts_per_page' => 5,
    ], $atts, 'menu_list');

    ob_start();

    // Load CSS only once
    if (empty($menu_list_assets_loaded)) {
        $menu_list_assets_loaded = true;
        echo '<style>
        .menu-item__details {
            max-height: 480px;
            overflow: hidden;
            position: relative;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .menu-item__details::-webkit-scrollbar {
            display: none;
        }
        .custom-scrollbar {
            position: absolute;
            right: 2px;
            top: 2px;
            bottom: 2px;
            width: 8px;
            background: rgba(241, 241, 241, 0.8);
            border-radius: 4px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 10;
        }
        /* Scrollbar bên trái khi có class cha */
        .scroll-position-left .menu-item__details .custom-scrollbar,
        .menu-item__details.scroll-position-left .custom-scrollbar {
            right: auto;
            left: 2px;
        }
        .menu-item__details:hover .custom-scrollbar {
            opacity: 1;
        }
        .custom-scrollbar-thumb {
            position: absolute;
            right: 0;
            width: 100%;
            background: rgba(193, 193, 193, 0.9);
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.3s ease;
            min-height: 20px;
        }
        .custom-scrollbar-thumb:hover {
            background: rgba(161, 161, 161, 0.9);
        }
        .custom-scrollbar-thumb.dragging {
            background: rgba(136, 136, 136, 0.9);
        }
        .menu-item__details-content {
            max-height: 480px;
            overflow-y: auto;
            padding-right: 15px;
            margin-right: -15px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        /* Content padding bên trái khi có class cha */
        .scroll-position-left .menu-item__details .menu-item__details-content,
        .menu-item__details.scroll-position-left .menu-item__details-content {
            padding-right: 0;
            margin-right: 0;
            padding-left: 15px;
            margin-left: -15px;
        }
        .menu-item__details-content::-webkit-scrollbar {
            display: none;
        }
        </style>';
    }

    echo '<div class="menu-list-ajax-wrapper" id="' . $unique_id . '">';
    echo '<div class="menu-list-ajax-content">';
    echo render_menu_list($atts);
    echo '</div>';
    echo '</div>';

    // Load JavaScript only once
    if (empty($menu_list_script_loaded)) {
        $menu_list_script_loaded = true;
        ?>
        <script>
            jQuery(document).ready(function($){
                function initCustomScrollbar(container) {
                    var $container = $(container);
                    if ($container.data('scrollbar-init')) return;

                    $container.data('scrollbar-init', true);
                    var $content = $container.children().wrapAll('<div class="menu-item__details-content"></div>').parent();
                    var $scrollbar = $('<div class="custom-scrollbar"><div class="custom-scrollbar-thumb"></div></div>');

                    $container.append($scrollbar);
                    var $thumb = $scrollbar.find('.custom-scrollbar-thumb');
                    var isDragging = false;

                    // Kiểm tra class scroll-position-left ở chính element hoặc các element cha
                    var isLeftPosition = $container.hasClass('scroll-position-left') ||
                        $container.parents('.scroll-position-left').length > 0;

                    function updateScrollbar() {
                        var containerHeight = $container.height();
                        var contentHeight = $content[0].scrollHeight;
                        var scrollRatio = containerHeight / contentHeight;

                        if (scrollRatio >= 1) {
                            $scrollbar.hide();
                            return;
                        }

                        $scrollbar.show();
                        var thumbHeight = Math.max(scrollRatio * containerHeight, 20);
                        var scrollTop = $content.scrollTop();
                        var maxScrollTop = contentHeight - containerHeight;
                        var thumbTop = maxScrollTop > 0 ? (scrollTop / maxScrollTop) * (containerHeight - thumbHeight) : 0;

                        $thumb.height(thumbHeight).css('top', thumbTop);
                    }

                    $content.on('scroll', updateScrollbar);

                    $thumb.on('mousedown', function(e) {
                        isDragging = true;
                        var startY = e.clientY;
                        var startTop = parseInt($thumb.css('top'));
                        $thumb.addClass('dragging');

                        $(document).on('mousemove.scrollbar', function(e) {
                            if (!isDragging) return;

                            var deltaY = e.clientY - startY;
                            var newTop = startTop + deltaY;
                            var containerHeight = $container.height();
                            var thumbHeight = $thumb.height();
                            var maxTop = containerHeight - thumbHeight;
                            var clampedTop = Math.max(0, Math.min(newTop, maxTop));

                            $thumb.css('top', clampedTop);

                            var scrollRatio = maxTop > 0 ? clampedTop / maxTop : 0;
                            var contentHeight = $content[0].scrollHeight;
                            var maxScrollTop = contentHeight - containerHeight;
                            var newScrollTop = scrollRatio * maxScrollTop;

                            $content.scrollTop(newScrollTop);
                        });

                        $(document).on('mouseup.scrollbar', function() {
                            isDragging = false;
                            $thumb.removeClass('dragging');
                            $(document).off('.scrollbar');
                        });

                        e.preventDefault();
                    });

                    // Click to scroll
                    $scrollbar.on('click', function(e) {
                        if (e.target === $thumb[0]) return;

                        var clickY = e.offsetY;
                        var containerHeight = $container.height();
                        var thumbHeight = $thumb.height();
                        var newThumbTop = clickY - thumbHeight / 2;
                        var maxTop = containerHeight - thumbHeight;
                        var clampedTop = Math.max(0, Math.min(newThumbTop, maxTop));

                        $thumb.css('top', clampedTop);

                        var scrollRatio = maxTop > 0 ? clampedTop / maxTop : 0;
                        var contentHeight = $content[0].scrollHeight;
                        var maxScrollTop = contentHeight - containerHeight;
                        var newScrollTop = scrollRatio * maxScrollTop;

                        $content.scrollTop(newScrollTop);
                    });

                    setTimeout(updateScrollbar, 100);
                }

                // Initialize scrollbars
                $('.menu-item__details').each(function() {
                    initCustomScrollbar(this);
                });

                // Pagination handler
                $(document).on('click', '.menu-list-ajax-wrapper .menu-pagination a', function(e){
                    e.preventDefault();

                    var $this = $(this);
                    var wrapper = $this.closest('.menu-list-ajax-wrapper');
                    var targetPage = $this.data('page');
                    var currentPage = parseInt(wrapper.find('.menu-pagination .current').text()) || 1;
                    var totalPages = wrapper.find('.menu-pagination .page-numbers').not('.prev, .next').length;

                    if (targetPage === 'prev') {
                        targetPage = Math.max(1, currentPage - 1);
                    } else if (targetPage === 'next') {
                        targetPage = Math.min(totalPages, currentPage + 1);
                    }

                    if (targetPage === currentPage || targetPage < 1 || targetPage > totalPages) return;

                    wrapper.find('.menu-item').hide();
                    wrapper.find('.menu-page-' + targetPage).show();

                    wrapper.find('.menu-pagination .page-numbers').removeClass('current');
                    wrapper.find('.menu-pagination [data-page="' + targetPage + '"]').addClass('current');

                    wrapper.find('.menu-pagination .prev').toggle(targetPage > 1);
                    wrapper.find('.menu-pagination .next').toggle(targetPage < totalPages);

                    // Update scrollbars
                    setTimeout(function() {
                        wrapper.find('.menu-item__details').each(function() {
                            var $container = $(this);
                            var $content = $container.find('.menu-item__details-content');
                            if ($content.length) {
                                $content.trigger('scroll');
                            }
                        });
                    }, 100);
                });
            });
        </script>
        <?php
    }

    return ob_get_clean();
});
?>