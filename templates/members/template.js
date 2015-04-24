/**
 * @author:  Phill Pafford
 * @website: http://llihp.blogspot.com
 * @notes:
 *      This extends the functionality of the connections WordPress plugin
 *      to display family contact information in a directory format.
 *      When clicked a popup will display the family contact information
 */
this.contactPreview = function() {
	jQuery( 'a.contact' ).each( function() {
		var $this = jQuery( this );
		$this.data( 'title', $this.attr( 'title' ) );
		$this.attr( 'title', '' );
		$this.css( "cursor", "pointer" );
	} );
	//alert( jQuery().jquery);
	jQuery( "a.contact" ).live( 'click', function( e ) {
		//store clicked link
		var $this = jQuery( this );
		//store title
		var title = $this.data( 'title' );

		jQuery( "body" ).append( "<div id='contact-info'>" + title + "<div id='close-contact-footer'><a id='close-contact' class='close-contact'>Close</a></div></div>" );
		jQuery( "a.close-contact" ).css( "cursor", "pointer" );

		jQuery( "#contact-info" ).fadeIn( 500 ).center();
	} );

	jQuery( "#close-contact" ).live( 'click', function() {
		jQuery( '#contact-info' ).remove();
	} );

};

jQuery.fn.center = function() {
	this.css( "position", "absolute" );
	this.css( "top", (jQuery( window ).height() - this.height()) / 2 + jQuery( window ).scrollTop() + "px" );
	this.css( "left", (jQuery( window ).width() - this.width()) / 2 + jQuery( window ).scrollLeft() + "px" );
	return this;
};

// Use jQuery() instead of $()for WordPress compatibility with the included prototype js library which uses $()
// http://ipaulpro.com/blog/tutorials/2008/08/jquery-and-wordpress-getting-started/
// See http://chrismeller.com/using-jquery-in-wordpress
jQuery( document ).ready( function() {

	// Call the function here
	contactPreview();
	//window.onload = function(){setTimeout("contactPreview()",0)}

} );
