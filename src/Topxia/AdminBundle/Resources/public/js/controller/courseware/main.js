define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var Overlay = require("arale-overlay");
    var TagTreeChooser = require('tag-tree-chooser');
    var TagChooser = require('tag-chooser');

    exports.run = function() {
        
        _initTagSearch();
        _initKnowledgeSearch();
        
        $('.delete-courseware-btn').click(function(){

            if (!confirm('您真的要删除该课件吗？')) {
                return ;
            }

            var $btn = $(this);
            $.post($btn.data('url'),function(){
                Notify.success('删除成功！');
                window.location.reload();
            }).error(function(){
                Notify.danger('删除失败！');
            });
        });

        $('[data-role=batch-select]').click(function(){
            if ($(this).is(":checked") == true){
                $('[data-role=single-select]').prop('checked', true);
            } else {
               $('[data-role=single-select]').prop('checked', false);
            }
        });

        $('[data-role=batch-delete]').click(function(){

            var ids = [];
            $('[data-role=single-select]:checked').each(function(index,item) {
                ids.push($(item).data('coursewareId'));
            });

            if (ids.length == 0) {
                Notify.danger('未选中任何课件');
                return ;
            }

            if (!confirm('您真的要删除选中的课件吗？')) {
                return ;
            }
            var $btn = $(this);
            $.post($btn.data('url'),{ids:ids},function(){
                Notify.success('删除成功！');
                window.location.reload();
            }).error(function(){
                Notify.danger('删除失败！');
            });
        });

        $('#courseware-item-list').on('hover','.update-courseware-btn,.preview-courseware-btn,.delete-courseware-btn',function(){
            $("#example").tooltip();
        });

        $('#courseware-thumb-item-list').on('mouseenter','.item-courseware',function(){
            $(this).find('.courseware-thumb-title').addClass('hide');
            $(this).find('.courseware-thumb-btn').removeClass('hide');
        }).on('mouseleave','.item-courseware',function(){
            $(this).find('.courseware-thumb-title').removeClass('hide');
            $(this).find('.courseware-thumb-btn').addClass('hide');
        });

    };

    var TagChooserOverlay = Overlay.extend({

        _chooser: null,

        events: {
            'click .tag-search-confrim': '_onClickConfirm',
            'click .tag-search-cancel': '_onClickCancel'
        },

        show: function() {
            var overlay = this;
            TagChooserOverlay.superclass.setup.call(this);
            if (this._chooser) {
                return ;
            }
            chooser = new TagTreeChooser({
                element: '#knowledges-search',
                sourceUrl: $('#knowledges-search').data('sourceUrl'),
                queryUrl: $('#knowledges-search').data('queryUrl'),
                matchUrl: $('#knowledges-search').data('matchUrl'),
                maxTagNum: 4,
                // choosedTags: $("#testpaper-search-form").find('input[name=knowledgeIds]').val().split(','),
                alwaysShow: true
            });

            chooser.on('change', function(tags) {
                overlay.set('height', this.getHeight() + 70);
            });
        },

        _onClickConfirm: function() {
            this.hide();
            var tags = this._chooser.get('choosedTags');
            var tagNames = [];
            var tagIds = [];
            $.each(tags, function(i, tag) {
                tagNames.push(tag.name);
                tagIds.push(tag.id);
            });
            var btnText = tagNames.length >0 ? tagNames.join(' ') : '全选';
            $('.knowledge-search-trigger').text(btnText);
            $("#testpaper-search-form").find('input[name=knowledgeIds]').val(tagIds.join(','));
        },

        _onClickCancel: function() {
            this.hide();
        }


    });

    var tagOverlay = new TagChooserOverlay({
        trigger: '.knowledge-search-trigger',
        element: $('#knowledges-search-overlay'),
        width: 400,
        align: {
            baseElement: $('#knowledges-search-group'),
            baseXY: [0, 36]
        }
    });

    function _initTagSearch() {
        var overlay = new Overlay({
          element: $('#tags-search-overlay'),
          width: 400,
          align: {
            baseElement: $('#tags-search-group'),
            baseXY: [0, 36]
          }
        });

        var chooser;

        $('.tag-search-trigger').click(function() {
          overlay.show();

          if (chooser) {
            return ;
          }

          chooser = new TagChooser({
            element: '#tags-search',
                sourceUrl: $('#tags-search').data('sourceUrl'),
                queryUrl: $('#tags-search').data('queryUrl'),
                matchUrl: $('#tags-search').data('matchUrl'),
            maxTagNum: 4,
            // choosedTags: $("#testpaper-search-form").find('input[name=tagIds]').val().split(','),
            alwaysShow: true
          });

          chooser.on('change', function(tags) {
            overlay.set('height', this.getHeight() + 70);
          });

          overlay.set('height', chooser.getHeight() + 70);

        });

        overlay.$('.tag-search-confrim').click(function() {
          overlay.hide();
          var tags = chooser.get('choosedTags');
          var tagNames = [];
          var tagIds = [];
          $.each(tags, function(i, tag) {
            tagNames.push(tag.name);
            tagIds.push(tag.id);
          });
          var btnText = tagNames.length >0 ? tagNames.join(' ') : '全选';
          $('.tag-search-trigger').text(btnText);
          $("#testpaper-search-form").find('input[name=tagIds]').val(tagIds.join(','));
        });

        overlay.$('.tag-search-cancel').click(function(){
          overlay.hide();
        });
    }
});