function tinymce_setup(){
        $(function(){
                $('textarea.tinymce').tinymce({
                        script_url : baseurl+"tiny_mce/4.0.2/tiny_mce.js",

                        // General options
                        schema: "html5-strict",
                        theme : "modern",
                        plugins : "layer,textcolor,image,searchreplace,print,contextmenu,paste,directionality,fullscreen,visualchars,nonbreaking,wordcount,advlist,save",

                        // Buttons and toolbar
                        menu: {
                                //no menu
                        },
                        toolbar: "save,|,bold,italic,underline,strikethrough,|,alignleft,aligncenter,alignright,alignjustify,formatselect,fontsizeselect,removeformat,|,cut,copy,paste,|,search,replace,|,image,advhr,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,forecolor,backcolor,|,deletedia",
                        fontsize_formats: "40px 45px 50px 60px 70px 80px 90px 100px",
                        statusbar: "bottom",

                        //Font tweaking
                        font_formats: 'Helvetica=helvetica,arial,sans-serif;',
                        style_formats : [
                                {title : 'Paragraph', inline : 'p'},
                                {title : 'Red text', inline : 'span', styles : {color : '#ff0000'}},
                                {title : 'Red header', block : 'h1', styles : {color : '#ff0000'}},
                                {title : 'Example 1', inline : 'span', classes : 'example1'},
                                {title : 'Example 2', inline : 'span', classes : 'example2'},
                                {title : 'Table styles'},
                                {title : 'Table row 1', selector : 'tr', classes : 'tablerow1'}
                        ],

                        // Little tweaking.
                        theme_advanced_resizing : false,
                        force_br_newlines : true,
                        force_p_newlines : false,
                        forced_root_block : '',
                        save_enablewhendirty: false,
                        save_onsavecallback : "tinymce_tallenna",
                        imagemanager_contextmenu: true,
                        theme_advanced_resizing_use_cookie : false,

                        // Editor size
                        height: "470",
                        width: "940",

                        // Preview
                        plugin_preview_width : "1020",
                        plugin_preview_height : "760",
                        plugin_preview_pageurl : baseurl+"tiny_mce/preview.html",

                        // Content CSS
                        content_css : baseurl+"css/tiny_mce.css",
                        body_id : "text",
                        //body_class : "main",

                        setup : function(editor) {//on-demand hack cancel-napin tooltipin vaihtoon.
                                editor.addButton('deletedia', {
                                        text: 'Delete slide',
                                        icon: false,
                                        onclick: function(){
                                                tinymce_poista();
                                        }
                                });
                        }
                });
                setted_up = true;
        });
}