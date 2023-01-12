<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>

<?php

global $wpdb, $insw_dir, $insw_url;

    $all_attr_json_path = $insw_dir."/cache/all-attribute.json";
    $all_attr_json = file_get_contents($all_attr_json_path);
    $all_attribute = json_decode($all_attr_json, true);
    $default_slug = 'material';

//    pree(insw_get_single_attr_data($default_slug));
//
//    exit;
    ?>



    <div class="container">
        <section class="row my-3">
            <div class="col-md-12">
                <div class="h3"><?php _e('Attribute Swatches Image Mapping', '');?></div>
                <hr>
            </div>
        </section>

        <section class="row my-3">
            <div class="col-md-4 col-8">
                <div class="form-group">
                    <label for="attr-list"><?php _e('Select attribute for image mapping', '');?></label>
                    <select name="" id="attr-list" class="form-control" readonly>
                        <option value=""><?php _e('Select Attribute', '');?></option>
                        <?php

                            if(!empty($all_attribute)){
                                foreach($all_attribute as $attr_slug => $attr){
                                    if($attr_slug != $default_slug) continue;
                                    $selected = $default_slug == $attr_slug ? 'selected' : '';
                                    echo "<option {$selected} value='$attr_slug'>{$attr['name']}</option>";
                                }
                            }

                        ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4 col-4 d-flex align-items-end">
                <button class="btn btn-primary attr-load" disabled><?php _e('Load', '');?> </button>
            </div>
        </section>

        <section class="row mt-5 loading d-none">
            <div class="col-md-4 text-center">
                <div class="spinner-grow text-primary" role="status">
                    <span class="sr-only d-none">Loading...</span>
                </div>
            </div>
        </section>



        <section class="row mt-5 attribute-map-container d-none">

            <div class="col-md-12">
                <div class="h4">
                    <span class="dynamic-name"></span> <?php _e('Attribute Mapping', '');?>
                </div>
            </div>

            <div class="col-md-12 attribute-content">

            </div>

        </section>

    </div>




