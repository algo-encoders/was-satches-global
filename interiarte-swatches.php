<?php

/*
Plugin Name:  Interiarte Swatches
Version: 1
Text Domain:  interiarte-swatches
*/


if (!defined('ABSPATH')) die;

global $insw_dir, $insw_url, $insw_default_image_settings;


$insw_dir = plugin_dir_path(__FILE__);
$insw_url = plugin_dir_url(__FILE__);

$insw_default_image_settings = array (
    'swatch_type' => 'image-swatch',
    'swatch_shape' => 'round',
    'swatch_size' =>
        array (
            'width' => '40',
            'height' => '30',
        ),
    'tooltips' => '1',
    'large_preview' => '0',
    'loop' => '0',
    'loop-method' => 'link',
    'handle-overflow' => 'stacked',
    'values' => []
);

include('inc/functions.php');
add_action('admin_enqueue_scripts', 'insw_register_scripts');
register_activation_hook( __FILE__, 'interiarte_swatches_activate' );
if ( is_admin() ) {
    add_action( 'admin_menu', 'insw_add_products_menu_entry', 10 );
}

function insw_iconic_was_get_sync_settings($full_slug, $_iconic_was){
    global $insw_default_image_settings;

    $db_settings = array_key_exists($full_slug, $_iconic_was) && is_array($_iconic_was[$full_slug]) ? $_iconic_was[$full_slug] : [];
    $args = wp_parse_args($db_settings, $insw_default_image_settings);

    return $args;

}

function insw_sync_attributes_images($product_id, $attr_slug = 'material'){
    $product = new WC_Product($product_id);
    $all_attr = $product->get_attributes();

    if(empty($all_attr) || !array_key_exists($attr_slug, $all_attr)) return;

    $is_variation = $all_attr[$attr_slug]->get_variation();

    if(!$is_variation) return;

    $_iconic_was = get_post_meta($product_id, '_iconic-was', true);
    $_iconic_was = !empty($_iconic_was) && is_array($_iconic_was) ? $_iconic_was : [];

    $prefix = 'attribute_';
    $full_slug = $prefix.$attr_slug;
    $ic_was = insw_iconic_was_get_sync_settings($full_slug, $_iconic_was);
    $ic_was_values = $ic_was["values"];
    $global_map_option = insw_get_slug_map_options($attr_slug);

    if(!empty($global_map_option)){
        foreach($global_map_option as $name => $attachment){
            $option_slug = sanitize_title($name);
            if(array_key_exists($option_slug, $ic_was_values)){
                $ic_was_values[$option_slug]['value'] = $attachment['attachment_id'];
            }else{
                $ic_was_values[$option_slug]['value'] = $attachment['attachment_id'];
                $ic_was_values[$option_slug]['label'] = $name;
            }
        }
    }


    $ic_was["values"] = $ic_was_values;
    $_iconic_was[$full_slug] = $ic_was;
    update_post_meta($product_id, '_iconic-was', $_iconic_was);

}

if(!function_exists('insw_sync_all_products_attr')){
    function insw_sync_all_products_attr($slug){
        $args = [
            'numberposts' => -1,
            'post_status' => 'publish',
            'post_type' => 'product',
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key'       => '_product_attributes',
                    'value'     => $slug,
                    'compare'   => 'LIKE',
                ]
            ]
        ];

        $all_products = get_posts($args);
        if(!empty($all_products)){
            foreach($all_products as $product){
                insw_sync_attributes_images($product, $slug);
            }
        }

    }
}

















