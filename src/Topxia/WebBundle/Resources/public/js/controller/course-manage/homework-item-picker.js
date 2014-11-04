define(function(require, exports, module) {
    var Widget     = require('widget');
    var $form = $("#homework-item-picker-form");
    var $modal = $form.parents('.modal');
    exports.run = function() {

        var itemPickerBody = new homeworkItemPickerBody ({
            element: '#homework-item-picker-body'
        });

        $("#homework-item-picker-form").submit(function(e) {
            var $form = $(this);
              var   $modal = $form.parents('.modal');
            e.preventDefault();

            $.get($form.attr('action'), $form.serialize(), function(html) {
                $modal.html(html);
            });
        });
    };

    var homeworkItemPickerBody = Widget.extend({
         setup: function (){
            pickerBody = this;
         },

        events: {
            'click [data-role=picked-item]': 'onClickPickedItem',
            'click .newWindowPreview': 'onClickNewWindowPreview',
            'click [data-role=item-batch-select]': 'onClickItemBatchSelect',
            'click [data-role=batch-select-save]': 'onClickBatchSelectSave'
        },

        onClickBatchSelectSave: function (e) {
            $('#homework-item-picker-body').find('[data-role=item-select]').each(function(index,item){
                if ($(item).is(":checked")) {
                    pickerBody.pickedItem(item);
                };
            });
            $modal.modal('hide');
        },

        onClickItemBatchSelect: function (e) {
            $this = $(e.currentTarget);
            if ($this.is(':checked')) {
                $('[data-role=item-select]').prop('checked',true);
            } else {
                $('[data-role=item-select]').prop('checked',false);
            }
        },

        pickedItem : function (e) {
            var $this = $(e);
            var replace = parseInt($this.data('replace'));
            $.get($this.data('url'), function(html) {
                var $trs = $(html).find('tr'),
                    $firstTr = $trs.first();
                if (replace) {
                    $("#homework-item-" + replace).parents('tbody').find('[data-parent-id=' + replace + ']').remove();
                    $("#homework-item-" + replace).replaceWith($trs);
                } else {
                    $("#homework-table-tbody").append($trs);
                }
                pickerBody.refreshSeqs();
            });
        },

        refreshSeqs: function () {
            var seq = 1;
              $("#homework-table-tbody>tr").each(function(index,item) {
                var $tr = $(item);
                    $tr.find('td.seq').html(seq);
                    seq ++;
            });
        },

        onClickPickedItem: function (e) {
                pickerBody.pickedItem(e.currentTarget);
                $modal.modal('hide');
        },

        onClickNewWindowPreview: function (e) {
          window.open($(e.currentTarget).data('url'), '_blank',
                "directories=0,height=580,width=820,scrollbars=1,toolbar=0,status=0,menubar=0,location=0");
        }
    });
});