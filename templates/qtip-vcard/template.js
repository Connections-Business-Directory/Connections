jQuery(document).ready(function ($) {
	
	$('.cn-qtip-vcard').each(function(){
		$(this).qtip({
			content: {
				text: $(this).find('span.cn-qtip-content-vcard'), // Add .clone() if you don't want the matched elements to be removed, but simply copied
				title: {
					text: $(this).find('span.fn')
					/*button: true*/
				},
			},
			position: {
				my: 'bottom center',
				at: 'top center'
			},
			hide: false,
			hide: 'unfocus',
			style: {
				classes: 'ui-tooltip-shadow ui-tooltip-jtools'
			},
			show: {
		      solo: true
		   }
		});
	});
	
});