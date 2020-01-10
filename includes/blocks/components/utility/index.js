/**
 * Tests whether or not supplied variable is numeric or not.
 *
 * @todo This should be moved to a helper library.
 * @link https://stackoverflow.com/a/15043984/5351316
 *
 * @param n
 *
 * @return {boolean}
 */
export function isNumber( n ) {
	return ( Object.prototype.toString.call( n ) === '[object Number]' || Object.prototype.toString.call( n ) === '[object String]' ) && !isNaN( parseFloat( n ) ) && isFinite( n.toString().replace( /^-/, '' ) );
}
