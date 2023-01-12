var insw_current_attr = {};

jQuery(document).ready(function($){
    let attr_load = $('.attr-load');
    let attr_list = $('#attr-list');
    let loading_section = $('.loading');

    attr_list.on('change', function(){

        let this_val = $(this).val();
        // console.log(this_val);
        if(this_val.length > 0){
            attr_load.prop('disabled', false);
        }else{
            attr_load.prop('disabled', true);
        }
    });

    attr_load.on('click', function(){
        let attr_map_container = $('.attribute-map-container');
        attr_map_container.addClass('d-none');
        loading_section.removeClass('d-none');
        let attr_list_val = attr_list.val();
        // console.log(attr_list_val);

        let data = {
            action: 'insw_get_attribute_mapping_section',
            attr_slug: attr_list_val,
            insw_nonce: insw_obj.insw_nonce
        }

        $.post(ajaxurl, data, function(resp, code){

            loading_section.addClass('d-none');
            if(code == 'success' && resp.status){
                attr_map_container.removeClass('d-none');
                attr_map_container.find('.attribute-content').html(resp.section_html);
                insw_current_attr = JSON.parse(resp.slug_json);
            }
        });
    });

    $(document).on('click', '.insw_attr_update', function(){

        let this_btn = $(this);
        let this_slug = $(this).data('attr_slug');


        if(this_slug.length > 0){
            this_btn.next('div').removeClass('d-none');
            this_btn.prop('disabled', true);
            let data = {
                action: 'insw_update_single_attr',
                attr_slug: $(this).data('attr_slug'),
                insw_data: insw_current_attr,
                insw_nonce: insw_obj.insw_nonce
            };

            $.post(ajaxurl, data, function(resp, code){
                this_btn.prop('disabled', false);
                this_btn.next('div').addClass('d-none');
                if(code == 'success'){

                    // console.log('success');
                }

            });

        }


    });


    // Set all variables to be used in scope
    var frame, this_img_clicked;
    // ADD IMAGE LINK


    $(document).on( 'click', '.insw-attr-row .img-thumbnail', function( event ){

        event.preventDefault();

        this_img_clicked = $(this);

        // Create a new media frame
        frame = wp.media({
            title: 'Select Attribute Image',
            button: {
                text: 'Use this media'
            },
            multiple: false  // Set to true to allow multiple files to be selected
        });


        // When an image is selected in the media frame...
        frame.on( 'select', function() {
            // Get media attachment details from the frame state
            var attachment = frame.state().get('selection').first().toJSON();
            this_img_clicked.prop('src', attachment.url);
            this_img_clicked.data('id', attachment.id);
            let close_btn = this_img_clicked.next();
            close_btn.removeClass('active').addClass('active');
            let this_img_attr = this_img_clicked.data('attr');
            insw_current_attr[this_img_attr].attachment_id = attachment.id;
            insw_current_attr[this_img_attr].attachment_url = attachment.url;
        });

        frame.on('open',function() {
            var selection = frame.state().get('selection');
            var this_img_id = this_img_clicked.data('id');

            if(this_img_id > 0) {
                let attachment = wp.media.attachment(this_img_id);
                attachment.fetch();
                selection.add(attachment ? [attachment] : []);
            }
        });

        // Finally, open the modal on click
        frame.open();
    });

    let close_btn_selector = '.insw-attr-row .active.dashicons-dismiss';
    $(document).on('click', close_btn_selector, function(){
        let this_img = jQuery(this).prev('img');
        this_img.prop('src', jQuery(this).data('placeholder'));
        this_img.data('id', '0');
        let this_img_attr = this_img.data('attr');
        insw_current_attr[this_img_attr].attachment_id = "";
        insw_current_attr[this_img_attr].attachment_url = "";
        $(this).removeClass('active');
        this_img_clicked = this_img;
    });

    attr_list.change();
    attr_load.click();
});