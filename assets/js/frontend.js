jQuery(document).ready(function($) {
	$('#cn-list-body').cn_preloader({
		delay:10,
		imgSelector:'.cn-image-loading img.photo, .cn-image-loading img.logo, .cn-image-loading img.map, .cn-image-loading img.screenshot',
		beforeShow:function(){
			$(this).closest('.cn-image-loading img').css('visibility','hidden');
		},
		afterShow:function(){
			//var image = $(this).closest('.cn-image');
			//jQuery(image).spin(false);
		}
	});

});
