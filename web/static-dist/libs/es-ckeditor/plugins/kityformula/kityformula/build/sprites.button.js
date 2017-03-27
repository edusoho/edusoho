/*!
 * icon生成器: 按钮ICON
 */

( function () {

    'use strict';

    var config = window.iconConfig,
        canvas = document.createElement( 'canvas' ),
        ctx = null,
        maxHeight = 0,
        lastOffset = 0,
        keySet = [],
        currentIndex = 0,
        padding = 5,
        position = {};

    //------------------------ init
    ( function () {
        config = {
            fx: "/kityformula-editor/assets/images/toolbar/button/fx.png",
            frac: "/kityformula-editor/assets/images/toolbar/button/frac.png",
            script: "/kityformula-editor/assets/images/toolbar/button/script.png",
            sqrt: "/kityformula-editor/assets/images/toolbar/button/sqrt.png",
            int: "/kityformula-editor/assets/images/toolbar/button/int.png",
            sum: "/kityformula-editor/assets/images/toolbar/button/sum.png",
            brackets: "/kityformula-editor/assets/images/toolbar/button/brackets.png",
            sin: "/kityformula-editor/assets/images/toolbar/button/sin.png",
            up: "/kityformula-editor/assets/images/toolbar/button/up.png",
            down: "/kityformula-editor/assets/images/toolbar/button/down.png",
            open: "/kityformula-editor/assets/images/toolbar/button/open.png",
            tick: "/kityformula-editor/assets/images/toolbar/button/tick.png"
        };
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

    function createSprites ( conf ) {

        for ( var key in conf ) {

            if ( !conf.hasOwnProperty( key ) ) {
                continue;
            }

            keySet.push( key );

        }

        if ( keySet.length ) {
            next();
        }

    }

    function next () {

        var key = keySet[ currentIndex ];

        if ( !key ) {
            return outputResult();
        }

        createImage( key, config[ key ], function () {

            next();

        } );

        currentIndex++;

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
                x: offset,
                y: 0
            };

            image.onload = null;
            image = null;

            callback();

        };

        image.src = imgSrc;

    }

} )();
