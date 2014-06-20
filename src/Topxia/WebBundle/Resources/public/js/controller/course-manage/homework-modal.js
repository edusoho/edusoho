define(function(require, exports, module) {

    var EditorFactory = require('common/kindeditor-factory');

    var  $modal = $("#modal");
    var Widget     = require('widget');


    var HomeworkItemManager = Widget.extend({

        attrs: {
            currentType: null
        },

        events: {
            'click [data-role=pick-item]': 'onClickPickItem',
        },


        onClickPickItem: function(e) {
            var $btn = $(e.currentTarget);

            var $btn = $('#picker_homework_items');

            // $btn.click(function(){
            //  $.get($btn.data('url'), function(html) {
            //         $modal.html(html);
            //     $modal.modal('show');
            // });
            //     console.log($btn.data('url'));
            // });
            // var excludeIds = [];
            // $("#testpaper-items-" + this.get('currentType')).find('[name="questionId[]"]').each(function(){
            //     excludeIds.push($(this).val());
            // });

            var $modal = $("#modal").modal();
            $modal.data('manager', this);
            $.get($btn.data('url'), function(html) {
                $modal.html(html);
            });
        },

        refreshSeqs: function () {
            var seq = 1;
            $("#homework-table").find("tbody tr").each(function(){
                var $tr = $(this);

                    $tr.find('td.seq').html(seq);
                    seq ++;
            });
        },


    });


        var editor = EditorFactory.create('#homework-about-field', 'simple');
    exports.run = function() {

        new HomeworkItemManager({
            element: '#homework-items-manager'
        });




    };

});