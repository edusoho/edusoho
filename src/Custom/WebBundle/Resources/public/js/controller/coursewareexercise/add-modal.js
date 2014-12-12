define(function(require, exports, module) {
    var Notify = require('common/bootstrap-notify');

    var TagChooserOverlay = require('tag-chooser-overlay');
    exports.run = function() {

        $('.search-btn').on('click', function() {
            $(this).button('search').addClass('disabled');
            $.get($(this).data('url'), $('#search-form').serialize(), function(html){
                $('.modal-content').html($(html).find('.modal-content').html());
                $('.search-btn').removeClass('disabled').button('reset');
           });

        });

        $('.question-table').on('click', '.btn-add-exercise', function(){
            $.post($(this).data('url'), function(html){
                var $modal = $('.question-table').parents('.modal');
                $modal.modal('hide');
                Notify.success('添加题目成功');
                $('.execise-questions-table').html(html);
            }).error(function(){
                Notify.danger('添加题目失败');
            });;
        });

        var tagOverlay = new TagChooserOverlay({
            trigger: '.tag-search-trigger',
            element: $('#tags-search-overlay'),
            width: 400,
            align: {
                baseElement: $('#tags-search-group'),
                baseXY: [0, 36]
            },
            choosedTags: $("#search-form").find('input[name=tagIds]').val().split(',')
        });

        tagOverlay.on('change', function(tags, tagIds) {
            $("#search-form").find('input[name=tagIds]').val(tagIds.join(','));
        });

        $('#search-form').on("keyup keypress", function(e) {
          var code = e.keyCode || e.which; 
          if (code  == 13) {               
            $('.search-btn').click();
            e.preventDefault();
            return false;
          }
        });
    };

});