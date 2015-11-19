/*!
 * 定位模块
 */


define( function ( require ) {

    var kity = require( "kity" ),

        kfUtils = require( "base/utils" ),

        PositionComponenet = kity.createClass( 'PositionComponenet', {

            constructor: function ( kfEditor ) {

                this.kfEditor = kfEditor;

                this.initServices();

            },

            initServices: function () {

                this.kfEditor.registerService( "position.get.group", this, {
                    getGroupByTarget: this.getGroupByTarget
                } );

                this.kfEditor.registerService( "position.get.index", this, {
                    getIndexByTargetInGroup: this.getIndexByTargetInGroup
                } );

                this.kfEditor.registerService( "position.get.location.info", this, {
                    getLocationInfo: this.getLocationInfo
                } );

                this.kfEditor.registerService( "position.get.parent.group", this, {
                    getParentGroupByTarget: this.getParentGroupByTarget
                } );

                this.kfEditor.registerService( "position.get.wrap", this, {
                    getWrap: this.getWrap
                } );

                this.kfEditor.registerService( "position.get.area", this, {
                    getAreaByCursorInGroup: this.getAreaByCursorInGroup
                } );

                this.kfEditor.registerService( "position.get.group.info", this, {
                    getGroupInfoByNode: this.getGroupInfoByNode
                } );

                this.kfEditor.registerService( "position.get.parent.info", this, {
                    getParentInfoByNode: this.getParentInfoByNode
                } );

            },

            getGroupByTarget: function ( target ) {

                var groupDom = getGroup( target, false, false );

                if ( groupDom ) {
                    return this.kfEditor.requestService( "syntax.get.group.content", groupDom.id );
                }

                return null;

            },

            /**
             * 根据给定的组节点和目标节点， 获取目标节点在组节点内部的索引
             * @param groupNode 组节点
             * @param targetNode 目标节点
             */
            getIndexByTargetInGroup: function ( groupNode, targetNode ) {

                var groupInfo = this.kfEditor.requestService( "syntax.get.group.content", groupNode.id ),
                    index = -1;

                kity.Utils.each( groupInfo.content, function ( child, i ) {

                    index = i;

                    if ( kfUtils.contains( child, targetNode ) ) {
                        return false;
                    }

                } );

                return index;

            },

            /**
             * 根据给定的组节点和给定的偏移值，获取当前偏移值在组中的区域值。
             * 该区域值的取值为true时， 表示在右区域， 反之则在左区域
             * @param groupNode 组节点
             * @param offset 偏移值
             */
            getAreaByCursorInGroup: function ( groupNode, offset ) {

                var groupRect = kfUtils.getRect( groupNode );

                return groupRect.left + groupRect.width / 2 < offset;

            },

            getLocationInfo: function ( distance, groupInfo ) {

                var index = -1,
                    children = groupInfo.content,
                    boundingRect = null;

                for ( var i = children.length - 1, child = null; i >= 0; i-- ) {

                    index = i;

                    child = children[ i ];

                    boundingRect = kfUtils.getRect( child );

                    if ( boundingRect.left < distance ) {

                        if ( boundingRect.left + boundingRect.width / 2 < distance ) {
                            index += 1;
                        }

                        break;

                    }

                }

                return index;

            },

            getParentGroupByTarget: function ( target ) {

                var groupDom = getGroup( target, true, false );

                if ( groupDom ) {
                    return this.kfEditor.requestService( "syntax.get.group.content", groupDom.id );
                }

                return null;

            },

            getWrap: function ( node ) {

                return  getGroup( node, true, true );

            },

            /**
             * 给定一个节点， 获取其节点所属的组及其在该组内的偏移
             * @param target 目标节点
             */
            getGroupInfoByNode: function ( target ) {

                var result = {},
                    containerNode = getGroup( target, false, false ),
                    containerInfo = null;

                if ( !containerNode ) {
                    return null;
                }

                containerInfo = this.kfEditor.requestService( "syntax.get.group.content", containerNode.id );

                for ( var i = 0, len = containerInfo.content.length; i < len; i++) {

                    result.index = i;

                    if ( kfUtils.contains( containerInfo.content[ i ], target ) ) {
                        break;
                    }

                }

                result.group = containerInfo;

                return result;

            },

            /**
             * 给定一个节点， 获取其节点所属的直接包含组及其在该直接包含组内的偏移
             * @param target 目标节点
             */
            getParentInfoByNode: function ( target ) {

                var group = getGroup( target, true, false );

                group = this.kfEditor.requestService( "syntax.get.group.content", group.id );

                return {
                    group: group,
                    index: group.content.indexOf( target )
                };

            }

        } );

    /**
     * 获取给定节点元素所属的组
     * @param node 当前点击的节点
     * @param isAllowVirtual 是否允许选择虚拟组
     * @param isAllowWrap 是否允许选择目标节点的最小包裹单位
     * @returns {*}
     */
    function getGroup ( node, isAllowVirtual, isAllowWrap ) {

        var tagName = null;

        if ( !node.ownerSVGElement ) {
            return null;
        }

        node = node.parentNode;

        tagName = node.tagName.toLowerCase();

        if ( node && tagName !== "body" && tagName !== "svg" ) {

            if ( node.getAttribute( "data-type" ) === "kf-editor-group" ) {
                return node;
            }

            if ( isAllowVirtual && node.getAttribute( "data-type" ) === "kf-editor-virtual-group" ) {
                return node;
            }

            if ( isAllowWrap && node.getAttribute( "data-flag" ) !== null ) {
                return node;
            }

            return getGroup( node, isAllowVirtual, isAllowWrap );

        } else {
            return null;
        }

    }

    return PositionComponenet;

} );