<?php
/**
 * Plugin Name: نمایش محصول در مقالات
 * Description: با استفاده از شرت کد [custom_product sku="YOUR_SKU_HERE"] و قرار دادن آن در لابه لای متن خود محصولات رو نمایش دهید ٫ فقط باید sku محصول را وارد کنید
 * Version: 1.1
 * Author: pooya
 */

if (!defined('ABSPATH')) {
    exit;
}

// اضافه کردن CSS افزونه
function my_custom_plugin_enqueue_styles() {
    wp_enqueue_style('my-custom-style', plugin_dir_url(__FILE__) . 'css/custom-style.css');
}
add_action('wp_enqueue_scripts', 'my_custom_plugin_enqueue_styles');

// شورت کد برای نمایش محصول بر اساس SKU
function custom_product_shortcode($atts) {
    $atts = shortcode_atts(array(
        'sku' => '',
    ), $atts, 'custom_product');

    if (empty($atts['sku'])) {
        return 'لطفاً SKU محصول را وارد کنید.';
    }

    $args = array(
        'post_type' => 'product',
        'meta_query' => array(
            array(
                'key' => '_sku',
                'value' => $atts['sku'],
                'compare' => '='
            )
        )
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $output = '<div class="custom-product-box">';
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            $product_image = get_the_post_thumbnail($product->get_id(), 'medium');
            $product_title = get_the_title();
            $product_link = get_permalink();
            $product_attributes = $product->get_attributes();

            // استخراج اولین 4 ویژگی محصول
            $attributes = array_slice($product_attributes, 0, 4);
            $attributes_html = '';
            foreach ($attributes as $attribute) {
                $attribute_label = wc_attribute_label($attribute->get_name());
                $attribute_values = wc_get_product_terms($product->get_id(), $attribute->get_name(), array('fields' => 'names'));
                $attributes_html .= '<li>' . esc_html($attribute_label) . ': ' . implode(', ', $attribute_values) . '</li>';
            }

            $output .= '
            <div class="product-image">' . $product_image . '</div>
            <div class="product-details">
                <h2>' . esc_html($product_title) . '</h2>
                <ul>' . $attributes_html . '</ul>
                <a href="' . esc_url($product_link) . '" class="buy-now-button">اطلاعات بیشتر</a>
            </div>';
        }
        $output .= '</div>';
        wp_reset_postdata();
        return $output;
    } else {
        return 'محصولی با این SKU یافت نشد.';
    }
}
add_shortcode('custom_product', 'custom_product_shortcode');