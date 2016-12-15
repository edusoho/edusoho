/*!
 * 光标选区组件
 */

define( function ( require, exports, module ) {

    var kity = require( "kity" ),
        kfUtils = require( "base/utils" ),

        // 鼠标移动临界距离
        MAX_DISTANCE = 10;

    return kity.createClass( "SelectionComponent", {

        constructor: function ( parentComponent, kfEditor ) {

            this.parentComponent = parentComponent;
            this.kfEditor = kfEditor;

            this.isDrag = false;
            this.isMousedown = false;

            this.startPoint = {
                x: -1,
                y: -1
            };

            // 起始位置是占位符
            this.startGroupIsPlaceholder = false;

            this.startGroup = {};

            this.initServices();
            this.initEvent();

        },

        initServices: function () {

            this.kfEditor.registerService( "control.select.all", this, {
                selectAll: this.selectAll
            } );

        },

        initEvent: function () {

            var eventServiceObject = this.kfEditor.request( "ui.canvas.container.event" ),
                _self = this;

            /* 选区拖拽 start */
            eventServiceObject.on( "mousedown", function ( e ) {

                e.preventDefault();

                // 存在根占位符， 禁止拖动
                if ( _self.kfEditor.requestService( "syntax.has.root.placeholder" ) ) {
                    return false;
                }

                _self.isMousedown = true;
                _self.updateStartPoint( e.clientX, e.clientY );
                _self.updateStartGroup();

            } );

            eventServiceObject.on( "mouseup", function ( e ) {

                e.preventDefault();

                _self.stopUpdateSelection();

            } );

            eventServiceObject.on( "mousemove", function ( e ) {

                e.preventDefault();

                if ( !_self.isDrag ) {

                    if ( _self.isMousedown ) {

                        // 移动的距离达到临界条件
                        if ( MAX_DISTANCE < _self.getDistance( e.clientX, e.clientY ) ) {
                            _self.kfEditor.requestService( "control.cursor.hide" );
                            _self.startUpdateSelection();
                        }

                    }

                } else {

                    if ( e.which !== 1 ) {
                        _self.stopUpdateSelection();
                        return;
                    }

                    _self.updateSelection( e.target, e.clientX, e.clientY );

                }

            } );
            /* 选区拖拽 end */

            /* 双击选区 start */
            eventServiceObject.on( "dblclick", function ( e ) {

                _self.updateSelectionByTarget( e.target );

            } );
            /* 双击选区 end */

        },

        getDistance: function ( x, y ) {

            var distanceX = Math.abs( x - this.startPoint.x ),
                distanceY = Math.abs( y - this.startPoint.y );

            return Math.max( distanceX, distanceY );

        },

        updateStartPoint: function ( x, y ) {
            this.startPoint.x = x;
            this.startPoint.y = y;
        },

        updateStartGroup: function () {

            var cursorInfo = this.kfEditor.requestService( "syntax.get.record.cursor" );

            this.startGroupIsPlaceholder = this.kfEditor.requestService( "syntax.is.select.placeholder" );

            this.startGroup = {
                groupInfo: this.kfEditor.requestService( "syntax.get.group.content", cursorInfo.groupId ),
                offset: cursorInfo.startOffset
            };

        },

        startUpdateSelection: function () {
            this.isDrag = true;
            this.isMousedown = false;
            this.clearSelection();
        },

        stopUpdateSelection: function () {

            this.isDrag = false;
            this.isMousedown = false;

            this.kfEditor.requestService( "control.update.input" );

        },

        clearSelection: function () {

            this.kfEditor.requestService( "render.clear.select" );

        },

        updateSelection: function ( target, x, y ) {

            // 移动方向， true为右， false为左
            var dir = x > this.startPoint.x,
                cursorInfo = {},
                communityGroupInfo = null,
                inRightArea = false,
                startGroupInfo = this.startGroup,
                currentGroupNode = null,
                currentGroupInfo = this.getGroupInof( x, target );


            if ( currentGroupInfo.groupInfo.id === startGroupInfo.groupInfo.id ) {

                cursorInfo = {
                    groupId: currentGroupInfo.groupInfo.id,
                    startOffset: startGroupInfo.offset,
                    endOffset: currentGroupInfo.offset
                };

                // 如果起始点是占位符， 要根据移动方向修正偏移
                if ( this.startGroupIsPlaceholder ) {

                    // 左移修正
                    if ( !dir ) {
                        cursorInfo.startOffset += 1;
                    // 右移修正
                    } else if ( cursorInfo.startOffset === cursorInfo.endOffset ) {
                        cursorInfo.endOffset += 1;
                    }

                }

            } else {

                // 存在包含关系
                if ( kfUtils.contains( startGroupInfo.groupInfo.groupObj, currentGroupInfo.groupInfo.groupObj ) ) {

                    cursorInfo = {
                        groupId: startGroupInfo.groupInfo.id,
                        startOffset: startGroupInfo.offset,
                        endOffset: this.getIndex( startGroupInfo.groupInfo.groupObj, target, x )
                    };

                } else if ( kfUtils.contains( currentGroupInfo.groupInfo.groupObj, startGroupInfo.groupInfo.groupObj ) ) {

                    cursorInfo = {
                        groupId: currentGroupInfo.groupInfo.id,
                        startOffset: this.kfEditor.requestService( "position.get.index", currentGroupInfo.groupInfo.groupObj, startGroupInfo.groupInfo.groupObj ),
                        endOffset: currentGroupInfo.offset
                    };

                    // 向左移动要修正开始偏移
                    if ( !dir ) {
                        cursorInfo.startOffset += 1;
                    }

                // 都不存在包含关系
                } else {

                    // 获取公共容器
                    communityGroupInfo = this.getCommunityGroup( startGroupInfo.groupInfo, currentGroupInfo.groupInfo );

                    // 修正偏移相同时的情况， 比如在分数中选中时
                    if ( communityGroupInfo.startOffset === communityGroupInfo.endOffset ) {

                        communityGroupInfo.endOffset += 1;

                    // 根据拖拽方向修正各自的偏移
                    } else {

                        // 当前光标移动所在的组元素节点
                        currentGroupNode = communityGroupInfo.group.content[ communityGroupInfo.endOffset ];

                        inRightArea = this.kfEditor.requestService( "position.get.area", currentGroupNode, x );

                        // 当前移动到右区域， 则更新结束偏移
                        if ( inRightArea ) {
                            communityGroupInfo.endOffset += 1;
                        }

                        // 左移动时， 修正起始偏移
                        if ( !dir ) {
                            communityGroupInfo.startOffset += 1;
                        }

                    }

                    cursorInfo = {
                        groupId: communityGroupInfo.group.id,
                        startOffset: communityGroupInfo.startOffset,
                        endOffset: communityGroupInfo.endOffset
                    };

                }

            }

            // 更新光标信息
            this.kfEditor.requestService( "syntax.update.record.cursor", cursorInfo.groupId, cursorInfo.startOffset, cursorInfo.endOffset );

            // 仅重新选中就可以，不用更新输入框内容
            this.kfEditor.requestService( "control.reselect" );

        },

        updateSelectionByTarget: function ( target ) {

            var parentGroupInfo = this.kfEditor.requestService( "position.get.parent.group", target ),
                containerInfo = null,
                cursorInfo = {};

            if ( parentGroupInfo === null ) {
                return;
            }

            // 如果是根节点， 则直接选中其内容
            if ( this.kfEditor.requestService( "syntax.is.root.node", parentGroupInfo.id ) ) {

                this.selectAll();
                return;

            // 否则，仅选中该组
            } else {

                // 当前组可以是容器， 则选中该容器的内容
                if ( !this.kfEditor.requestService( "syntax.is.virtual.node", parentGroupInfo.id ) ) {

                    cursorInfo = {
                        groupId: parentGroupInfo.id,
                        startOffset: 0,
                        endOffset: parentGroupInfo.content.length
                    };

                // 否则 直接选中该组的所有内容
                } else {

                    // 获取包含父组的容器
                    containerInfo = this.kfEditor.requestService( "position.get.group.info", parentGroupInfo.groupObj );

                    cursorInfo = {
                        groupId: containerInfo.group.id,
                        startOffset: containerInfo.index,
                        endOffset: containerInfo.index + 1
                    };

                }

            }

            this.kfEditor.requestService( "syntax.update.record.cursor", cursorInfo );
            this.kfEditor.requestService( "control.reselect" );
            this.kfEditor.requestService( "control.update.input" );

        },

        selectAll: function () {

            var rootGroupInfo = this.kfEditor.requestService( "syntax.get.root.group.info" );

            var cursorInfo= {
                groupId: rootGroupInfo.id,
                startOffset: 0,
                endOffset: rootGroupInfo.content.length
            };

            this.kfEditor.requestService( "syntax.update.record.cursor", cursorInfo );
            this.kfEditor.requestService( "control.reselect" );
            this.kfEditor.requestService( "control.update.input" );

        },

        getGroupInof: function ( offset, target ) {

            var groupInfo = this.kfEditor.requestService( "position.get.group", target );

            if ( groupInfo === null ) {

                groupInfo = this.kfEditor.requestService( "syntax.get.root.group.info" );

            }

            var index = this.kfEditor.requestService( "position.get.location.info", offset, groupInfo );

            return {
                groupInfo: groupInfo,
                offset: index
            };

        },

        getIndex: function ( groupNode, targetNode, offset ) {

            var index = this.kfEditor.requestService( "position.get.index", groupNode, targetNode ),
                groupInfo = this.kfEditor.requestService( "syntax.get.group.content", groupNode.id ),
                targetWrapNode = groupInfo.content[ index ],
                targetRect = kfUtils.getRect( targetWrapNode );

            if ( ( targetRect.left + targetRect.width / 2 ) < offset ) {
                index += 1;
            }

            return index;

        },

        /**
         * 根据给定的两个组信息， 获取其所在的公共容器及其各自的偏移
         * @param startGroupInfo 组信息
         * @param endGroupInfo 另一个组信息
         */
        getCommunityGroup: function ( startGroupInfo, endGroupInfo ) {

            var bigBoundingGroup = null,
                targetGroup = startGroupInfo.groupObj,
                groupNode = null;

            while ( bigBoundingGroup = this.kfEditor.requestService( "position.get.group.info", targetGroup ) ) {

                targetGroup = bigBoundingGroup.group.groupObj;

                if ( kfUtils.contains( bigBoundingGroup.group.groupObj, endGroupInfo.groupObj ) ) {
                    break;
                }

            }

            groupNode = bigBoundingGroup.group.groupObj;

            return {
                group: bigBoundingGroup.group,
                startOffset: bigBoundingGroup.index,
                endOffset: this.kfEditor.requestService( "position.get.index", groupNode, endGroupInfo.groupObj )
            };

        }

    } );

} );