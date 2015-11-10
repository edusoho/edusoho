/*!
 * 工具条组件
 */

define( function ( require ) {

    var kity = require( "kity" ),

        UiImpl = require( "ui/ui-impl/ui" ),

        $$ = require( "ui/ui-impl/ui-utils" ),

        UI_ELE_TYPE = require( "ui/ui-impl/def/ele-type" ),

        Tollbar = kity.createClass( "Tollbar", {

            constructor: function ( uiComponent, kfEditor, elementList ) {

                this.kfEditor = kfEditor;
                this.uiComponent = uiComponent;

                // 工具栏元素定义列表
                this.elementList = elementList;

                this.elements = [];

                this.initToolbarElements();

                this.initServices();

                this.initEvent();

            },

            initServices: function () {

                this.kfEditor.registerService( "ui.toolbar.disable", this, {
                    disableToolbar: this.disableToolbar
                } );

                this.kfEditor.registerService( "ui.toolbar.enable", this, {
                    enableToolbar: this.enableToolbar
                } );

                this.kfEditor.registerService( "ui.toolbar.close", this, {
                    closeToolbar: this.closeToolbar
                } );

            },

            initEvent: function () {

                var _self = this;

                $$.on( this.uiComponent.toolbarContainer, "mousedown", function ( e ) {
                    e.preventDefault();
                } );

                $$.on( this.uiComponent.toolbarContainer, "mousewheel", function ( e ) {
                    e.preventDefault();
                } );

                // 通知所有组件关闭
                $$.on( this.kfEditor.getContainer(), "mousedown", function () {
                    _self.notify( "closeAll" );
                } );

                // 订阅数据选择主题
                $$.subscribe( "data.select", function ( data ) {

                    _self.insertSource( data );

                } );

            },

            insertSource: function ( val ) {

                this.kfEditor.requestService( "control.insert.string", val );

            },

            disableToolbar: function () {

                kity.Utils.each( this.elements, function ( ele ) {
                    ele.disable && ele.disable();
                } );

            },

            enableToolbar: function () {

                kity.Utils.each( this.elements, function ( ele ) {
                    ele.enable && ele.enable();
                } );

            },

            getContainer: function () {
                return this.kfEditor.requestService( "ui.get.canvas.container" );
            },

            closeToolbar: function () {
                this.closeElement();
            },

            // 接受到关闭通知
            notify: function ( type ) {

                switch ( type ) {

                    // 关闭所有组件
                    case "closeAll":
                    // 关闭其他组件
                    case "closeOther":
                        this.closeElement( arguments[ 1 ] );
                        return;

                }

            },

            closeElement: function ( exception ) {

                kity.Utils.each( this.elements, function ( ele ) {

                    if ( ele != exception ) {
                        ele.hide && ele.hide();
                    }

                } );

            },

            initToolbarElements: function () {

                var elements = this.elements,
                    doc = this.uiComponent.toolbarContainer.ownerDocument,
                    _self = this;

                kity.Utils.each( this.elementList, function ( eleInfo, i ) {

                    var ele = createElement( eleInfo.type, doc, eleInfo.options );
                    elements.push( ele );
                    _self.appendElement( ele );

                } );

            },

            appendElement: function ( uiElement ) {

                uiElement.setToolbar( this );
                uiElement.attachTo( this.uiComponent.toolbarContainer );

            }

        } );

    function createElement ( type, doc, options ) {

        switch ( type ) {

            case UI_ELE_TYPE.DRAPDOWN_BOX:
                return createDrapdownBox( doc, options );

            case UI_ELE_TYPE.DELIMITER:
                return createDelimiter( doc );

            case UI_ELE_TYPE.AREA:
                return createArea( doc, options );


        }

    }

    function createDrapdownBox ( doc, options ) {

        return new UiImpl.DrapdownBox( doc, options );

    }

    function createDelimiter ( doc ) {
        return new UiImpl.Delimiter( doc );
    }

    function createArea ( doc, options ) {
        return new UiImpl.Area( doc, options );
    }

    return Tollbar;

} );