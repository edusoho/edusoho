/*!
 * 光标定位组件
 */

define( function ( require, exports, module ) {

    var kity = require( "kity" );

    return kity.createClass( "LocationComponent", {

        constructor: function ( parentComponent, kfEditor ) {

            this.parentComponent = parentComponent;
            this.kfEditor = kfEditor;

            // 创建光标
            this.paper = this.getPaper();
            this.cursorShape = this.createCursor();

            this.initServices();

            this.initEvent();

        },

        getPaper: function () {
            return this.kfEditor.requestService( "render.get.paper" );
        },

        initServices: function () {

            // 重定位光标
            this.kfEditor.registerService( "control.cursor.relocation", this, {
                relocationCursor: this.updateCursor
            } );

            // 清除光标
            this.kfEditor.registerService( "control.cursor.hide", this, {
                hideCursor: this.hideCursor
            } );

            this.kfEditor.registerService( "control.reselect", this, {
                reselect: this.reselect
            } );

            this.kfEditor.registerService( "control.get.cursor.location", this, {
                getCursorLocation: this.getCursorLocation
            } );

        },

        createCursor: function () {

            var cursorShape = new kity.Rect( 1, 0, 0, 0 ).fill( "black" );

            cursorShape.setAttr( "style", "display: none" );

            this.paper.addShape( cursorShape );

            return cursorShape;

        },

        // 光标定位监听
        initEvent: function () {

            var eventServiceObject = this.kfEditor.request( "ui.canvas.container.event" ),
                _self = this;

            eventServiceObject.on( "mousedown", function ( e ) {

                e.preventDefault();

                _self.updateCursorInfo( e );
                _self.kfEditor.requestService( "control.update.input" );
                _self.reselect();

            } );

        },

        updateCursorInfo: function ( evt ) {

            var wrapNode = null,
                groupInfo = null,
                index = -1;

            // 有根占位符存在， 所有定位到定位到根占位符内部
            if ( this.kfEditor.requestService( "syntax.has.root.placeholder" ) ) {

                this.kfEditor.requestService( "syntax.update.record.cursor", {
                    groupId: this.kfEditor.requestService( "syntax.get.root.group.info" ).id,
                    startOffset: 0,
                    endOffset: 1
                } );

                return false;
            }

            wrapNode = this.kfEditor.requestService( "position.get.wrap", evt.target );

            // 占位符处理, 选中该占位符
            if ( wrapNode && this.kfEditor.requestService( "syntax.is.placeholder.node", wrapNode.id ) ) {
                groupInfo = this.kfEditor.requestService( "position.get.group.info", wrapNode );
                this.kfEditor.requestService( "syntax.update.record.cursor", groupInfo.group.id, groupInfo.index, groupInfo.index + 1 );
                return;
            }

            groupInfo = this.kfEditor.requestService( "position.get.group", evt.target );

            if ( groupInfo === null ) {
                groupInfo = this.kfEditor.requestService( "syntax.get.root.group.info" );
            }

            index = this.getIndex( evt.clientX, groupInfo );

            this.kfEditor.requestService( "syntax.update.record.cursor", groupInfo.id, index );

        },

        hideCursor: function () {
            this.cursorShape.setAttr( "style", "display: none" );
        },

        // 根据当前的光标信息， 对选区和光标进行更新
        reselect: function () {

            var cursorInfo = this.kfEditor.requestService( "syntax.get.record.cursor" ),
                groupInfo = null;

            this.hideCursor();

            // 根节点单独处理
            if ( this.kfEditor.requestService( "syntax.is.select.placeholder" ) ) {

                groupInfo = this.kfEditor.requestService( "syntax.get.group.content", cursorInfo.groupId );
                this.kfEditor.requestService( "render.select.group", groupInfo.content[ cursorInfo.startOffset ].id );
                return;

            }

            if ( cursorInfo.startOffset === cursorInfo.endOffset ) {
                // 更新光标位置
                this.updateCursor();
                // 请求背景着色
                this.kfEditor.requestService( "render.tint.current.cursor" );
            } else {
                this.kfEditor.requestService( "render.select.current.cursor" );
            }

        },

        updateCursor: function () {

            var cursorInfo = this.kfEditor.requestService( "syntax.get.record.cursor" );

            if ( cursorInfo.startOffset !== cursorInfo.endOffset ) {
                this.hideCursor();
                return;
            }

            var groupInfo = this.kfEditor.requestService( "syntax.get.group.content", cursorInfo.groupId ),
                isBefore = cursorInfo.endOffset === 0,
                index = isBefore ? 0 : cursorInfo.endOffset - 1,
                focusChild = groupInfo.content[ index ],
                paperContainerRect = getRect( this.paper.container.node ),
                cursorOffset = 0,
                focusChildRect = getRect( focusChild ),
                cursorTransform = this.cursorShape.getTransform( this.cursorShape ),
                canvasZoom = this.kfEditor.requestService( "render.get.canvas.zoom" ),
                formulaZoom = this.paper.getZoom();

            this.cursorShape.setHeight( focusChildRect.height / canvasZoom / formulaZoom );

            // 计算光标偏移位置
            cursorOffset = isBefore ? ( focusChildRect.left - 2 ) : ( focusChildRect.left + focusChildRect.width - 2 );
            cursorOffset -= paperContainerRect.left;

            // 定位光标
            cursorTransform.m.e = Math.floor( cursorOffset / canvasZoom / formulaZoom ) + 0.5 ;
            cursorTransform.m.f = ( focusChildRect.top - paperContainerRect.top ) / canvasZoom / formulaZoom;

            this.cursorShape.setMatrix( cursorTransform );
            this.cursorShape.setAttr( "style", "display: block" );

        },

        getCursorLocation: function () {

            var rect = this.cursorShape.getRenderBox( "paper" );

            return {
                x: rect.x,
                y: rect.y
            };

        },

        getIndex: function ( distance, groupInfo ) {

            var index = -1,
                children = groupInfo.content,
                boundingRect = null;

            for ( var i = children.length - 1, child = null; i >= 0; i-- ) {

                index = i;

                child = children[ i ];

                boundingRect = getRect( child );

                if ( boundingRect.left < distance ) {

                    if ( boundingRect.left + boundingRect.width / 2 < distance ) {
                        index += 1;
                    }

                    break;

                }

            }

            return index;

        }

    } );

    function getRect ( node ) {
        return node.getBoundingClientRect();
    }

} );