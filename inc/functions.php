<?php

if(!defined('ABSPATH')) die;


if(!function_exists('pree')){
    function pree($d){
        echo "<pre>";
        print_r($d);
        echo "</pre>";
    }
}

function interiarte_swatches_activate() {
    // Create /instant-images directory inside /uploads to temporarily store images.
}


if(!function_exists('insw_register_scripts')){

    function insw_register_scripts() {

        global $insw_url;

        if(isset($_GET['page']) && $_GET['page'] === 'insw-swatch'){

            wp_register_style( 'insw_custom_style', "{$insw_url}/assets/css/style.css?time=".time(), false );
            wp_enqueue_style ( 'insw_custom_style');
            wp_enqueue_media ();
            wp_enqueue_script( 'insw_custom_script', "{$insw_url}/assets/js/script.js", array(), time() );

            $local_array = [
                'ajaxurl' => admin_url( 'admin-ajax.php'),
                'insw_nonce' => wp_create_nonce('insw_nonce_action'),
            ];

            wp_localize_script('insw_custom_script', 'insw_obj', $local_array);

        }



    }

}

function insw_add_products_menu_entry() {
    add_submenu_page(
        'edit.php?post_type=product',
        __( 'Swatches Image Mapping',  'interiarte-swatches'),
        __( 'Swatches Image Mapping', 'interiarte-swatches' ),
        'manage_woocommerce', // Required user capability
        'insw-swatch',
        'insw_swatches_map_page'
    );
}

function insw_swatches_map_page() {
    global $insw_dir;

    require_once ($insw_dir."/inc/admin-page.php");
}

if(!function_exists('insw_get_all_products_attributes')){
    function insw_get_all_products_attributes(){
        global $wpdb;
        $table_name = $wpdb->prefix."postmeta";
        $attribute_query = "SELECT * FROM $table_name WHERE meta_key='_product_attributes'";
        $all_attributes = $wpdb->get_results($attribute_query, ARRAY_A);
        $all_attributes_raw = [];

        if(!empty($all_attributes)){
            foreach ($all_attributes as $single_pa){
                $attribute_single = maybe_unserialize($single_pa['meta_value']);

                if(!empty($attribute_single)){

                    foreach ($attribute_single as $attr_slug => $attr){

                        $attr_val = $attr['value'];

                        if(!empty($attr_val)){

                            $attr_values = explode('|', $attr_val);
                            $attr_values = array_map('trim', $attr_values);
                            $all_attributes_raw[$attr_slug]['name'] = $attr['name'];
                            if(!is_array($all_attributes_raw[$attr_slug]['values'])){
                                $all_attributes_raw[$attr_slug]['values'] = [];
                            }

                            $all_attributes_raw[$attr_slug]['values'] = array_unique(array_merge($all_attributes_raw[$attr_slug]['values'], $attr_values));

                        }

                    }

                }


            }
        }

        return $all_attributes_raw;
    }
}

add_action( 'save_post', 'insw_update_attribute_cache', 10, 3);
function insw_update_attribute_cache($post_id, $post, $update){
    global $insw_dir;
    if ( $post->post_type != 'product') {
        return;
    }

    $all_attr_json = $insw_dir."/cache/all-attribute.json";
    $all_attributes_raw = insw_get_all_products_attributes();
    uasort($all_attributes_raw, function($a, $b){

        return $a['name'] > $b['name'];
    });
    $all_attributes_raw_json = json_encode($all_attributes_raw);
    file_put_contents($all_attr_json, $all_attributes_raw_json);



    insw_sync_attributes_images($post_id );
}

if(!function_exists('insw_get_single_attr_data')){
    function insw_get_single_attr_data($attr_slug){
        global $insw_dir;
//        $attr_slug = "material";
        $all_attr_json_path = $insw_dir."/cache/all-attribute.json";
        $all_attr_json = file_get_contents($all_attr_json_path);
        $all_attribute = json_decode($all_attr_json, true);
//    pree($all_attribute);

        $attribute_map_options = get_option('insw_attr_map_options', []);


        $slug_raw_attribute = array_key_exists($attr_slug, $all_attribute) ? $all_attribute[$attr_slug] : [];
        $slug_map_option = array_key_exists($attr_slug, $attribute_map_options) ? $attribute_map_options[$attr_slug] : [];


        $slug_map_option_new = [];
        if(!empty($slug_raw_attribute)){
            $slug_attribute_values = array_key_exists('values', $slug_raw_attribute) ? $slug_raw_attribute['values'] : [];

            if(!empty($slug_attribute_values)){
                foreach($slug_attribute_values as $single_attr){

                    $single_attr_new = [
                        'attachment_id' => "",
                        'attachment_url' => "",
                    ];

                    if(array_key_exists($single_attr, $slug_map_option)){
                        $single_attr_new['attachment_id'] = array_key_exists('attachment_id', $slug_map_option[$single_attr]) ? $slug_map_option[$single_attr]['attachment_id'] : '';
                        $single_attr_new['attachment_url'] = array_key_exists('attachment_url', $slug_map_option[$single_attr]) ? $slug_map_option[$single_attr]['attachment_url'] : '';
                    }

                    $slug_map_option_new[$single_attr] = $single_attr_new;
                }
            }

        }

        return $slug_map_option_new;

    }
}

