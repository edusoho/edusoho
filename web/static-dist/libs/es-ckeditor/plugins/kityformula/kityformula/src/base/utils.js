/*!
 * 基础工具包
 */

define( function ( require ) {

    var Utils = {},
        commonUtils = require( "base/common" );

    commonUtils.extend( Utils, commonUtils, require( "base/event/event" ) );

    return Utils;

} );
