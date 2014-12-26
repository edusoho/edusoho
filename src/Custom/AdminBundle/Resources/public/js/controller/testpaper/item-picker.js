define(function(require, exports, module) {

    var TagTreeChooser = require('tag-tree-chooser');
    var TagChooser = require('tag-chooser');
    var TagChooserOverlay = require('tag-chooser-overlay');
    var TagTreeChooserOverlay = require('tag-tree-chooser-overlay');

    exports.run = function() {
        var $form = $("#testpaper-item-picker-form"),
            $modal = $form.parents('.modal');
        $("#testpaper-item-picker-form").submit(function(e) {
            var $form = $(this),
                $modal = $form.parents('.modal');
            e.preventDefault();

            $.get($form.attr('action'), $form.serialize(), function(html) {
                $modal.html(html);
            });
        });

        $("#testpaper-item-picker-table").on('click', '[data-role=picked-item]', function() {
            var replace = parseInt($(this).data('replace'));
            $.get($(this).data('url'), function(html) {
                var $trs = $(html).find('tr'),
                    $firstTr = $trs.first();

                if (replace) {
                    $("#testpaper-item-" + replace).parents('tbody').find('[data-parent-id=' + replace + ']').remove();
                    $("#testpaper-item-" + replace).replaceWith($trs);
                } else {
                    var target = $firstTr.data('part') || $firstTr.data('type');
                    $("#testpaper-items-" + target).append($trs);
                }

                $modal.modal('hide');
                $modal.data('manager').refreshSeqs();
                $modal.data('manager').refreshTestpaperStats();

            });
            
        });


        $("#testpaper-item-picker-table").on('click', '.newWindowPreview', function() {
            window.open($(this).data('url'), '_blank',
                "directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0");
        });

         var knowledgeOverlay = new TagTreeChooserOverlay({
            trigger: '.knowledge-search-trigger',
            element: $('#knowledges-search-overlay'),
            width: 400,
            align: {
                baseElement: $('#knowledges-search-group'),
                baseXY: [0, 36]
            },
            choosedTags: $("#testpaper-item-picker-form").find('input[name=knowledgeIds]').val().split(',')
        });

        knowledgeOverlay.on('change', function(tags, tagIds) {
            $("#testpaper-item-picker-form").find('input[name=knowledgeIds]').val(tagIds.join(','));
        });

        var tagOverlay = new TagChooserOverlay({
            trigger: '.tag-search-trigger',
            element: $('#tags-search-overlay'),
            width: 400,
            align: {
                baseElement: $('#tags-search-group'),
                baseXY: [0, 36]
            },
            choosedTags: $("#testpaper-item-picker-form").find('input[name=tagIds]').val().split(',')
        });

        tagOverlay.on('change', function(tags, tagIds) {
            $("#testpaper-item-picker-form").find('input[name=tagIds]').val(tagIds.join(','));
        });
    }

});