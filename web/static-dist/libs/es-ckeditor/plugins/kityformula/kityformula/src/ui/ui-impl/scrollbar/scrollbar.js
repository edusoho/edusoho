/*!
 * 滚动条组件
 */

define( function ( require ) {

    var kity = require( "kity" ),
        SCROLLBAR_DEF = require( "ui/def" ).scrollbar,
        SCROLLBAR_CONF = require( "sysconf" ).scrollbar,
        Utils = require( "base/utils" ),
        CLASS_PREFIX = "kf-editor-ui-";

    return kity.createClass( "Scrollbar", {

        constructor: function ( uiComponent, kfEditor ) {

            this.uiComponent = uiComponent;
            this.kfEditor = kfEditor;

            this.widgets = null;
            this.container = this.uiComponent.scrollbarContainer;

            // 显示状态
            this.state = false;

            // 滚动条当前各个状态下的值
            this.values = {
                // 滚动条此时实际的偏移值, 计算的时候假定滑块的宽度为0
                offset: 0,
                // 滑块此时偏移位置所占轨道的比例, 计算的时候假定滑块的宽度为0
                left: 0,
                // 滚动条控制的容器的可见宽度
                viewWidth: 0,
                // 滚动条对应的内容实际宽度
                contentWidth: 0,
                // 轨道长度
                trackWidth: 0,
                // 滑块宽度
                thumbWidth: 0,
                // 可滚动的宽度
                scrollWidth: 0
            };

            // 滑块的物理偏移， 不同于values.offset
            this.thumbLocationX = 0;
            // 左溢出长度
            this.leftOverflow = 0;
            // 右溢出长度
            this.rightOverflow = 0;
            // 记录本次和上一次改变内容之间宽度是否变大
            this.isExpand = true;

            this.initWidget();
            this.mountWidget();
            this.initSize();

            this.hide();
            this.initServices();
            this.initEvent();

            this.updateHandler = function (){};

        },

        initWidget: function () {

            var doc = this.container.ownerDocument;

            this.widgets = {
                leftButton: createElement( doc, "div", "left-button" ),
                rightButton: createElement( doc, "div", "right-button" ),
                track: createElement( doc, "div", "track" ),
                thumb: createElement( doc, "div", "thumb" ),
                thumbBody: createElement( doc, "div", "thumb-body" )
            };

        },

        initSize: function () {

            var leftBtnWidth = getRect( this.widgets.leftButton ).width,
                rightBtnWidth = getRect( this.widgets.rightButton ).width;

            this.values.viewWidth = getRect( this.container ).width;
            this.values.trackWidth = this.values.viewWidth - leftBtnWidth - rightBtnWidth;

            this.widgets.track.style.width = this.values.trackWidth + "px";

        },

        initServices: function () {

            this.kfEditor.registerService( "ui.show.scrollbar", this, {
                showScrollbar: this.show
            } );

            this.kfEditor.registerService( "ui.hide.scrollbar", this, {
                hideScrollbar: this.hide
            } );

            this.kfEditor.registerService( "ui.update.scrollbar", this, {
                updateScrollbar: this.update
            } );

            this.kfEditor.registerService( "ui.set.scrollbar.update.handler", this, {
                setUpdateHandler: this.setUpdateHandler
            } );

            this.kfEditor.registerService( "ui.relocation.scrollbar", this, {
                relocation: this.relocation
            } );

        },

        initEvent: function () {

            preventDefault( this );
            trackClick( this );
            thumbHandler( this );
            btnClick( this );

        },

        mountWidget: function () {

            var widgets = this.widgets,
                container = this.container;

            for ( var wgtName in widgets ) {
                if ( widgets.hasOwnProperty( wgtName ) ) {
                    container.appendChild( widgets[ wgtName ] );
                }
            }

            widgets.thumb.appendChild( widgets.thumbBody );
            widgets.track.appendChild( widgets.thumb );

        },

        show: function () {
            this.state = true;
            this.container.style.display = "block";
        },

        hide: function () {
            this.state = false;
            this.container.style.display = "none";
        },

        update: function ( contentWidth ) {

            var trackWidth = this.values.trackWidth,
                thumbWidth = 0;

            this.isExpand = contentWidth > this.values.contentWidth;
            this.values.contentWidth = contentWidth;
            this.values.scrollWidth = contentWidth - this.values.viewWidth;

            if ( trackWidth >= contentWidth ) {
                this.hide();
                return;
            }

            thumbWidth = Math.max( Math.ceil( trackWidth * trackWidth / contentWidth ), SCROLLBAR_DEF.thumbMinSize );

            this.values.thumbWidth = thumbWidth;
            this.widgets.thumb.style.width = thumbWidth + "px";
            this.widgets.thumbBody.style.width = thumbWidth - 10 + "px";

        },

        setUpdateHandler: function ( updateHandler ) {
            this.updateHandler = updateHandler;
        },

        updateOffset: function ( offset ) {

            var values = this.values;

            values.offset = offset;
            values.left = offset / values.trackWidth;

            this.leftOverflow = values.left * ( values.contentWidth-values.viewWidth );
            this.rightOverflow = values.contentWidth-values.viewWidth-this.leftOverflow;

            this.updateHandler( values.left, values.offset, values );

        },

        relocation: function () {

            var cursorLocation = this.kfEditor.requestService( "control.get.cursor.location" ),
                padding = SCROLLBAR_CONF.padding,
                contentWidth = this.values.contentWidth,
                viewWidth = this.values.viewWidth,
                // 视图左溢出长度
                viewLeftOverflow = this.values.left * ( contentWidth - viewWidth ),
                diff = 0;

            if ( cursorLocation.x < viewLeftOverflow ) {

                if ( cursorLocation.x < 0 ) {
                    cursorLocation.x = 0;
                }

                setThumbOffsetByViewOffset( this, cursorLocation.x );

            } else if ( cursorLocation.x + padding > viewLeftOverflow + viewWidth ) {

                cursorLocation.x += padding;
                
                if ( cursorLocation.x > contentWidth ) {
                    cursorLocation.x = contentWidth;
                }

                diff = cursorLocation.x - viewWidth;

                setThumbOffsetByViewOffset( this, diff );

            } else {
                if ( this.isExpand ) {
                    // 根据上一次左溢出值设置滑块位置
                    setThumbByLeftOverflow( this, this.leftOverflow );
                } else  {
                    // 减少左溢出
                    setThumbByLeftOverflow( this, contentWidth - viewWidth - this.rightOverflow );
                }
            }
        }

    } );

    function createElement ( doc, eleName, className ) {

        var node = doc.createElement( eleName ),
            str = '<div class="$1"></div><div class="$2"></div>';

        node.className = CLASS_PREFIX + className;

        if ( className === "thumb" ) {
            className = CLASS_PREFIX + className;
            node.innerHTML = str.replace( '$1', className+'-left' )
                .replace( '$2', className+'-right' );
        }

        return node;

    }

    function getRect ( node ) {
        return node.getBoundingClientRect();
    }

    // 阻止浏览器在scrollbar上的默认行为
    function preventDefault ( container ) {

        Utils.addEvent( container, "mousedown", function ( e ) {
            e.preventDefault();
        } );

    }

    function preventDefault ( comp ) {

        Utils.addEvent( comp.container, "mousedown", function ( e ) {
            e.preventDefault();
        } );

    }

    // 轨道点击
    function trackClick ( comp ) {

        Utils.addEvent( comp.widgets.track, "mousedown", function ( e ) {
            trackClickHandler( this, comp, e );
        } );

    }

    // 两端按钮点击
    function btnClick ( comp ) {

        // left
        Utils.addEvent( comp.widgets.leftButton, "mousedown", function () {

            setThumbOffsetByStep( comp, -SCROLLBAR_CONF.step );

        } );

        Utils.addEvent( comp.widgets.rightButton, "mousedown", function () {

            setThumbOffsetByStep( comp, SCROLLBAR_CONF.step );

        } );

    }

    // 滑块处理
    function thumbHandler ( comp ) {

        var isMoving = false,
            startPoint = 0,
            startOffset = 0,
            trackWidth = comp.values.trackWidth;

        Utils.addEvent( comp.widgets.thumb, "mousedown", function ( e ) {

            e.preventDefault();
            e.stopPropagation();

            isMoving = true;
            startPoint = e.clientX;
            startOffset = comp.thumbLocationX;

        } );

        Utils.addEvent( comp.container.ownerDocument, "mouseup", function () {

            isMoving = false;
            startPoint = 0;
            startOffset = 0;

        } );

        Utils.addEvent( comp.container.ownerDocument, "mousemove", function ( e ) {

            if ( !isMoving ) {
                return;
            }

            var distance = e.clientX - startPoint,
                offset = startOffset + distance,
                thumbWidth = comp.values.thumbWidth;

            if ( offset < 0 ) {
                offset = 0;
            } else if ( offset + thumbWidth > trackWidth ) {
                offset = trackWidth - thumbWidth;
            }

            setThumbLocation( comp, offset );

        } );

    }

    // 轨道点击处理器
    function trackClickHandler ( track, comp, evt ) {

        var trackRect = getRect( track ),
            values = comp.values,
            // 单位偏移值， 一个viewWidth所对应到轨道上后的offset值
            unitOffset = values.viewWidth / ( values.contentWidth - values.viewWidth ) * values.trackWidth,
            // 点击位置在轨道中的偏移
            clickOffset = evt.clientX - trackRect.left;

        // right click
        if ( clickOffset > values.offset ) {

            // 剩余距离已经不足以支撑滚动， 则直接偏移置最大
            if ( values.offset + unitOffset > values.trackWidth ) {
                setThumbOffset( comp, values.trackWidth );
            } else {
                setThumbOffset( comp, values.offset + unitOffset );
            }

        // left click
        } else {

            // 剩余距离已经不足以支撑滚动， 则直接把偏移置零
            if ( values.offset - unitOffset < 0 ) {
                setThumbOffset( comp, 0 );
            } else {
                setThumbOffset( comp, values.offset - unitOffset );
            }

        }

    }

    function setThumbLocation ( comp, locationX ) {

        // 滑块偏移值
        var values = comp.values,
            trackPieceWidth = values.trackWidth - values.thumbWidth,
            offset = Math.floor( ( locationX / trackPieceWidth ) * values.trackWidth );

        comp.updateOffset( offset );

        // 更新滑块物理偏移: 定位
        comp.thumbLocationX = locationX;
        comp.widgets.thumb.style.left = locationX + "px";

    }

    // 根据指定的内容视图上移动的步长来改变滚动条的offset值
    function setThumbOffsetByStep ( comp, step ) {

        var leftOverflow = comp.leftOverflow + step;

        // 修正越界
        if ( leftOverflow < 0 ) {
            leftOverflow = 0;
        } else if ( leftOverflow > comp.values.scrollWidth ) {
            leftOverflow = comp.values.scrollWidth;
        }

        setThumbByLeftOverflow( comp, leftOverflow );

    }

    // 设置偏移值, 会同时更新滑块在显示上的定位
    function setThumbOffset ( comp, offset ) {

        var values = comp.values,
            offsetProportion = offset / values.trackWidth,
            trackPieceWidth = values.trackWidth - values.thumbWidth,
            thumbLocationX = 0;

        thumbLocationX = Math.floor( offsetProportion * trackPieceWidth );

        if ( offset < 0 ) {
            offset = 0;
            thumbLocationX = 0;
        }

        comp.updateOffset( offset );

        // 更新滑块定位
        comp.widgets.thumb.style.left = thumbLocationX + "px";
        comp.thumbLocationX = thumbLocationX;

    }

    /**
     * 根据内容视图上的偏移值设置滑块位置
     */
    function setThumbOffsetByViewOffset ( comp, viewOffset ) {

        var values = comp.values,
            offsetProportion = 0,
            offset = 0;

        // 轨道偏移比例
        offsetProportion = viewOffset / ( values.contentWidth - values.viewWidth );

        // 轨道偏移值
        offset = Math.floor( offsetProportion * values.trackWidth );

        setThumbOffset( comp, offset );

    }

    /**
     * 根据左溢出值设置滑块定位
     */
    function setThumbByLeftOverflow ( comp, leftViewOverflow ) {

        var values = comp.values,
            overflowProportion = leftViewOverflow / ( values.contentWidth - values.viewWidth );

        setThumbOffset( comp, overflowProportion * values.trackWidth );

    }

} );
