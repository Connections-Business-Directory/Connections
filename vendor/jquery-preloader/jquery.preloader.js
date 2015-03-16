/* 
 * jQuery Plugin :: preloader
 * @author (?) http://themeforest.net/user/Kaptinlin
 */
(function($) {

	$.fn.cn_preloader = function(options) {
		var settings = $.extend({}, $.fn.cn_preloader.defaults, options);


		return this.each(function() {
			settings.beforeShowAll.call(this);
			var imageHolder = $(this);
			
			var images = imageHolder.find(settings.imgSelector).css({opacity:0, visibility:'hidden'});	
			var count = images.length;
			var showImage = function(image,imageHolder){
				if(image.data.source != undefined){
					imageHolder = image.data.holder;
					image = image.data.source;	
				};
				
				count --;
				if(settings.delay <= 0){
					image.css('visibility','visible').animate({opacity:1}, settings.animSpeed, function(){settings.afterShow.call(this)});
				}
				if(count == 0){
					imageHolder.removeData('count');
					if(settings.delay <= 0){
						settings.afterShowAll.call(this);
					}else{
						if(settings.gradualDelay){
							images.each(function(i,e){
								var image = $(this);
								setTimeout(function(){
									image.css('visibility','visible').animate({opacity:1}, settings.animSpeed, function(){settings.afterShow.call(this)});
								},settings.delay*(i+1));
							});
							setTimeout(function(){settings.afterShowAll.call(imageHolder[0])}, settings.delay*images.length+settings.animSpeed);
						}else{
							setTimeout(function(){
								images.each(function(i,e){
									$(this).css('visibility','visible').animate({opacity:1}, settings.animSpeed, function(){settings.afterShow.call(this)});
								});
								setTimeout(function(){settings.afterShowAll.call(imageHolder[0])}, settings.animSpeed);
							}, settings.delay);
						}
					}
				}
			};
			
			if(count==0){
				settings.afterShowAll.call(this);
			}else{
				images.each(function(i){
					settings.beforeShow.call(this);
				
					image = $(this);
				
					if(this.complete==true){
						showImage(image,imageHolder);
					}else{
						image.bind('error load',{source:image,holder:imageHolder}, showImage);
						if($.browser.opera || ($.browser.msie && parseInt(jQuery.browser.version, 10) == 9 && document.documentMode == 9) 
							|| ($.browser.msie && parseInt(jQuery.browser.version, 10) == 8 && document.documentMode == 8) 
							|| ($.browser.msie && parseInt(jQuery.browser.version, 10) == 7 && document.documentMode == 7)){
							image.trigger("load");//for hidden image
						}
					}
				});
			}
		});
	};


	//Default settings
	$.fn.cn_preloader.defaults = {
		delay:1000,
		gradualDelay:true,
		imgSelector:'img',
		animSpeed:500,
		beforeShowAll: function(){},
		beforeShow: function(){},
		afterShow: function(){},
		afterShowAll: function(){}
	};
})(jQuery);