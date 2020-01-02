export function decodeROT13( str ) {

	return str.replace( /[a-zA-Z]/g, function( character ) {

		return String.fromCharCode( ( character <= 'Z' ? 90 : 122 ) >= ( character = character.charCodeAt( 0 ) + 13 ) ? character : character - 26 );
	} );
}
