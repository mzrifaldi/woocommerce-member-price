<?php
/*
Plugin Name: WooCommerce Member Price Pro
Description: Atur harga member berdasarkan harga tetap atau diskon persentase. Role "member" otomatis dibuat.
Version: 1.5
Author: Zam Rifaldi
Author URI: https://www.linkedin.com/in/mzrifaldi/
*/

if (!defined('ABSPATH')) exit;

// ==================================================
// Tambahkan role 'member' saat plugin diaktifkan
// ==================================================
register_activation_hook(__FILE__, function() {
    if (!get_role('member')) {
        add_role(
            'member',
            'Member',
            array(
                'read' => true,
                'level_0' => true
            )
        );
    }
});

// ==================================================
// Load admin meta box
// ==================================================
if (is_admin()) {
    require_once plugin_dir_path(__FILE__) . 'admin/admin-meta-box.php';
}

// ==================================================
// Filter harga WooCommerce untuk role member
// ==================================================
add_filter('woocommerce_product_get_price', 'wcmp_role_based_price', 10, 2);
add_filter('woocommerce_product_get_regular_price', 'wcmp_role_based_price', 10, 2);

function wcmp_role_based_price($price, $product) {
    if (is_admin()) return $price;

    $user = wp_get_current_user();

    if (in_array('member', (array) $user->roles)) {
        $fixed_price      = get_post_meta($product->get_id(), '_member_price', true);
        $discount_percent = get_post_meta($product->get_id(), '_member_discount', true);

        // Prioritas: harga tetap > diskon
        if ($fixed_price && $fixed_price > 0) {
            $price = $fixed_price;
        } elseif ($discount_percent && $discount_percent > 0) {
            $price = $price * (1 - floatval($discount_percent) / 100);
        }
    }

    return $price;
}

// ==================================================
// Tambahkan label + format harga member sesuai WooCommerce
// ==================================================
add_filter('woocommerce_get_price_html', 'wcmp_custom_price_label', 20, 2);
function wcmp_custom_price_label($price_html, $product) {
    $user = wp_get_current_user();

    if (in_array('member', (array) $user->roles)) {
        $member_price    = wc_get_price_to_display($product);
        $formatted_price = wc_price($member_price);

        // Ambil harga reguler asli produk
        $regular_price = $product->get_regular_price();

        if ($regular_price && $regular_price > $member_price) {
            // Tambahkan harga coret jika lebih tinggi
            $formatted_price = '<del>' . wc_price($regular_price) . '</del> <ins>' . $formatted_price . '</ins>';
        }

        // Tambahkan label di atas harga
        $price_html = '
            <div class="wcmp-member-label" style="margin-bottom:6px;color:#BA2C2B;font-weight:600;">
                Selamat anda memakai harga member
            </div>
            <span class="price">' . $formatted_price . '</span>
        ';
    }

    return $price_html;
}

// ==================================================
// Enqueue CSS plugin
// ==================================================
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'wcmp-member-style',
        plugin_dir_url(__FILE__) . 'assets/css/member-price.css',
        array(),
        '1.0.0'
    );
});
