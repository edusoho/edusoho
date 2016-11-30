/*!
 * icon生成器: 其他区域， 不包含特殊字符
 */

( function () {

    'use strict';

    var config = window.iconConfig,
        canvas = document.createElement( 'canvas' ),
        ctx = null,
        maxHeight = 0,
        lastOffset = 0,
        padding = 5,
        position = {},
        root = '/kityformula-editor/';

    //------------------------ init
    ( function () {
        config = filteConfig( config );
        initCanvas();
    } )();

    //----------------------- start
    window.onload = function () {

        createSprites( config );

    };

    //------------------------- end

    function initCanvas () {

        canvas.width = 20000;
        canvas.height = 1000;

        ctx = canvas.getContext( '2d' );

        ctx.save();
        ctx.fillStyle = 'white';
        ctx.fillRect( 0, 0, canvas.width, canvas.height );
        ctx.restore();

    }

    function filteConfig ( config ) {

        var result = {};

        config = config.slice( 0, 2 ).concat( config.slice( 3 ) );

        kity.Utils.each( config, function ( conf ) {

            if ( conf.type !== 1 ) {
                return;
            }

            var group = conf.options.box.group;

            processGroup( group, result );

        } );

        return result;

    }

    /**
     * 输出最终结果
     */
    function outputResult () {

        var imageData = ctx.getImageData( 0, 0, lastOffset, maxHeight);

        canvas.width = lastOffset;
        canvas.height = maxHeight;

        ctx.putImageData( imageData, 0, 0 );

        var dataUrl = canvas.toDataURL();

        console.log( dataUrl )
        console.log( JSON.stringify( position, null, 4 ) )

    }

    function processGroup ( group, result ) {

        kity.Utils.each( group, function ( gData ) {

            processItems( gData.items, result );

        } );

    }

    function processItems ( items, result ) {

        kity.Utils.each( items, function ( item ) {

            processItem( item.content, result );

        } );

    }

    function processItem ( item, result ) {

        kity.Utils.each( item, function ( data ) {

            data = data.item;

            result[ data.val ] = data.show;

        } );

    }

    function createSprites ( conf ) {

        var count = 0;

        for ( var key in conf ) {

            if ( !conf.hasOwnProperty( key ) ) {
                continue;
            }

            count++;

            createImage( key, conf[ key ], function ( x ) {

                count--;

                if ( count === 0 ) {
                    outputResult();
                }

            } );

        }

    }

    function createImage ( key, imgSrc, callback ) {

        var image = new Image(),
            offset = 0;

        image.onload = function () {

            offset =lastOffset;

            if ( offset !== 0 ) {
                offset += padding;
            }

            ctx.drawImage( image, offset, 0 );

            lastOffset = offset + image.width;

            maxHeight = Math.max( maxHeight, image.height );

            position[ key ] = {
                pos: {
                    x: offset,
                    y: 0
                },
                size: {
                    width: image.width,
                    height: image.height
                }
            };

            image.onload = null;
            image = null;

            callback( offset );

        };

        image.src = imgSrc;

    }

} )();