if(!function_exists('insw_get_single_attr_html')){
    function insw_get_single_attr_html($attr_slug){

        global $insw_url;

        $single_attr_data = insw_get_single_attr_data($attr_slug);
        $placeholder_img = $insw_url."/assets/images/img-placeholder.png";
        ?>

        <section class="row my-3">
            <div class="col-md-12">
                <?php

                if(!empty($single_attr_data)){
                    foreach($single_attr_data as $attr => $data){

                        ?>

                        <div class="row my-4 insw-attr-row">
                            <div class="col-md-4 border rounded d-flex align-items-center bg-light">
                                <?= $attr; ?>
                            </div>
                            <div class="col-md-4 d-flex align-items-center img-container">
                                <span>
                                    <img class="img-thumbnail" data-attr="<?= $attr; ?>" data-id="<?= $data['attachment_id'] ?  $data['attachment_id'] : 0; ?>" src="<?= $data['attachment_url'] ?  $data['attachment_url'] : $placeholder_img; ?>" alt="">
                                    <span data-placeholder="<?= $placeholder_img?>" class="dashicons dashicons-dismiss <?php echo $data['attachment_url'] ?  'active' : $placeholder_img; ?> clear-image"></span>
                                </span>

                            </div>
                        </div>


                        <?php

                    }
                }

                ?>

            </div>

            <div class="ps-0 col-md-12 mt-3 d-flex alighn-items-center">
                <button class="btn btn-primary insw_attr_update" data-attr_slug="<?= $attr_slug; ?>"><?= __('Update', ''); ?></button>
                <div class="spinner-grow text-primary ms-3 insw-update-loading d-none" role="status">
                    <span class="sr-only d-none">Loading...</span>
                </div>
            </div>
        </section>

        <?php
    }
}

add_action('wp_ajax_insw_get_attribute_mapping_section', 'insw_get_attribute_mapping_section_callback');

if(!function_exists('insw_get_attribute_mapping_section_callback')){
    function insw_get_attribute_mapping_section_callback(){

        $result = [
            'status' => false,
            'slug_json' => [],
            'section_html' => ''
        ];

        if(!empty($_POST) && isset($_POST['attr_slug']) && $_POST['attr_slug']){
            if(!isset($_POST['insw_nonce']) || !wp_verify_nonce($_POST['insw_nonce'], 'insw_nonce_action')){
                wp_die(__('Sorry, your nonce did not verify.', 'interiarte-swatches'));
            }else{
                $attr_slug = sanitize_text_field($_POST['attr_slug']);
                $attr_json = json_encode(insw_get_single_attr_data($attr_slug));
                ob_start();
                insw_get_single_attr_html($attr_slug);
                $attr_html = ob_get_clean();

                $result = [
                    'status' => true,
                    'slug_json' => $attr_json,
                    'section_html' => $attr_html
                ];
            }
        }

        wp_send_json($result);
    }
}


add_action('wp_ajax_insw_update_single_attr', 'insw_update_single_attr_callback');

if(!function_exists('insw_update_single_attr_callback')){
    function insw_update_single_attr_callback(){

        $result = [
            'status' => false
        ];

        if(!empty($_POST) && isset($_POST['attr_slug']) && isset($_POST['insw_data'])){
            if(!isset($_POST['insw_nonce']) || !wp_verify_nonce($_POST['insw_nonce'], 'insw_nonce_action')){
                wp_die(__('Sorry, your nonce did not verify.', 'interiarte-swatches'));
            }else{
                $attr_slug = sanitize_text_field($_POST['attr_slug']);
                $insw_data = $_POST['insw_data'];
                $attribute_map_options = get_option('insw_attr_map_options', []);
                $attribute_map_options[$attr_slug] = $insw_data;
                $result['status'] = update_option('insw_attr_map_options', $attribute_map_options);
                insw_sync_all_products_attr($attr_slug);
            }
        }

        wp_send_json($result);
    }
}

if(!function_exists('insw_get_slug_map_options')){
    function insw_get_slug_map_options($attr_slug){
        $attribute_map_options = get_option('insw_attr_map_options', []);
        return array_key_exists($attr_slug, $attribute_map_options) && is_array($attribute_map_options[$attr_slug]) ? $attribute_map_options[$attr_slug] : [];

    }
}









