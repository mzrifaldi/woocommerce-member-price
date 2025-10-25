<?php
if (!defined('ABSPATH')) exit;

// Tambah meta box
add_action('add_meta_boxes', function() {
    add_meta_box(
        'wcmp_member_price',
        'Harga Member',
        'wcmp_member_price_meta_box',
        'product',
        'side',
        'default'
    );
});

// Konten meta box
function wcmp_member_price_meta_box($post) {
    wp_nonce_field('wcmp_save_member_price', 'wcmp_member_price_nonce');

    $fixed = get_post_meta($post->ID, '_member_price', true);
    $discount = get_post_meta($post->ID, '_member_discount', true);

    echo '<p>Harga tetap untuk member:</p>';
    echo '<input type="number" name="wcmp_member_price" value="' . esc_attr($fixed) . '" style="width:100%;" min="0">';

    echo '<p style="margin-top:10px;">Diskon (%) untuk member:</p>';
    echo '<input type="number" name="wcmp_member_discount" value="' . esc_attr($discount) . '" style="width:100%;" min="0" max="100">';
    echo '<p style="font-size:12px; color:#666;">Harga tetap prioritas, diskon berlaku jika harga tetap kosong.</p>';
}

// Simpan meta box
add_action('save_post', function($post_id) {
    if (!isset($_POST['wcmp_member_price_nonce'])) return;
    if (!wp_verify_nonce($_POST['wcmp_member_price_nonce'], 'wcmp_save_member_price')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (isset($_POST['wcmp_member_price'])) {
        update_post_meta($post_id, '_member_price', floatval($_POST['wcmp_member_price']));
    }

    if (isset($_POST['wcmp_member_discount'])) {
        update_post_meta($post_id, '_member_discount', floatval($_POST['wcmp_member_discount']));
    }
});
