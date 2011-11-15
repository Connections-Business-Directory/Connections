/**
 * @author Steven A. Zahm
 */

// Use jQuery() instead of $()for WordPress compatibility with the included prototype js library which uses $()
// http://ipaulpro.com/blog/tutorials/2008/08/jquery-and-wordpress-getting-started/
// See http://chrismeller.com/using-jquery-in-wordpress
jQuery(document).ready(function($){
	
	jQuery('.connections').preloader({
		delay:200,
		imgSelector:'.cn-image img.photo, .cn-image img.logo',
		beforeShow:function(){
			jQuery(this).closest('.cn-image img').css('visibility','hidden');
		},
		afterShow:function(){
			//var image = $(this).closest('.cn-image');
			//jQuery(image).spin(false);
		}
	});
	
	jQuery(function()
	{
		jQuery('a.detailsbutton')
			.css("cursor","pointer")
			.attr("title","Click to show details.")
			.click(function()
			{
				jQuery('.child-'+this.id).each(function(i, elem)
				{
					jQuery(elem).toggle(jQuery(elem).css('display') == 'none');
				});

				return false;		
			})
			.toggle
			(
				function() 
				{
					jQuery(this).html('Hide Details');
					jQuery(this).attr("title","Click to hide details.")
				},
				
				function() 
				{
					jQuery(this).html('Show Details');
					jQuery(this).attr("title","Click to show details.")
				}
			);
		//jQuery('tr[@class^=child-]').hide().children('td');
		return false;
	});
	
	
	jQuery(function() {
		jQuery('input#entry_type_0')
			.click(function(){
				jQuery('#family').slideUp();
				jQuery('.namefield').slideDown();
				jQuery('#contact_name').slideUp();
				jQuery('.celebrate').slideDown();
			});
	});
	
	jQuery(function() {
		jQuery('input#entry_type_1')
			.click(function(){
				jQuery('#family').slideUp();
				jQuery('.namefield').slideUp();
				jQuery('#contact_name').slideDown();
				jQuery('.celebrate').slideUp();
			});
	});
	
	jQuery(function() {
		jQuery('input#entry_type_2')
			.click(function(){
				jQuery('#family').slideDown();
				jQuery('.namefield').slideUp();
				jQuery('.celebrate').slideUp();
			});
	});
	
	
	jQuery(function() {
		var $entryType = (jQuery('input[name^=entry_type]:checked').val());
		
		switch ($entryType)
		{
			case 'individual':
				jQuery('#family').slideUp();
				jQuery('#contact_name').slideUp();
				break;
			
			case 'organization':
				jQuery('#family').slideUp();
				jQuery('.namefield').slideUp();
				jQuery('.celebrate').slideUp();
				break;
			
			case 'family':
				jQuery('.namefield').slideUp();
				jQuery('.celebrate').slideUp();
				break;
		}
	
	});
	
	jQuery(function() {
		//var intCount = 0;
		//var jRelations = (jQuery('#relation_row_base').html());
		
		jQuery('#add_relation')
			.click(function() {
				var jRelations = (jQuery('#relation_row_base').text());
				var d = new Date();
				var id = Math.floor( Math.random() * d.getTime() );
				
				jRelations = jRelations.replace(
					new RegExp('::FIELD::', 'gi'),
					id
					);
				
				jQuery('#relations').append( '<div id="relation_row_' + id + '" class="relation_row">' + jRelations + '<a href="#" id="remove_button_' + intCount + '" ' + 'class="button button-warning" onClick="removeEntryRow(\'#relation_row_' + intCount + '\'); return false;">Remove</a>' + '</div>' );
				
				//intCount++;
			});
	});
	
	jQuery(function() {
		//var intCount = 0;
		//var jRelations = (jQuery('#social_media_row_base').html());
		
		jQuery('#add_social_media')
			.click(function() {
				var jRelations = (jQuery('#social_media_row_base').text());
				var d = new Date();
				var id = Math.floor( Math.random() * d.getTime() );
		
				jRelations = jRelations.replace(
					new RegExp('::FIELD::', 'gi'),
					id
					);
				
				//jQuery('#social_media').append( '<div id="social-row_' + intCount + '" class="social_media_row">' + jRelations + '<a href="#" id="remove_button_' + intCount + '" ' + 'class="button button-warning" onClick="removeEntryRow(\'#social_media_row_' + intCount + '\'); return false;">Remove</a>' + '</div>' );
				jQuery('#social-media').append( '<div class="widget social" id="social-row-' + id + '">' + jRelations + '</div>' );
				
				//intCount++;
			});
	});
	
	jQuery(function() {
		//var intCount = 0;
		//var jRelations = (jQuery('#address_row_base').html());
		
		jQuery('#add_address')
			.click(function() {
				var jRelations = (jQuery('#address_row_base').text());
				var d = new Date();
				var id = Math.floor( Math.random() * d.getTime() );
				
				jRelations = jRelations.replace(
					new RegExp('::FIELD::', 'gi'),
					id
					);
				
				//jQuery('#addresses').append( '<div id="address_row_' + intCount + '" class="address_row">' + jRelations + '<br /><a href="#" id="remove_button_' + intCount + '" ' + 'class="button button-warning" onClick="removeEntryRow(\'#address_row_' + intCount + '\'); return false;">Remove</a>' + '</div>' );
				jQuery('#addresses').append( '<div class="widget address" id="address_row_' + id + '">' + jRelations + '</div>' );
				
				//intCount++;
			});
	});
	
	jQuery(function() {
		//var intCount = 0;
		//var jRelations = (jQuery('#phone_number_row_base').html());
		
		jQuery('#add_phone_number')
			.click(function() {
				var jRelations = (jQuery('#phone_number_row_base').text());
				var d = new Date();
				var id = Math.floor( Math.random() * d.getTime() );
				
				jRelations = jRelations.replace(
					new RegExp('::FIELD::', 'gi'),
					id
					);
				
				//jQuery('#phone-numbers').append( '<div id="phone-row-' + intCount + '" class="phone-row">' + jRelations + '<a href="#" id="remove_button_' + intCount + '" ' + 'class="button button-warning" onClick="removeEntryRow(\'#phone_number_row_' + intCount + '\'); return false;">Remove</a>' + '</div>' );
				jQuery('#phone-numbers').append( '<div class="widget phone" id="phone-row-' + id + '">' + jRelations  + '</div>' );
				
				//intCount++;
			});
	});
	
	jQuery(function() {
		//var intCount = 0;
		//var jRelations = (jQuery('#email_address_row_base').html());
		
		jQuery('#add_email_address')
			.click(function() {
				var jRelations = (jQuery('#email_address_row_base').text());
				var d = new Date();
				var id = Math.floor( Math.random() * d.getTime() );
				
				jRelations = jRelations.replace(
					new RegExp('::FIELD::', 'gi'),
					id
					);
				
				//jQuery('#email_addresses').append( '<div id="email_address_row_' + intCount + '" class="email_address_row">' + jRelations + '<a href="#" id="remove_button_' + intCount + '" ' + 'class="button button-warning" onClick="removeEntryRow(\'#email_address_row_' + intCount + '\'); return false;">Remove</a>' + '</div>' );
				jQuery('#email-addresses').append( '<div class="widget email" id="email-row-' + id + '">' + jRelations + '</div>' );
				
				//intCount++;
			});
	});
	
	jQuery(function() {
		//var intCount = 0;
		//var jRelations = (jQuery('#website_address_row_base').html());
		
		jQuery('#add_link')
			.click(function() {
				var jRelations = (jQuery('#link_row_base').text());
				var d = new Date();
				var id = Math.floor( Math.random() * d.getTime() );
				
				jRelations = jRelations.replace(
					new RegExp('::FIELD::', 'gi'),
					id
					);
				
				//jQuery('#website_addresses').append( '<div id="website_address_row_' + intCount + '" class="website_address_row">' + jRelations + '<a href="#" id="remove_button_' + intCount + '" ' + 'class="button button-warning" onClick="removeEntryRow(\'#website_address_row_' + intCount + '\'); return false;">Remove</a>' + '</div>' );
				jQuery('#links').append( '<div class="widget link" id="link-row-' + id + '">' + jRelations + '</div>' );
				
				//intCount++;
			});
	});
	
	jQuery(function() {
		//var intCount = 0;
		//var jRelations = (jQuery('#im_row_base').html());
		
		jQuery('#add_im_id')
			.click(function() {
				var jRelations = (jQuery('#im_row_base').text());
				var d = new Date();
				var id = Math.floor( Math.random() * d.getTime() );
				
				jRelations = jRelations.replace(
					new RegExp('::FIELD::', 'gi'),
					id
					);
				
				//jQuery('#im_ids').append( '<div id="im_row_' + intCount + '" class="im_row">' + jRelations + '<a href="#" id="remove_button_' + intCount + '" ' + 'class="button button-warning" onClick="removeEntryRow(\'#im_row_' + intCount + '\'); return false;">Remove</a>' + '</div>' );
				jQuery('#im-ids').append( '<div class="widget im" id="im-row-' + id + '">' + jRelations + '</div>' );
				
				//intCount++;
			});
	});
	
	/*
	 * Switching Visual/HTML Modes With TinyMCE
	 * http://www.keighl.com/2010/04/switching-visualhtml-modes-with-tinymce/
	 */
	
	jQuery('a#toggleBioEditor').click(
		function() {
			id = 'bio';
			if (tinyMCE.get(id))
			{
				tinyMCE.execCommand('mceRemoveControl', false, id);
			}
			else
			{
				tinyMCE.execCommand('mceAddControl', false, id);
			}
		}
	);

	jQuery('a#toggleNoteEditor').click(
		function() {
			id = 'note';
			if (tinyMCE.get(id))
			{
				tinyMCE.execCommand('mceRemoveControl', false, id);
			}
			else
			{
				tinyMCE.execCommand('mceAddControl', false, id);
			}
		}
	);
	

});

function removeEntryRow(id)
	{
		jQuery(id).remove();
		//jQuery(id).slideUp('slow', function() {jQuery(id).remove});
	}