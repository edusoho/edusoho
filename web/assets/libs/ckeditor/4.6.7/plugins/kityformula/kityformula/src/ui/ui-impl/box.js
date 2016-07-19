/**
 * Created by hn on 14-3-31.
 */

define( function ( require ) {

    var kity = require( "kity" ),

        PREFIX = "kf-editor-ui-",

        // UiUitls
        $$ = require( "ui/ui-impl/ui-utils" ),

        BOX_TYPE = require( "ui/ui-impl/def/box-type" ),

        ITEM_TYPE = require( "ui/ui-impl/def/item-type" ),

        Button = require( "ui/ui-impl/button" ),

        List = require( "ui/ui-impl/list" ),

        SCROLL_STEP = 20,

        Box = kity.createClass( "Box", {

            constructor: function ( doc, options ) {

                this.options = options;
                this.toolbar = null;
                this.options.type = this.options.type || BOX_TYPE.DETACHED;

                this.doc = doc;
                this.itemPanels = null;

                this.overlapButtonObject = null;
                this.overlapIndex = -1;

                this.element = this.createBox();
                this.groupContainer = this.createGroupContainer();
                this.itemGroups = this.createItemGroup();

                this.mergeElement();

            },

            createBox: function () {

                var boxNode = $$.ele( this.doc, "div", {
                    className: PREFIX + "box"
                } );

                if ( "width" in this.options ) {
                    boxNode.style.width = this.options.width + "px";
                }

                return boxNode;

            },

            setToolbar: function ( toolbar ) {
                this.toolbar = toolbar;
                if ( this.overlapButtonObject ) {
                    this.overlapButtonObject.setToolbar( toolbar );
                }
            },

            updateSize: function () {

                var containerBox = $$.getRectBox( this.toolbar.getContainer() ),
                    diff = 30,
                    curBox = $$.getRectBox( this.element );

                if ( this.options.type === BOX_TYPE.DETACHED ) {

                    if ( curBox.bottom <= containerBox.bottom ) {
                        this.element.scrollTop = 0;
                        return;
                    }

                    this.element.style.height = curBox.height - ( curBox.bottom - containerBox.bottom + diff ) + "px";

                } else {

                    var panel = this.getCurrentItemPanel(),
                        panelRect = null;

                    panel.scrollTop = 0;

                    if ( curBox.bottom <= containerBox.bottom ) {
                        return;
                    }

                    panelRect = getRectBox( panel );

                    panel.style.height = containerBox.bottom - panelRect.top - diff + "px";

                }

            },

            initEvent: function () {

                var className = "." + PREFIX + "box-item",
                    _self = this;

                $$.delegate( this.groupContainer, className, "mousedown", function ( e ) {

                    e.preventDefault();

                    if ( e.which !== 1 ) {
                        return;
                    }

                    _self.onselectHandler && _self.onselectHandler( this.getAttribute( "data-value" ) );

                } );

                $$.on( this.element, "mousedown", function ( e ) {

                    e.stopPropagation();
                    e.preventDefault();

                } );

                $$.on( this.element, "mousewheel", function ( e ) {

                    e.preventDefault();
                    e.stopPropagation();

                    _self.scroll( e.originalEvent.wheelDelta );

                } );

            },

            getNode: function () {
                return this.element;
            },

            setSelectHandler: function ( onselectHandler ) {
                this.onselectHandler = onselectHandler;
            },

            scroll: function ( delta ) {

                // down
                if ( delta < 0 ) {
                    this.scrollDown();
                } else {
                    this.scrollUp();
                    this.element.scrollTop -= 20;
                }

            },

            scrollDown: function () {

                if ( this.options.type === BOX_TYPE.DETACHED ) {
                    this.element.scrollTop += SCROLL_STEP;
                } else {
                    this.getCurrentItemPanel().scrollTop += SCROLL_STEP;
                }

            },

            scrollUp: function () {
                if ( this.options.type === BOX_TYPE.DETACHED ) {
                    this.element.scrollTop -= SCROLL_STEP;
                } else {
                    this.getCurrentItemPanel().scrollTop -= SCROLL_STEP;
                }
            },

            setChangeHandler: function ( changeHandler ) {
                this.onchangeHandler = changeHandler;
            },

            onchangeHandler: function ( index ) {},

            createGroupContainer: function () {

                return $$.ele( this.doc, "div", {
                    className: PREFIX + "box-container"
                } );

            },

            getPositionInfo: function () {
                return $$.getRectBox( this.element );
            },

            createItemGroup: function () {

                var itemGroup = this.createGroup();

                switch ( this.options.type ) {

                    case BOX_TYPE.DETACHED:
                        return itemGroup.items[ 0 ];

                    case BOX_TYPE.OVERLAP:
                        return this.createOverlapGroup( itemGroup );

                }

                return null;

            },

            enable: function () {
                if ( this.overlapButtonObject ) {
                    this.overlapButtonObject.enable();
                }
            },

            disable: function () {
                if ( this.overlapButtonObject ) {
                    this.overlapButtonObject.disable();
                }
            },

            hide: function () {
                this.overlapButtonObject && this.overlapButtonObject.hideMount();
            },

            getOverlapContent: function () {

                // 只有重叠式才可以获取重叠内容
                if ( this.options.type !== BOX_TYPE.OVERLAP ) {
                    return null;
                }

                return this.options.group[ this.overlapIndex ].items;

            },

            createOverlapGroup: function ( itemGroup ) {

                var classifyList = itemGroup.title,
                    _self = this,
                    overlapContainer = createOverlapContainer( this.doc),
                    overlapButtonObject = createOverlapButton( this.doc, {
                        fixOffset: this.options.fixOffset
                    } ),
                    overlapListObject = createOverlapList( this.doc, {
                        width: 150,
                        items: classifyList
                    } ),
                    wrapNode = $$.ele( this.doc, "div", {
                        className: PREFIX + "wrap-group"
                    } );

                this.overlapButtonObject = overlapButtonObject;

                // 组合选择组件
                overlapButtonObject.mount( overlapListObject );

                overlapButtonObject.initEvent();
                overlapListObject.initEvent();

                // 合并box的内容
                kity.Utils.each( itemGroup.items, function ( itemArr, index ) {

                    var itemWrapNode = wrapNode.cloneNode( false );

                    kity.Utils.each( itemArr, function ( item ) {

                        itemWrapNode.appendChild( item );

                    } );

                    itemGroup.items[ index ] = itemWrapNode;

                } );

                this.itemPanels = itemGroup.items;

                // 切换面板处理器
                overlapListObject.setSelectHandler( function ( index, oldIndex ) {

                    _self.overlapIndex = index;

                    overlapButtonObject.setLabel( classifyList[index] );
                    overlapButtonObject.hideMount();

                    // 切换内容
                    itemGroup.items[ oldIndex ].style.display = "none";
                    itemGroup.items[ index ].style.display = "block";

                    if ( index !== oldIndex ) {
                        _self.updateSize();
                    }

                    _self.onchangeHandler( index );

                } );

                overlapContainer.appendChild( overlapButtonObject.getNode() );

                kity.Utils.each( itemGroup.items, function ( group, index ) {

                    if ( index > 0 ) {
                        group.style.display = "none";
                    }

                    overlapContainer.appendChild( group );

                } );

                overlapListObject.select( 0 );

                return [ overlapContainer ];

            },

            getCurrentItemPanel: function () {
                return this.itemPanels[ this.overlapIndex ];
            },

            // 获取group的list列表, 该类表满足box的group参数格式
            getGroupList: function () {

                var lists = [];

                kity.Utils.each( this.options.group, function ( group, index ) {

                    lists.push( group.title );

                } );

                return {
                    width: 150,
                    items: lists
                };

            },

            createGroup: function () {

                var doc = this.doc,
                    itemGroup = [],
                    result = {
                        title: [],
                        items: []
                    },
                    groupNode = null,
                    groupTitle = null,
                    itemType = BOX_TYPE.DETACHED === this.options.type ? ITEM_TYPE.BIG : ITEM_TYPE.SMALL,
                    itemContainer = null;

                groupNode = $$.ele( this.doc, "div", {
                    className: PREFIX + "box-group"
                } );

                itemContainer = groupNode.cloneNode( false );
                itemContainer.className = PREFIX + "box-group-item-container";

                kity.Utils.each( this.options.group, function ( group, i ) {

                    result.title.push( group.title || "" );
                    itemGroup = [];

                    kity.Utils.each( group.items, function ( item ) {

                        groupNode = groupNode.cloneNode( false );
                        itemContainer = itemContainer.cloneNode( false );

                        groupTitle = $$.ele( doc, "div", {
                            className: PREFIX + "box-group-title",
                            content: item.title
                        } );

                        groupNode.appendChild( groupTitle );
                        groupNode.appendChild( itemContainer );

                        kity.Utils.each( createItems( doc, item.content, itemType ), function ( boxItem ) {

                            boxItem.appendTo( itemContainer );

                        } );

                        itemGroup.push( groupNode );

                    } );

                    result.items.push( itemGroup );

                } );

                return result;

            },

            mergeElement: function () {

                var groupContainer = this.groupContainer;

                this.element.appendChild( groupContainer );

                kity.Utils.each( this.itemGroups, function ( group ) {

                    groupContainer.appendChild( group );

                } );

            },

            mountTo: function ( container ) {
                container.appendChild( this.element );
            },

            appendTo: function ( container ) {
                container.appendChild( this.element );
            }

        } ),

        BoxItem = kity.createClass( "BoxItem", {

            constructor: function ( type, doc, options ) {

                this.type = type;
                this.doc = doc;
                this.options = options;

                this.element = this.createItem();

                // 项的label是可选的
                this.labelNode = this.createLabel();
                this.contentNode = this.createContent();

                this.mergeElement();

            },

            getNode: function () {
                return this.element;
            },

            createItem: function () {

                var itemNode = $$.ele( this.doc, "div", {
                    className: PREFIX + "box-item"
                } );

                return itemNode;

            },

            createLabel: function () {

                var labelNode = null;

                if ( !( "label" in this.options ) ) {
                    return;
                }

                labelNode = $$.ele( this.doc, "div", {
                    className: PREFIX + "box-item-label",
                    content: this.options.label
                } );

                return labelNode;

            },

            getContent: function () {},

            createContent: function () {

                switch ( this.type ) {

                    case ITEM_TYPE.BIG:
                        return this.createBigContent();

                    case ITEM_TYPE.SMALL:
                        return this.createSmallContent();

                }

            },

            createBigContent: function () {

                var doc = this.doc,
                    contentNode = $$.ele( doc, "div", {
                        className: PREFIX + "box-item-content"
                    }),
                    cls = PREFIX + "box-item-val",
                    tmpContent = this.options.item,
                    tmpNode = null,
                    styleStr = getStyleByData( tmpContent );

                tmpNode = $$.ele( doc, "div", {
                    className: cls
                } );

                tmpNode.innerHTML = '<div class="'+ PREFIX +'item-image" style="'+ styleStr +'"></div>';
                // 附加属性到项的根节点上
                this.element.setAttribute( "data-value", tmpContent.val );

                contentNode.appendChild( tmpNode );

                return contentNode;

            },

            createSmallContent: function () {

                var doc = this.doc,
                    contentNode = $$.ele( doc, "div", {
                        className: PREFIX + "box-item-content"
                    }),
                    cls = PREFIX + "box-item-val",
                    tmpContent = this.options,
                    tmpNode = null;

                tmpNode = $$.ele( doc, "div", {
                    className: cls
                } );

                tmpNode.style.background = 'url( '+ tmpContent.img +' )';
                tmpNode.style.backgroundPosition = -tmpContent.pos.x + 'px ' + -tmpContent.pos.y + 'px';
                // 附加属性到项的根节点上
                this.element.setAttribute( "data-value", tmpContent.key );

                contentNode.appendChild( tmpNode );

                return contentNode;

            },

            mergeElement: function () {

                if ( this.labelNode ) {
                    this.element.appendChild( this.labelNode );
                }

                this.element.appendChild( this.contentNode );

            },

            appendTo: function ( container ) {
                container.appendChild( this.element );
            }

        } );


    function createItems ( doc, group, type ) {

        var items = [];

        kity.Utils.each( group, function ( itemVal, i ) {

            items.push( new BoxItem( type, doc, itemVal) );

        } );

        return items;

    }

    // 为重叠式box创建容器
    function createOverlapContainer ( doc ) {

        return $$.ele( doc, "div", {
            className: PREFIX + "overlap-container"
        } );

    }

    function createOverlapButton ( doc, options ) {

        return new Button( doc, {
            className: "overlap-button",
            label: "",
            fixOffset: options.fixOffset
        } );

    }

    function createOverlapList ( doc, list ) {
        return new List( doc, list );
    }

    function getRectBox ( node ) {
        return node.getBoundingClientRect();
    }

    function getStyleByData ( data ) {

        // background
        var style = 'background: url( '+ data.img +' ) no-repeat ';

        style += -data.pos.x + 'px ';
        style += -data.pos.y + 'px;';

        // width height
        style += ' width: ' + data.size.width + 'px;';
        style += ' height: ' + data.size.height + 'px;';

        return style;

    }

    return Box;

} );