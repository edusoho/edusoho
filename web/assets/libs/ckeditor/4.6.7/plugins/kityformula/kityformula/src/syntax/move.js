/*！
 * 光标移动控制
 */

define( function ( require, exports, module ) {

    var kity = require( "kity" ),
        DIRECTION = {
            LEFT: 'left',
            RIGHT: 'right'
        };

    return kity.createClass( "MoveComponent", {

        constructor: function ( parentComponent, kfEditor ) {

            this.parentComponent = parentComponent;
            this.kfEditor = kfEditor;

        },

        leftMove: function () {

            var cursorInfo = this.parentComponent.getCursorRecord();

            cursorInfo = updateCursorGoLeft.call( this, cursorInfo );

            // cursorInfo 为null则不用处理
            if ( cursorInfo ) {
                this.parentComponent.updateCursor( cursorInfo );
            }

        },

        rightMove: function () {

            var cursorInfo = this.parentComponent.getCursorRecord();

            cursorInfo = updateCursorGoRight.call( this, cursorInfo );

            // cursorInfo 为null则不用处理
            if ( cursorInfo ) {
                this.parentComponent.updateCursor( cursorInfo );
            }

        }

    } );


    function updateCursorGoLeft ( cursorInfo ) {

        var prevGroupNode = null,
            syntaxComponent = this.parentComponent,
            containerInfo = null;

        containerInfo = syntaxComponent.getGroupContent( cursorInfo.groupId );

        // 当前处于占位符中
        if ( syntaxComponent.isSelectPlaceholder() ) {
            return locateOuterIndex( this, containerInfo.content[ cursorInfo.startOffset ], DIRECTION.LEFT );
        }

        if ( cursorInfo.startOffset === cursorInfo.endOffset ) {

            if ( cursorInfo.startOffset > 0 ) {

                prevGroupNode = containerInfo.content[ cursorInfo.startOffset - 1 ];

                if ( isGroupNode( prevGroupNode ) ) {
                    cursorInfo = locateIndex( this, prevGroupNode, DIRECTION.LEFT );
                } else {

                    cursorInfo.startOffset -= 1;

                    // 非占位符处理
                    if ( !isPlaceholderNode( prevGroupNode ) ) {
                        cursorInfo.endOffset = cursorInfo.startOffset;
                    }

                }

            // 跳出当前容器， 回溯
            } else {

                cursorInfo = locateOuterIndex( this, containerInfo.groupObj, DIRECTION.LEFT );

            }

        } else {

            cursorInfo.startOffset = Math.min( cursorInfo.startOffset, cursorInfo.endOffset );
            // 收缩
            cursorInfo.endOffset = cursorInfo.startOffset;

        }

        return cursorInfo;

    }

    function updateCursorGoRight ( cursorInfo ) {

        var nextGroupNode = null,
            syntaxComponent = this.parentComponent,
            containerInfo = null;

        containerInfo = syntaxComponent.getGroupContent( cursorInfo.groupId );

        // 当前处于占位符中
        if ( syntaxComponent.isSelectPlaceholder() ) {
            return locateOuterIndex( this, containerInfo.content[ cursorInfo.startOffset ], DIRECTION.RIGHT );
        }

        if ( cursorInfo.startOffset === cursorInfo.endOffset ) {

            if ( cursorInfo.startOffset < containerInfo.content.length ) {

                nextGroupNode = containerInfo.content[ cursorInfo.startOffset ];

                // 进入容器内部
                if ( isGroupNode( nextGroupNode ) ) {
                    cursorInfo = locateIndex( this, nextGroupNode, DIRECTION.RIGHT );
                } else {

                    cursorInfo.startOffset += 1;

                    // 非占位符同时更新结束偏移
                    if ( !isPlaceholderNode( nextGroupNode ) ) {
                        cursorInfo.endOffset = cursorInfo.startOffset;
                    }

                }

            // 跳出当前容器， 回溯
            } else {

                cursorInfo = locateOuterIndex( this, containerInfo.groupObj, DIRECTION.RIGHT );

            }

        } else {

            cursorInfo.endOffset = Math.max( cursorInfo.startOffset, cursorInfo.endOffset );
            // 收缩
            cursorInfo.startOffset = cursorInfo.endOffset;

        }

        return cursorInfo;

    }

    /**
     * 组内寻址, 入组
     */
    function locateIndex ( moveComponent, groupNode, dir ) {

        switch ( dir ) {

            case DIRECTION.LEFT:
                return locateLeftIndex( moveComponent, groupNode );

            case DIRECTION.RIGHT:
                return locateRightIndex( moveComponent, groupNode );

        }

        throw new Error( "undefined move direction!" );

    }

    /**
     * 组外寻址, 出组
     */
    function locateOuterIndex ( moveComponent, groupNode, dir ) {

        switch ( dir ) {

            case DIRECTION.LEFT:
                return locateOuterLeftIndex( moveComponent, groupNode );

            case DIRECTION.RIGHT:
                return locateOuterRightIndex( moveComponent, groupNode );

        }

        throw new Error( "undefined move direction!" );

    }

    // 左移内部定位
    function locateLeftIndex ( moveComponent, groupNode ) {

        var syntaxComponent = moveComponent.parentComponent,
            groupInfo = null,
            groupElement = null;

        if ( isPlaceholderNode( groupNode ) || isEmptyNode( groupNode ) ) {
            return locateOuterLeftIndex( moveComponent, groupNode );
        }

        if ( isGroupNode( groupNode ) ) {

            groupInfo = syntaxComponent.getGroupContent( groupNode.id );
            // 容器内部中末尾的元素
            groupElement = groupInfo.content[ groupInfo.content.length - 1 ];

            // 空检测
            if ( isEmptyNode( groupElement ) ) {

                // 做跳出处理
                return locateOuterLeftIndex( moveComponent, groupElement );

            }

            // 待定位的组本身就是一个容器, 则检测其内部结构是否还包含容器
            if ( isContainerNode( groupNode ) ) {

                // 进入到占位符包裹容器内
                if ( isPlaceholderNode( groupElement ) ) {

                    return {
                        groupId: groupNode.id,
                        startOffset: groupInfo.content.length - 1,
                        endOffset: groupInfo.content.length
                    };

                // 内部元素仍然是一个容器并且只有这一个内部元素，则进行递归处理
                } else if ( isContainerNode( groupElement ) && groupInfo.content.length === 1 ) {
                    return locateLeftIndex( moveComponent, groupElement );
                }

                return {
                    groupId: groupNode.id,
                    startOffset: groupInfo.content.length,
                    endOffset: groupInfo.content.length
                };

            // 仅是一个组， 进入组内部处理, 找到目标容器
            } else {

                while ( !isContainerNode( groupElement ) && !isEmptyNode( groupElement ) && !isPlaceholderNode( groupElement ) ) {
                    groupInfo = syntaxComponent.getGroupContent( groupElement.id );
                    groupElement = groupInfo.content[ groupInfo.content.length - 1 ];
                }

                if ( isEmptyNode( groupElement ) ) {
                    return locateOuterLeftIndex( moveComponent, groupElement );
                }

                if ( isPlaceholderNode( groupElement ) ) {
                    return {
                        groupId: groupElement.id,
                        startOffset: groupInfo.content.length,
                        endOffset: groupInfo.content.length
                    };
                }

                return locateLeftIndex( moveComponent, groupElement );

            }

        }

        return null;

    }

    // 左移外部定位
    function locateOuterLeftIndex ( moveComponent, groupNode ) {

        var kfEditor = moveComponent.kfEditor,
            outerGroupInfo = null,
            groupInfo = null;

        // 根容器， 不用再跳出
        if ( isRootNode( groupNode ) ) {
            return null;
        }

        outerGroupInfo = kfEditor.requestService( "position.get.parent.info", groupNode );

        while ( outerGroupInfo.index === 0 ) {

            if ( isRootNode( outerGroupInfo.group.groupObj ) ) {
                return {
                    groupId: outerGroupInfo.group.id,
                    startOffset: 0,
                    endOffset: 0
                };
            }

            // 如果父组是一个容器， 并且该容器包含不止一个节点， 则跳到父组开头
            if ( isContainerNode( outerGroupInfo.group.groupObj ) && outerGroupInfo.group.content.length > 1 ) {
                return {
                    groupId: outerGroupInfo.group.id,
                    startOffset: 0,
                    endOffset: 0
                };
            }

            outerGroupInfo = kfEditor.requestService( "position.get.parent.info", outerGroupInfo.group.groupObj );

        }

        // 如果外部组是容器， 则直接定位即可
        if ( isContainerNode( outerGroupInfo.group.groupObj ) ) {
            return {
                groupId: outerGroupInfo.group.id,
                startOffset: outerGroupInfo.index,
                endOffset: outerGroupInfo.index
            };
        }

        groupNode = outerGroupInfo.group.content[ outerGroupInfo.index - 1 ];

        // 定位到的组是一个容器， 则定位到容器尾部
        if ( isGroupNode( groupNode ) ) {

            // 容器节点
            if ( isContainerNode( groupNode ) ) {

                // 进入容器内部
                return locateLeftIndex( moveComponent, groupNode );

            // 组节点
            } else {

                return locateLeftIndex( moveComponent, groupNode );

            }

            return {
                groupId: groupNode.id,
                startOffset: groupInfo.content.length,
                endOffset: groupInfo.content.length
            };

        }

        if ( isEmptyNode( groupNode ) ) {
            return locateOuterLeftIndex( moveComponent, groupNode );
        }

        return {
            groupId: outerGroupInfo.group.id,
            startOffset: outerGroupInfo.index,
            endOffset: outerGroupInfo.index
        };

    }

    // 右移内部定位
    function locateRightIndex ( moveComponent, groupNode ) {

        var syntaxComponent = moveComponent.parentComponent,
            groupInfo = null,
            groupElement = null;

        if ( isGroupNode( groupNode ) ) {

            groupInfo = syntaxComponent.getGroupContent( groupNode.id );
            // 容器内部中末尾的元素
            groupElement = groupInfo.content[ 0 ];

            // 待定位的组本身就是一个容器, 则检测其内部结构是否还包含容器
            if ( isContainerNode( groupNode ) ) {

                // 内部元素仍然是一个容器
                if ( isContainerNode( groupElement ) ) {
                    // 递归处理
                    return locateRightIndex( moveComponent, groupElement );
                }

                if ( isPlaceholderNode( groupElement ) ) {
                    return {
                        groupId: groupNode.id,
                        startOffset: 0,
                        endOffset: 1
                    };
                }

                return {
                    groupId: groupNode.id,
                    startOffset: 0,
                    endOffset: 0
                };

                // 仅是一个组， 进入组内部处理, 找到目标容器
            } else {

                while ( !isContainerNode( groupElement ) && !isPlaceholderNode( groupElement ) && !isEmptyNode( groupElement ) ) {
                    groupInfo = syntaxComponent.getGroupContent( groupElement.id );
                    groupElement = groupInfo.content[ 0 ];
                }

                // 定位到占位符内部
                if ( isPlaceholderNode( groupElement ) ) {
                    return {
                        groupId: groupElement.id,
                        startOffset: 0,
                        endOffset: 0
                    };
                } else if ( isEmptyNode( groupElement ) ) {
                    return locateOuterRightIndex( moveComponent, groupElement );
                } else {
                    return locateRightIndex( moveComponent, groupElement );
                }

            }

        }

        return null;

    }

    // 右移外部定位
    function locateOuterRightIndex ( moveComponent, groupNode ) {

        var kfEditor = moveComponent.kfEditor,
            syntaxComponent = moveComponent.parentComponent,
            outerGroupInfo = null,
            groupInfo = null;

        // 根容器， 不用再跳出
        if ( isRootNode( groupNode ) ) {
            return null;
        }

        outerGroupInfo = kfEditor.requestService( "position.get.parent.info", groupNode );

        // 仍然需要回溯
        while ( outerGroupInfo.index === outerGroupInfo.group.content.length - 1 ) {

            if ( isRootNode( outerGroupInfo.group.groupObj ) ) {
                return {
                    groupId: outerGroupInfo.group.id,
                    startOffset: outerGroupInfo.group.content.length,
                    endOffset: outerGroupInfo.group.content.length
                };
            }

            // 如果父组是一个容器， 并且该容器包含不止一个节点， 则跳到父组末尾
            if ( isContainerNode( outerGroupInfo.group.groupObj ) && outerGroupInfo.group.content.length > 1 ) {
                return {
                    groupId: outerGroupInfo.group.id,
                    startOffset: outerGroupInfo.group.content.length,
                    endOffset: outerGroupInfo.group.content.length
                };
            }

            outerGroupInfo = kfEditor.requestService( "position.get.parent.info", outerGroupInfo.group.groupObj );

        }

        groupNode = outerGroupInfo.group.content[ outerGroupInfo.index + 1 ];

        // 空节点处理
        if ( isEmptyNode( groupNode ) ) {
            return locateOuterRightIndex( moveComponent, groupNode );
        }

        // 定位到的组是一个容器， 则定位到容器内部开头位置上
        if ( isContainerNode( groupNode ) ) {

            groupInfo = syntaxComponent.getGroupContent( groupNode.id );

            // 检查内容开始元素是否是占位符
            if ( syntaxComponent.isPlaceholder( groupInfo.content[ 0 ].id ) ) {

                return {
                    groupId: groupNode.id,
                    startOffset: 0,
                    endOffset: 1
                };

            }

            return {
                groupId: groupNode.id,
                startOffset: 0,
                endOffset: 0
            };

        }

        return {
            groupId: outerGroupInfo.group.id,
            startOffset: outerGroupInfo.index + 1,
            endOffset: outerGroupInfo.index + 1
        };

    }

    function isRootNode ( node ) {

        return !!node.getAttribute( "data-root" );

    }

    function isContainerNode ( node ) {
        return node.getAttribute( "data-type" ) === "kf-editor-group";
    }

    function isGroupNode ( node ) {
        var dataType = node.getAttribute( "data-type" );
        return dataType === "kf-editor-group" || dataType === "kf-editor-virtual-group";
    }

    function isPlaceholderNode ( node ) {
        return node.getAttribute( "data-flag" ) === "Placeholder";
    }

    function isEmptyNode ( node ) {
        return node.getAttribute( "data-flag" ) === "Empty";
    }

} );