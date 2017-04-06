/*!
 * icon生成器: 特殊字符区域
 */

( function () {

    'use strict';

    var config = window.iconConfig,
        root = 'http://localhost/kityformula-editor/',
        result = {},
        padding = 5,
        canvas = null,
        row = 0,
        COUNT = 0,
        // 单行最大icon数
        iconCountInLine = 30,
        positionData = null,
        spritesData = {};

    // 特殊字符icon配置区
    config = config[2].options.box.group;

    config.forEach( function ( conf, index ) {

        result[ conf.title ] = {};
        process( conf.items, result[ conf.title ] );

    } );

    window.onload = function () {

        var ctx = null;
        canvas = document.createElement( 'canvas' );

        canvas.width = 10000;
        canvas.height = 500;
        ctx = canvas.getContext( '2d' );
        ctx.save();
        ctx.fillStyle = 'white';
        ctx.fillRect( 0, 0, canvas.width, canvas.height );
        ctx.restore();
        positionData = createSprites();
    };

    function outImageDataUrl () {

        var newCanvas = document.createElement( 'canvas' ),
            dataUrl = null,
            ctx = newCanvas.getContext( '2d' ),
            col = COUNT > iconCountInLine ? iconCountInLine : COUNT,
            row = Math.ceil( COUNT / iconCountInLine ),
            width = col * ( 32 + padding ) + padding,
            height = row * ( 32 + padding );

        newCanvas.width = width;
        newCanvas.height = height;

        var imgData = canvas.getContext( '2d' ).getImageData( 0, 0, width, height );

        ctx.putImageData( imgData, 0, 0 );
        dataUrl = newCanvas.toDataURL( 'iamge/jpeg' );

        console.log(dataUrl)
//        console.log( JSON.stringify( positionData, null, 4 ) )

    }

    function process ( data, storage ) {

        data.forEach( function ( d ) {

            var currentData = null;

            storage[ d.title ] = {};
            currentData = d.content;

            processRow( currentData, storage[ d.title ] );

        } );

    }

    // 雪碧图中的一行
    function processRow ( data, storage ) {

        data.forEach( function ( currentData ) {

            currentData = currentData.item;
            storage[ currentData.val ] = root + currentData.show;

        } );

    }

    function createSprites () {

        var tmpResult = {},
            count = 0,
            tmp = {};

        // ---------------- 把原始config里的结构化数据打散成为单层结构的数据
        for ( var key in result ) {

            if ( result.hasOwnProperty( key ) ) {
                for ( var jk in result[ key ] ) {
                    if ( result[ key ].hasOwnProperty( jk ) ) {
                        for ( var ck in result[ key ][ jk ] ) {
                            if ( result[ key ][ jk ].hasOwnProperty( ck ) ) {
                                tmp[ ck ] = result[ key ][ jk ][ck];
                            }
                        }
                    }
                }
            }

        }
        //--------------- 结构处理完毕

        result = tmp;
        tmp = null;

        for ( var key in result ) {
            count++;
            COUNT++;
            tmpResult[ key ] = createImage( result[ key ], count-1, function () {
                count--;
                if ( count === 0 ) {
                    outImageDataUrl();
                }
            } );
        }

        return tmpResult;

    }

    function createImage ( src, index, callback ) {

        var col = index % iconCountInLine,
            row = Math.floor( index / iconCountInLine ),
            result = {
                x: col * ( 32 + padding ) + padding,
                y: row * ( 32 + padding )
            };

        readImageData( src, canvas, col, row, function () {
            callback();
        } );

        return result;

    }

    function readImageData ( src, canvas, col, row, callback ) {

        var img = new Image(),
            ctx = canvas.getContext( "2d" );

        img.onload = function () {

            ctx.drawImage( img, col * ( 32+padding ) + padding, row * ( 32 + padding ) );
            callback();

        };

        img.src = src;

    }

} )();
