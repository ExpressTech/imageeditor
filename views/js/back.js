/**
* 2007-2015 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2016 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

var ie_launch_editor = null;
var ie_prev_selection = null;
var ie_id_product_active = null;
var ie_id_product_uploading = null;
var ie_img_ver_suffix = Math.random() * 10000;

jQuery(document).ready(function()  {
	

	var featherEditor = new Aviary.Feather({
        apiKey: 'e1490caea1f04b61ab9db7e61bef520e',
        theme : 'light',
        enableCORS : true,
        language : et_editor_lang,
        maxSize : 999999,
        onSave: function(imageID, newURL) {
            // var img = document.getElementById(imageID);
            // img.src = newURL;

            // featherEditor.close();

        },
        onSaveButtonClicked : function(imageID) {
        	var id_image = $('#' + imageID).attr('data-id-image');
        	var canvas = document.getElementById('avpw_canvas_element');
            var image = canvas.toDataURL();  
            var img_element = document.getElementById(imageID);
           
            //generate loading div gif

            var loading_gif = loadingGifOverlay(imageID);

            $.post('../modules/etimageeditor/api.php', { 'method' : 'saveImage', 'id_image' : id_image,  'image' : image, 'id_shop' : et_id_shop }, function (result) {
            	//console.log(result);
            	$.post('../modules/etimageeditor/api.php', { 'method' : 'regenerateThumbnails', 'id_image' : id_image , 'id_shop' : et_id_shop}, function(result){
            		// console.log('Thumbs done');
            		ie_img_ver_suffix += 1;
            		img_element.src = img_element.src + '&' + ie_img_ver_suffix;
            		$(loading_gif).remove();
            	})
            });

            featherEditor.close();
        	return false;
        }
    });

    ie_launch_editor = function(id, img_element) {
    	// console.log(img_element);
    	var src = $(img_element).attr('src');
        featherEditor.launch({
            image: id,
            url: src
        });
        return false;
    }

    
    function loadingGifOverlay(imageID) {
    	var img_element = document.getElementById(imageID);
    	var loading_gif = document.createElement('div');
        $(loading_gif).addClass('ie_loading_img');
        $(img_element).after(loading_gif);
        return loading_gif;
    }

    function setCover(imageID) {
    	var img_element = document.getElementById(imageID);
    	var cover_img = $('.ie_cover_img');//document.createElement('div');
    	if(!cover_img.length) {
    		//create cover img
    		var cover_img = document.createElement('div');
    		$(cover_img).addClass('ie_cover_img');
    		$(cover_img).html('<span class="icon-check"></span>');
    	}
        $(img_element).after(cover_img);
    }



    function clearLoadingGifOverlays() {
    	$('.ie_loading_img').remove();
    }

    function getGalleryImg(id_image, img_url, cover) {
    	var img_id = 'ie_img_' + id_image;
    	img_url +=  '?' + ie_img_ver_suffix; //+ new Date().getTime();
    	var ret  = '<div class="ie_gallery_img_wrapper col-xs-3"><div class="card card-1"><img onclick="ie_launch_editor(\'' + img_id + '\', this)" id="' + img_id + '" data-id-image ="' + id_image + '" class="ie_gallery_img " src="' + img_url + '">';

    	//add cover css
    	if(cover)
    		ret += '<div class="ie_cover_img"><span class="icon-check"></span></div>';

    	ret += '</div><span class="ie_gallery_img_tools"><span class="icon-trash text-danger ie_del_product_image card card-2 card-round" title="Delete Image"></span><span class="icon-check  text-success ie_cover_product_image card card-2 card-round" title="Set Cover"></span></span></div>';

    	return ret;
    }


    function getGalleryImgLoading() {
  
    	return '<div id="ie_gallery_img_loading" class="ie_gallery_img_wrapper  col-xs-3 loading"></div>';
    }

    function clearLoadingImg() {
    	$('#ie_gallery_img_loading').remove();
    }

    function addImageToProduct(id_product, id_image, img_url) {
    
    	ie_gallery_images[id_product][id_image] = img_url;
    }

    function initImageTools() {
    	
	    $('.ie_gallery_img_wrapper').height($('.ie_gallery_img_wrapper').width());

	    $(".ie_gallery_img_wrapper img").load(function()
		{
		    $(this).show();
		});

    }

    $(document).on("click", ".ie_cover_product_image", function(e) {

    	// var id_image = $($(e.currentTarget).parent().parent().find('img')[0]).attr('data-id-image');
    	// var imageID = $($(e.currentTarget).parent().parent().find('img')[0]).attr('id');

    	// var loading_gif = loadingGifOverlay(imageID);
    	// // console.log(imageID);return;
    	// $.post('../modules/etimageeditor/api.php', { 'method' : 'optimizeImage', 'id_image' : id_image }, function(result) {
    	// 	result = JSON.parse(result);
    	// 	if(result.error) {
    	// 		$.growl.error({ title: 'ERROR', message: result.error_long });
    	// 	} else {
    	// 		$.growl({ title: 'Success', message: result.percent + '% optimized' });
    	// 		$(loading_gif).remove();
    	// 	}
    	// })

    	var id_image = $($(e.currentTarget).parent().parent().find('img')[0]).attr('data-id-image');
    	var imageID = $($(e.currentTarget).parent().parent().find('img')[0]).attr('id');
    	var data = {
    		"action":"UpdateCover",
			"id_image":id_image,
			"id_product" : ie_id_product_active,
			"id_category" : 0,
			"token" : et_adminproducts_token,
			"tab" : "AdminProducts",
			"ajax" : 1 
    	};
    	var loading_gif = loadingGifOverlay(imageID);

    	$.post('', data, function(result) {
    		//console.log(result);
    		result = JSON.parse(result);
    		$(loading_gif).remove();
    		setCover(imageID);
    		if(result.status === "ok") {
    			$.growl({ title: '', message: result.confirmations.join()});
    		} else {
    			$.growl.error({ title: '', message: result.error.join()});
    		}
    	});

    })
    //set on delete. (replacement for live())
    $(document).on("click", ".ie_del_product_image", function(e) {

		if(confirm(et_lang["AREYOUSURE"])) {
			var id_image = $($(e.currentTarget).parent().parent().find('img')[0]).attr('data-id-image');
	    	var img_div = $(e.currentTarget).parent().parent();
	    	var imageID = $($(e.currentTarget).parent().parent().find('img')[0]).attr('id');
	    	var data = {
	    		"action":"deleteProductImage",
				"id_image":id_image,
				"id_product" : ie_id_product_active,
				"id_category" : 0,
				"token" : et_adminproducts_token,
				"tab" : "AdminProducts",
				"ajax" : 1 
	    	};

	    	var loading_gif = loadingGifOverlay(imageID);

	    	$.post('', data, function(result) {
	    		//console.log(result);
	    		result = JSON.parse(result);
	    		$(loading_gif).remove();

	    		if(result.status === "ok") {
	    			$.growl({ title: '', message: result.confirmations.join()});
	    			
	    			delete ie_gallery_images[ie_id_product_active][id_image];
	    			$(img_div).remove();

	    		} else {
	    			$.growl.error({ title: '', message: result.error.join()});
	    		}
	    	});
		}
    	
    })


    //open preview link

    $('.ie_product_link').click(function(e) {
    	var id_product = $(e.currentTarget).parent().parent().attr('id');
    	var win = window.open(decodeURIComponent(et_shop_url) + 'index.php?controller=product&id_product='+id_product, '_blank');
  		win.focus();
    })



    //open gallery on left pane item click
	$('.ie_item_wrapper').click(function(e) {
		var id_product = $(e.currentTarget).attr('id');
		var images = ie_gallery_images[id_product];
		
		try {
			
			var gallery = '';

			for (var i = 0; i < images.length; i++) {
				
				var img = images[i];
				// console.log(img);
				var id_image = img.id_image;//Object.keys(images)[i]; //id_image
				var img_url = img.path;//Object.keys(images)[i]; //id_image
				var img_cover = img.cover;//Object.keys(images)[i]; //id_image
				// console.log(img_url);
				var img_id = 'ie_img_' + id_image;
				gallery += getGalleryImg(id_image, img_url, img_cover); 
			}

			if(ie_prev_selection)
				$(ie_prev_selection).removeClass('active');
			$(e.currentTarget).addClass('active');
			ie_prev_selection = e.currentTarget;

			$('.ie_gallery_wrapper').html(gallery);
			//gallery image tools (del, set cover, move etc);
			initImageTools();

			ie_id_product_active = id_product;

		} catch(err) {
			console.log(err);
			$.growl.error({ title: '', message: 'Error while showing images for this product'});
		}
		
		
	});

	$(document).keyup(function(e) {
	     if (e.keyCode == 27) { 
	        featherEditor.close();
	    }
	});

	$('#ie_module_settings').click(function() {
		$('#ie_settings').toggle();
		$('.ie_gallery_wrapper').toggle();
		$(window).resize();
	})


	//file upload


	$('.ie_add_product_image').click(function(e) {
		ie_id_product_uploading = $(e.currentTarget).parent().parent().attr('id');
		var img_url =  decodeURIComponent(et_upload_image_url) + ie_id_product_uploading +  decodeURIComponent(et_upload_image_url_suffix);
		$('form[id=ie_image_upload_form]').attr('action', img_url);
		
		//open file dialog. 
		$('input[id=ie_image_file]').trigger('click');
		
		e.stopPropagation();
	})

	$('input[id=ie_image_file]').change(function() {
	    // console.log($(this).val());
	    $('form[id=ie_image_upload_form]').submit();
	});
	

	var bar = null;

	$('form[id=ie_image_upload_form]').on('submit', function(e) {
		//console.log(e);
		var form = e.target;
		var files = $(form).find('input')[0].files;
		// form. 
		e.preventDefault(); // prevent native submit
    	$(this).ajaxSubmit({
    		clearForm : true,
		    beforeSend: function(arr, $form, options) {
		    	//console.log(arr);

		  
		    	$(".ie_right_pane").animate({ scrollTop: $('.ie_right_pane').prop('scrollHeight') }, 200);
		    	var loadingImg = getGalleryImgLoading();
		    	// var loading_img = getGalleryImgLoading();
		    	$('.ie_gallery_wrapper').append(loadingImg);
		    	bar = new ProgressBar.Line('#ie_gallery_img_loading', {
		    		easing: 'easeInOut', 
		    		// color: '',
		    		trailColor: '#eee',
  					trailWidth: 1,
		    		strokeWidth: 4,
		    		from: {color: '#FFEA82'},
  					to: {color: '#ED6A5A'},
					// step: (state, bar) => {
					// 	bar.setText(Math.round(bar.value() * 100) + ' %');
					// }

		    	});
		    	

		    },
		    uploadProgress: function(event, position, total, percentComplete) {
		    	
		        bar.set(percentComplete/100);
		    },
		    error : function() {
		    	$.growl.error({ title: 'ERROR', message: 'No response from server'});
		        	clearLoadingImg();
		    },
		    success: function(result) {
		        
		        try {
		        	result = JSON.parse(result);
		        	if(result.file && result.file.length) {
			        	files = result.file;
			        	if(Array.isArray(files)) {
			        		//we got the correct response struct
			        		for (var i = 0; i < files.length; i++) {

			        			var file = files[i];
			        			
			        			if(file.error !== 0) {
			        				// alert();
			        				$.growl.error({ title: file.name, message: file.error});
			        			} else {

			        				$.growl({ title: file.name, message: "File uploaded successfully!"});

			        				//fetch the file
			        				// $.post('../modules/etimageeditor/api.php', { 'method' : 'getImagePath', 'id_image' : file.id}, function(img_url) {
			        					// if(img_url) {
	        						var img_url = decodeURIComponent(et_img_root) + file.path + '.jpg';
	        						//console.log(img_url);

	        						if(ie_id_product_uploading == ie_id_product_active) {
	        							if($('.ie_gallery_img_wrapper.loading').length) {
	        								var loading_img = $('.ie_gallery_img_wrapper.loading')[0];
	        								$(loading_img).replaceWith(getGalleryImg(file.id, img_url, 0));
		        						} else {
		        							$('.ie_gallery_wrapper').append(getGalleryImg(file.id, img_url, 0));
		        						}
	        						}
	        						
	        						// console.log(getGalleryImg(file.id, img_url));
	        						addImageToProduct(ie_id_product_uploading, file.id, img_url);
			        				initImageTools();

			        							
			        					// }
			        				// })
			        			}
			        		}
			        	}
			        } else {
			        	$.growl.error({ title: 'ERROR', message: 'No response from server'});
		        		clearLoadingImg();
			        }
		        } catch(err) {
		        	$.growl.error({ title: 'ERROR', message: 'Error while connecting to the server'});
		        	clearLoadingImg();
		        }
		        
		    },
			complete: function(xhr) {
				//console.log(xhr);
				// status.html(xhr.responseText);
				// console.log(xhr.responseText);
			}
		});

		// $(this).reset();
	}); 

	$(window).resize(function() {
		//console.log($('.ie_gallery_img_wrapper').width());
		$('.ie_gallery_img_wrapper').height($('.ie_gallery_img_wrapper').width());
		$('.ie_left_pane .list').height($('.ie_left_pane .list').parent().height() - 35);
	})

	//init list
	var options = {
	    valueNames: [ 'ie_item_txt', 'ie_item_ref' ]
	};

	var hackerList = new List('ie_left_pane', options);

	if($('.ie_item_wrapper')[0])
		$($('.ie_item_wrapper')[0]).trigger('click');

	$("img.ie_item_img").lazyload({
		effect : "fadeIn"
	});

	// init
	$('.ie_left_pane .list').height($('.ie_left_pane .list').parent().height() - 35);

	$('#ie_settings').html(decodeURIComponent($('#ie_settings').html()));

})

