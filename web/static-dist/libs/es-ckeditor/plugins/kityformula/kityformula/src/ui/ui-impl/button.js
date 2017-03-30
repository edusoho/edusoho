/**
 * Created by hn on 14-3-31.
 */

define( function ( require ) {

    var kity = require( "kity" ),

        PREFIX = "kf-editor-ui-",

        LIST_OFFSET = 7,

        DEFAULT_OPTIONS = {
            iconSize: {
                w: 32,
                h: 32
            }
        },

        // UiUitls
        $$ = require( "ui/ui-impl/ui-utils" ),

        Button = kity.createClass( "Button", {

            constructor: function ( doc, options ) {

                this.options = kity.Utils.extend( {}, DEFAULT_OPTIONS, options );

                // 事件状态， 是否已经初始化
                this.eventState = false;
                this.toolbar = null;
                this.displayState = false;
                this.fixOffset = options.fixOffset || false;

                this.doc = doc;

                this.element = this.createButton();
                this.disabled = true;

                // 挂载的对象
                this.mountElement = null;

                this.icon = this.createIcon();
                this.label = this.createLabel();
                this.sign = this.createSign();
                this.mountPoint = this.createMountPoint();

                this.mergeElement();

            },

            initEvent: function () {

                var _self = this;

                if ( this.eventState ) {
                    return;
                }

                this.eventState = true;

                $$.on( this.element, "mousedown", function ( e ) {

                    e.preventDefault();
                    e.stopPropagation();

                    if ( e.which !== 1 ) {
                        return;
                    }

                    if ( _self.disabled ) {
                        return;
                    }

                    _self.toggleSelect();
                    _self.toggleMountElement();

                } );

            },

            setToolbar: function ( toolbar ) {
                this.toolbar = toolbar;
            },

            toggleMountElement: function () {

                if ( this.displayState ) {
                    this.hideMount();
                } else {
                    this.showMount();
                }

            },

            setLabel: function ( labelText ) {
                var signText = "";
                if ( this.sign ) {
                    signText = '<div class="'+ PREFIX + 'button-sign"></div>';
                }
                this.label.innerHTML = labelText + signText;
            },

            toggleSelect: function () {
                $$.getClassList( this.element ).toggle( PREFIX + "button-in" );
            },

            unselect: function () {
                $$.getClassList( this.element ).remove( PREFIX + "button-in" );
            },

            select: function () {
                $$.getClassList( this.element ).add( PREFIX + "button-in" );
            },

            show: function () {
                this.select();
                this.showMount();
            },

            hide: function () {
                this.unselect();
                this.hideMount();
            },

            showMount: function () {

                this.displayState = true;
                this.mountPoint.style.display = "block";

                if ( this.fixOffset ) {

                    var elementRect = this.element.getBoundingClientRect();
                    this.mountElement.setOffset( elementRect.left + LIST_OFFSET, elementRect.bottom );

                }

                var editorContainer = this.toolbar.getContainer(),
                    currentBox = null,
                    containerBox = $$.getRectBox( editorContainer ),
                    mountEleBox = this.mountElement.getPositionInfo();

                // 修正偏移
                if ( mountEleBox.right > containerBox.right ) {
                    currentBox = $$.getRectBox( this.element );
                    // 对齐到按钮的右边界
                    this.mountPoint.style.left = currentBox.right - mountEleBox.right - 1 + "px";
                }

                this.mountElement.updateSize && this.mountElement.updateSize();

            },

            hideMount: function () {
                this.displayState = false;
                this.mountPoint.style.display = "none";
            },

            getNode: function () {
                return this.element;
            },

            mount: function ( element ) {
                this.mountElement = element;
                element.mountTo( this.mountPoint );
            },

            createButton: function () {

                var buttonNode = $$.ele( this.doc, "div", {
                    className: PREFIX + "button"
                } );

                // 附加className
                if ( this.options.className ) {
                    buttonNode.className += " " + PREFIX + this.options.className;
                }

                return buttonNode;

            },

            createIcon: function () {

                if ( !this.options.icon ) {
                    return null;
                }

                var iconNode = $$.ele( this.doc, "div", {
                    className: PREFIX + "button-icon"
                } );

                if ( typeof this.options.icon === "string" ) {
                    iconNode.style.backgroundImage = "url(" + this.options.icon + ") no-repeat";
                } else {
                    iconNode.style.background = getBackgroundStyle( this.options.icon );
                }

                if ( this.options.iconSize.w ) {
                    iconNode.style.width = this.options.iconSize.w + "px";
                }

                if ( this.options.iconSize.h ) {
                    iconNode.style.height = this.options.iconSize.h + "px";
                }

                return iconNode;

            },

            createLabel: function () {

                var labelNode = $$.ele( this.doc, "div", {
                    className: PREFIX + "button-label",
                    content: this.options.label
                } );


                return labelNode;

            },

            createSign: function () {

                if ( this.options.sign === false ) {
                    return null;
                }

                return $$.ele( this.doc, "div", {
                    className: PREFIX + "button-sign"
                } );

            },

            createMountPoint: function () {

                return $$.ele( this.doc, "div", {
                    className: PREFIX + "button-mount-point"
                } );

            },

            disable: function () {
                this.disabled = true;
                $$.getClassList( this.element ).remove( PREFIX + "enabled" );
            },

            enable: function () {
                this.disabled = false;
                $$.getClassList( this.element ).add( PREFIX + "enabled" );
            },

            mergeElement: function () {

                this.icon && this.element.appendChild( this.icon );
                this.element.appendChild( this.label );
                this.sign && this.label.appendChild( this.sign );
                this.element.appendChild( this.mountPoint );

            }

        } );

    function getBackgroundStyle ( data ) {

        var style = "url( " + data.src + " ) no-repeat ";

        style += -data.x + 'px ';
        style += -data.y + 'px';

        return style;

    }

    return Button;

} );