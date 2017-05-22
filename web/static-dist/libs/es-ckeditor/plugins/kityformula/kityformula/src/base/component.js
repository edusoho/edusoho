/*!
 * 组件抽象类，所有的组件都是该类的子类
 * @abstract
 */

define( function ( require ) {

    var kity = require( "kity" );

    return kity.createClass( 'Component', {

        constructor: function () {}

    } );

} );