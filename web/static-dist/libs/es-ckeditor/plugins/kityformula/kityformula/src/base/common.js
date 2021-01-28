/**
 * Created by hn on 14-3-17.
 */

define( function ( require ) {

    // copy保护
    var MAX_COPY_DEEP = 10,

        commonUtils = {
            extend: function ( target, source ) {

                var isDeep = false;

                if ( typeof target === "boolean" ) {
                    isDeep = target;
                    target = source;
                    source = [].splice.call( arguments, 2 );
                } else {
                    source = [].splice.call( arguments, 1 );
                }

                if ( !target ) {
                    throw new Error( 'Utils: extend, target can not be empty' );
                }

                commonUtils.each( source, function ( src ) {

                    if ( src && typeof src === "object" || typeof src === "function" ) {

                        copy( isDeep, target, src );

                    }

                } );

                return target;

            },

            /**
             * 返回给定节点parent是否包含target节点
             * @param parent
             * @param target
             */
            contains: function ( parent, target ) {

                if ( parent.contains ) {
                    return parent.contains( target );
                } else if ( parent.compareDocumentPosition ) {
                    return !!( parent.compareDocumentPosition( target ) & 16 );
                }

            },

            getRect: function ( node ) {
                return node.getBoundingClientRect();
            },

            isArray: function ( obj ) {
                return obj && ({}).toString.call( obj ) === "[object Array]";
            },

            isString: function ( obj ) {
                return typeof obj === "string";
            },

            proxy: function ( fn, context ) {

                return function () {
                    return fn.apply( context, arguments );
                };

            },

            each: function ( obj, fn ) {

                if ( !obj ) {
                    return;
                }

                if ( 'length' in obj && typeof obj.length === "number" ) {

                    for ( var i = 0, len = obj.length; i < len; i++ ) {

                        if ( fn.call( null, obj[ i ], i, obj ) === false ) {
                            break;
                        }

                    }

                } else {

                    for ( var key in obj ) {

                        if ( obj.hasOwnProperty( key ) ) {
                            if ( fn.call( null, obj[ key ], key, obj ) === false ) {
                                break;
                            }
                        }

                    }

                }

            }
        };

    function copy ( isDeep, target, source, count ) {

        count = count | 0;

        if ( count > MAX_COPY_DEEP ) {
            return source;
        }

        count++;

        commonUtils.each( source, function ( value, index, origin ) {

            if ( isDeep ) {

                if ( !value || ( typeof value !== "object" && typeof value !== "function" ) ) {
                    target[ index ] = value;
                } else {
                    target[ index ] = target[ index ] || ( commonUtils.isArray( value ) ? [] : {} );
                    target[ index ] = copy( isDeep, target[ index ], value, count );
                }

            } else {
                target[ index ] = value;
            }

        } );

        return target;

    }

    return commonUtils;

} );