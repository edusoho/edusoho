/*!
 * 特殊字符区域
 */

define( function ( require ) {

    var kity = require( "kity" ),

        PREFIX = "kf-editor-ui-",

        PANEL_HEIGHT = 66,
        // UiUitls
        $$ = require( "ui/ui-impl/ui-utils" ),

        Box = require( "ui/ui-impl/box" ),

        Area = kity.createClass( "Area", {

            constructor: function ( doc, options ) {

                this.options = options;

                this.doc = doc;
                this.toolbar = null;
                this.disabled = true;

                this.panelIndex = 0;
                this.maxPanelIndex = 0;
                this.currentItemCount = 0;
                this.lineMaxCount = 9;

                this.element = this.createArea();
                this.container = this.createContainer();
                this.panel = this.createPanel();
                this.buttonContainer = this.createButtonContainer();
                this.button = this.createButton();
                this.mountPoint = this.createMountPoint();
                this.moveDownButton = this.createMoveDownButton();
                this.moveUpButton = this.createMoveUpButton();

                this.boxObject = this.createBox();
                this.mergeElement();
                this.mount();

                this.setListener();
                this.initEvent();

            },

            initEvent: function () {

                var _self = this;

                $$.on( this.button, "mousedown", function ( e ) {

                    e.preventDefault();
                    e.stopPropagation();

                    if ( e.which !== 1 || _self.disabled ) {
                        return;
                    }

                    _self.showMount();
                    _self.toolbar.notify( "closeOther", _self );

                } );

                $$.on( this.moveDownButton, "mousedown", function ( e ) {

                    e.preventDefault();
                    e.stopPropagation();

                    if ( e.which !== 1 || _self.disabled ) {
                        return;
                    }

                    _self.nextPanel();
                    _self.toolbar.notify( "closeOther", _self );

                } );

                $$.on( this.moveUpButton, "mousedown", function ( e ) {

                    e.preventDefault();
                    e.stopPropagation();

                    if ( e.which !== 1 || _self.disabled ) {
                        return;
                    }

                    _self.prevPanel();
                    _self.toolbar.notify( "closeOther", _self );

                } );

                $$.delegate( this.container, ".kf-editor-ui-area-item", "mousedown", function ( e ) {

                    e.preventDefault();

                    if ( e.which !== 1 || _self.disabled ) {
                        return;
                    }

                    $$.publish( "data.select", this.getAttribute( "data-value" ) );

                } );

                this.boxObject.initEvent();

            },

            disable: function () {
                this.disabled = true;
                this.boxObject.disable();
                $$.getClassList( this.element ).remove( PREFIX + "enabled" );
            },

            enable: function () {
                this.disabled = false;
                this.boxObject.enable();
                $$.getClassList( this.element ).add( PREFIX + "enabled" );
            },

            setListener: function () {

                var _self = this;

                this.boxObject.setSelectHandler( function ( val ) {
                    // 发布
                    $$.publish( "data.select", val );
                    _self.hide();
                } );

                // 内容面板切换
                this.boxObject.setChangeHandler( function ( index ) {
                    _self.updateContent();
                } );

            },

            createArea: function () {

                var areaNode = $$.ele( this.doc, "div", {
                        className: PREFIX + "area"
                    });

                if ( "width" in this.options ) {
                    areaNode.style.width = this.options.width + "px";
                }

                return areaNode;

            },

            checkMaxPanelIndex: function () {

                this.maxPanelIndex = Math.ceil( this.currentItemCount / this.lineMaxCount / 2 );

            },

            updateContent: function () {

                var items = this.boxObject.getOverlapContent(),
                    count = 0,
                    style = null,
                    lineno = 0,
                    colno = 0,
                    lineMaxCount = this.lineMaxCount,
                    newContent = [];

                // 清空原有内容
                this.panel.innerHTML = "";

                kity.Utils.each( items, function ( item ) {

                    var contents = item.content;

                    kity.Utils.each( contents, function ( currentContent, index ) {

                        lineno = Math.floor( count / lineMaxCount );
                        colno = count % lineMaxCount;
                        count++;

                        style = "top: " + ( lineno * 33 + 5 ) + "px; left: " + ( colno * 32 + 5 ) + "px;" ;

                        newContent.push( '<div class="'+ PREFIX +'area-item" data-value="'+ currentContent.key +'" style="'+ style +'"><div class="'+ PREFIX +'area-item-inner"><div class="'+ PREFIX +'area-item-img" style="background: url('+ currentContent.img +') no-repeat '+ -currentContent.pos.x + 'px ' + -currentContent.pos.y +'px;"></div></div></div>' );

                    } );

                } );


                this.currentItemCount = count;
                this.panelIndex = 0;
                this.panel.style.top = 0;
                this.panel.innerHTML = newContent.join( "" );

                this.checkMaxPanelIndex();
                this.updatePanelButtonState();

            },

            // 挂载
            mount: function () {

                this.boxObject.mountTo( this.mountPoint );

            },

            showMount: function () {
                this.mountPoint.style.display = "block";
                this.boxObject.updateSize();
            },

            hideMount: function () {
                this.mountPoint.style.display = "none";
            },

            hide: function () {
                this.hideMount();
                this.boxObject.hide();
            },

            createButton: function () {

                return $$.ele( this.doc, "div", {
                    className: PREFIX + "area-button"
                } );

            },

            createMoveDownButton: function () {

                return $$.ele( this.doc, "div", {
                    className: PREFIX + "movedown-button",
                    content: ""
                } );

            },

            createMoveUpButton: function () {

                return $$.ele( this.doc, "div", {
                    className: PREFIX + "moveup-button",
                    content: ""
                } );

            },

            createMountPoint: function () {

                return $$.ele( this.doc, "div", {
                    className: PREFIX + "area-mount"
                } );

            },

            createBox: function () {

                return new Box( this.doc, this.options.box );

            },

            createContainer: function () {

                return $$.ele( this.doc, "div", {
                    className: PREFIX + "area-container"
                } );

            },

            createPanel: function () {

                return $$.ele( this.doc, "div", {
                    className: PREFIX + "area-panel"
                } );

            },

            createButtonContainer: function () {
                return $$.ele( this.doc, "div", {
                    className: PREFIX + "area-button-container"
                } );
            },

            mergeElement: function () {

                this.buttonContainer.appendChild( this.moveUpButton );
                this.buttonContainer.appendChild( this.moveDownButton );
                this.buttonContainer.appendChild( this.button );

                this.container.appendChild( this.panel );

                this.element.appendChild( this.container );
                this.element.appendChild( this.buttonContainer );
                this.element.appendChild( this.mountPoint );

            },

            disablePanelUp: function () {
                this.disabledUp = true;
                $$.getClassList( this.moveUpButton ).add( "kf-editor-ui-disabled" );
            },

            enablePanelUp: function () {
                this.disabledUp = false;
                $$.getClassList( this.moveUpButton ).remove( "kf-editor-ui-disabled" );
            },

            disablePanelDown: function () {
                this.disabledDown = true;
                $$.getClassList( this.moveDownButton ).add( "kf-editor-ui-disabled" );
            },

            enablePanelDown: function () {
                this.disabledDown = false;
                $$.getClassList( this.moveDownButton ).remove( "kf-editor-ui-disabled" );
            },

            updatePanelButtonState: function () {

                if ( this.panelIndex === 0 ) {
                    this.disablePanelUp();
                } else {
                    this.enablePanelUp();
                }

                if ( this.panelIndex + 1 >= this.maxPanelIndex ) {
                    this.disablePanelDown();
                } else {
                    this.enablePanelDown();
                }

            },

            nextPanel: function () {

                if ( this.disabledDown ) {
                    return;
                }

                if ( this.panelIndex + 1 >= this.maxPanelIndex ) {
                    return;
                }

                this.panelIndex++;

                this.panel.style.top = -this.panelIndex * PANEL_HEIGHT + "px";

                this.updatePanelButtonState();

            },

            prevPanel: function () {

                if ( this.disabledUp ) {
                    return;
                }

                if ( this.panelIndex === 0 ) {
                    return;
                }

                this.panelIndex--;

                this.panel.style.top = - this.panelIndex * PANEL_HEIGHT + "px";

                this.updatePanelButtonState();

            },

            setToolbar: function ( toolbar ) {
                this.toolbar = toolbar;
                this.boxObject.setToolbar( toolbar );
            },

            attachTo: function ( container ) {
                container.appendChild( this.element );
                this.updateContent();
                this.updatePanelButtonState();
            }

        } );

    return Area;

} );