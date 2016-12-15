/**
 * Created by hn on 14-3-31.
 */

define( function ( require ) {

    var kity = require( "kity" ),

        // UiUitls
        $$ = require( "ui/ui-impl/ui-utils" ),

        Button = require( "ui/ui-impl/button" ),
        Box = require( "ui/ui-impl/box" ),

        DrapdownBox = kity.createClass( "DrapdownBox", {

            constructor: function ( doc, options ) {

                this.options = options;
                this.toolbar = null;
                this.doc = doc;

                this.buttonElement = this.createButton();

                this.element = this.buttonElement.getNode();

                this.boxElement = this.createBox();

                this.buttonElement.mount( this.boxElement );

                this.initEvent();

            },

            initEvent: function () {

                var _self = this;

                // 通知工具栏互斥
                $$.on( this.element, "mousedown", function ( e ) {

                    e.preventDefault();
                    e.stopPropagation();

                    _self.toolbar.notify( "closeOther", _self );

                } );


                this.buttonElement.initEvent();
                this.boxElement.initEvent();

                this.boxElement.setSelectHandler( function ( val ) {
                    // 发布
                    $$.publish( "data.select", val );
                    _self.buttonElement.hide();
                } );

            },

            disable: function () {
                this.buttonElement.disable();
            },

            enable: function () {
                this.buttonElement.enable();
            },

            setToolbar: function ( toolbar ) {
                this.toolbar = toolbar;
                this.buttonElement.setToolbar( toolbar );
                this.boxElement.setToolbar( toolbar );
            },

            createButton: function () {

                return new Button( this.doc, this.options.button );

            },

            show: function () {
                this.buttonElement.show();
            },

            hide: function () {
                this.buttonElement.hide();
            },

            createBox: function () {

                return new Box( this.doc, this.options.box );

            },

            attachTo: function ( container ) {

                container.appendChild( this.element );

            }

        });

    return DrapdownBox;

} );