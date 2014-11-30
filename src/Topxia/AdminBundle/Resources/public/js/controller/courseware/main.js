define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');
    var TagChooser = require("tag-chooser");
          require("widget");
    var Overlay = require("overlay");
          require("autocomplete");

    exports.run = function() {
        $('.method-form-group').on('change',function(){
            if ($('.title-form-group').hasClass('hide')){
                $('.tagIds-form-group').addClass('hide');
                $('.title-form-group').removeClass('hide');
            } else {
                $('.tagIds-form-group').removeClass('hide');
                $('.title-form-group').addClass('hide');
            }
        });

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

        var overlay = new Overlay({
              element: $('.tagchooser-overlay'),
              width: 400,
              align: {
                baseElement: $('.tag-search-group'),
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
            element: '#tagchooser-example-3',
            sourceUrl: 'data-source-tags.html',
            queryUrl: 'data-choosed-tags.json',
            matchUrl: 'data-match-tags.json?q={{query}}',
            maxTagNum: 4,
            choosedTags: [1, 2, 3],
            alwaysShow: true
        });

            chooser.on('change', function(tags) {
                overlay.set('height', this.getHeight() + 70);
            });

            chooser.on('existed', function(existTag){
                console.log('existed');
            });
        });

        $('.tag-search-confrim').click(function() {
            overlay.hide();
            var tags = chooser.get('choosedTags');
            var tagNames = [];
            $.each(tags, function(i, tag) {
            tagNames.push(tag.name);
            });
            var btnText = tagNames.length >0 ? tagNames.join(',') : '全选';
            $('.tag-search-trigger').text(btnText);
        });

        $('.tag-search-cancel').click(function(){
            overlay.hide();
        });

    };
});