define(function(require, exports, module) {

    var Widget = require('widget');
    var Overlay = require('overlay');
    var AutoComplete = require('autocomplete');
    var Notify = require('common/bootstrap-notify');
    var Ztree = require('ztree');
    require('ztree-css');

    var TagChooser = Widget.extend({
        attrs: {
            multi: true,
            type:'knowledge',
            sourceUrl:'',
            choosedTags: []
        },

        _tagOverlay: null,
        _addDiyDom:null,
        _zTreeOnNodeCreated:null,
        _init:null,
        _initItemsCheck:null,

        events: {
            'click .dropdown' : 'onDropdown',
            'click .tag-cancel': 'onTagCancel',
            'click .tag-confirm': 'onTagConfirm',
            'click .tag-item': 'onTagItem',
            'click .tag-node': 'onTagNode',
            'click .tag-remove': 'onTagRemove'
        },

        setup: function() {

            this._init();
            var $tree = $('#knowledge-tree');
            var overlayY = this.$('.input-group').height();
            var overlayWidth = this.$('.input-group').width();

            var overlay = new Overlay({
                element: this.$('.tag-overlay'),
                width: overlayWidth,
                height: 300,
                align: {
                    baseElement: this.$('.input-group'),
                    baseXY: [0, overlayY]
                }

            });

            overlay._blurHide([overlay.element, this.$('.dropdown')]);

            this._tagOverlay = overlay;

            var autocomplete = new AutoComplete({
                trigger: '#tags-input',
                multi: this.get('multi'),
                dataSource: $('#tags-input').data('url'),
                filter: {
                    name: 'stringMatch',
                    options: {
                        key: 'id',
                        key: 'name'
                    }
                },
                selectFirst: true
            }).render();

            autocomplete.on('itemSelect', function(data){

                $('#tags-input').val('');

                var $tags = $('.tags-choosed');
                var error = "";
                var $tagTemplate = $('.choosed-tag-template');
                var $tag = $tagTemplate.clone().removeClass('choosed-tag-template');
                var choosedTags = [];

                $tag.find('.tag-name-placeholder').html(data.name);

                $tags.find('.tag-name-placeholder').each(function(index,item){
                    if ($(item).text() == data.name) {
                        error = "已添加，不能重复添加!";
                    };
                });
                if (error) {
                    Notify.danger(error);
                } else {
                    $tag.find('.tag-name-placeholder').attr("data-id",data.id);
                    $tag.find('.tag-name-placeholder').attr("data-name",data.name);
                    console.log(this.get('multi'))
                    if (this.get('multi') == false) {
                        $tags.html($tag);
                    } else {
                        $tags.append($tag);
                    }
                }
            });

            var setting = {
                type:this.get('type'),
                multi:this.get('multi'),
                async: {
                    enable: true,
                    url:$tree.data('url'),
                    autoParam:["id"],
                    otherParam:{"categoryId":$tree.data('cid')}
                },
                view: {
                    expandSpeed:"",
                    selectedMulti: false,
                    showLine: false,
                    showIcon: false,
                    addDiyDom: this._addDiyDom
                },
                edit: {
                    enable: true,
                    showRemoveBtn: false,
                    showRenameBtn: false
                },
                data: {
                    simpleData: {
                        enable: true,
                        idkey: "id",
                        pidKey: "pid"
                    }
                },
                callback: {
                    onNodeCreated: this._zTreeOnNodeCreated
                }
            };

            $.fn.zTree.init($("#knowledge-tree"), setting);

        },

        _init:function()
        {
            $('#tags-input').data("url",this.get('sourceUrl')+'?q={{query}}');
            var $tagOverlay = this.$('.tag-overlay');
            var $knowledgeList = $tagOverlay.find('[data-role=knowledge-list]');
            var $tagList = $tagOverlay.find('[data-role=tags-list]');
            if (this.get('type') == 'knowledge') {
                $knowledgeList.show();
                $tagList.hide();
            } else {
                $knowledgeList.hide();
                $tagList.show();
            }
        },

        _zTreeOnNodeCreated:function(event, treeId, treeNode)
        {
            var self = this;
            var tree = $.fn.zTree.getZTreeObj(treeId);
            $('.choosed-tag').each(function(index,item){
                var itemId = $(item).find('.tag-name-placeholder').data('id');
                if (tree.setting.type == "knowledge") {
                    $('.tag-overlay').find('.tag-item-' + itemId).prop('checked', true);
                } else {
                    $('.tag-overlay').find('.tag-item-' + itemId).addClass('tag-item-choosed');
                }
            });
        },

        _addDiyDom:function(treeId, treeNode) {
            tree = $.fn.zTree.getZTreeObj(treeId);
            var html = '<div class="actions ">';
            if (tree.setting.multi == true) {
                html += '<span class="btn btn-link btn-sm "    id="checkBtn_'+treeNode.tId+'"><label><input class="knowledge-checkbox tag-item-'+treeNode.id+'" data-id="'+treeNode.id+'" data-name="'+treeNode.name+'" type="checkbox"></label></span>';
                html += '</div>';
            } else {
                $('.tag-overlay').find('.panel-footer').hide();
                $('#' + treeNode.tId + '_a').addClass('tag-node');
                $('#' + treeNode.tId + '_a').attr("data-id",treeNode.id);
                $('#' + treeNode.tId + '_a').attr("data-name",treeNode.name);
                html += '</div>';
            }
            $('#' + treeNode.tId + '_a').after(html);
            $('#knowledge-tree_1_switch').click();
            $('#knowledge-tree_2_switch').click();
        },

        onDropdown: function(e) {
            if (this._tagOverlay.get('visible')) {
                this._tagOverlay.hide();
            } else {
                this._initData();
                this._tagOverlay.show();
            }
        },

        onTagCancel: function(e) {
            this._tagOverlay.hide();
        },

        onTagConfirm: function(e) {
            if (this.get('type') == 'knowledge') {
                this.$('.knowledge-list').find('.knowledge-checkbox').each(function(index,item){
                    if ($(item).is(':checked')) {
                        $(item).addClass('tag-item-choosed')
                    };
                });
            };

            var choosedTags = [];
            this.$('.tag-overlay').find('.tag-item-choosed').each(function(index, item) {
                var $item = $(item);
                choosedTags.push($item.data());
            });
            this.set('choosedTags', choosedTags);
            this.trigger('choosed', choosedTags);
            this._tagOverlay.hide();
        },

        onTagRemove: function (e) {
            $(e.currentTarget).parents('.choosed-tag').remove();

            var choosedTags = [];
            this.$('.tags-choosed').find('.choosed-tag').each(function(index, item) {
                choosedTags.push($(item).data());
            });
        },

        onTagNode: function(e) {
            var $item = $(e.currentTarget);
            var $tagsChoosed = $('.tags-choosed');
            var $tagTemplate = $('.choosed-tag-template');
            var $tag = $tagTemplate.clone().removeClass('choosed-tag-template');
            var $tagNamePlaceholder = $tag.find('.tag-name-placeholder');

            $tagNamePlaceholder.html($item.data('name'));
            $tagsChoosed.html($tag);
            $tagNamePlaceholder.attr('data-id',$item.data('id'));
            $tagNamePlaceholder.attr('data-name',$item.data('name'));

            this._tagOverlay.hide();
        },

        onTagItem: function(e) {
            var $item = $(e.currentTarget);

            if (this.get('multi')) {
                if ($item.hasClass('tag-item-choosed')) {
                    $item.removeClass('tag-item-choosed');
                } else {
                    $item.addClass('tag-item-choosed');
                }
            } else {
                this.element.find('.tag-item-choosed').removeClass('tag-item-choosed');
                $item.addClass('tag-item-choosed');
            }

        },

        _onChangeChoosedTags: function(tags) {
            var $tags = this.$('.tags-choosed').empty();

            var $tagTemplate = this.$('.choosed-tag-template');

            $.each(tags, function(index, tag) {
                var $tag = $tagTemplate.clone().removeClass('choosed-tag-template');
                $tag.data(tag);

                $tagNamePlaceholder = $tag.find('.tag-name-placeholder');
                $tagNamePlaceholder.attr("data-id",tag.id)
                $tagNamePlaceholder.attr("data-name",tag.name)
                $tagNamePlaceholder.html(tag.name);

                $tags.append($tag);
            });
        },

        _initItemsCheck:function($type)
        {
            $('.choosed-tag').each(function(index,item){
                var itemId = $(item).find('.tag-name-placeholder').data('id');
                if ($type == "knowledge") {
                    $('.tag-overlay').find('.tag-item-' + itemId).prop('checked', true);
                } else {
                    $('.tag-overlay').find('.tag-item-' + itemId).addClass('tag-item-choosed');
                }
            });
        },

        _initData: function() {
            var self = this;
            self.$('.tag-overlay').find('.tag-item-choosed').removeClass('tag-item-choosed');
            if (self.get('type') == "knowledge") {
                self.$('.tag-overlay').find('.knowledge-checkbox').attr("checked",false);
            }

            this._initItemsCheck(self.get('type'));
        }

    });

    module.exports = TagChooser;
});