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
            var $modal = $form.parents('.modal');
            e.preventDefault();

            $.get($form.attr('action'), $form.serialize(), function(html) {
                $modal.html(html);
            });
        });

        $("[data-role='batch-select-save']").click(function(){
            itemPickerBody.trigger("batch-save");
        });
    };

    var homeworkItemPickerBody = Widget.extend({
         setup: function (){
            pickerBody = this;
            this.on("batch-save", this.onClickBatchSelectSave);
         },

        events: {
            'click [data-role=picked-item]': 'onClickPickedItem',
            'click .newWindowPreview': 'onClickNewWindowPreview',
            'click [data-role=item-batch-select]': 'onClickBatchSelect',
            'click [data-role=item-select]':'onClickItemBatchSelect'
        },

        onClickBatchSelectSave: function (e) {
            $('#homework-item-picker-body').find('[data-role=item-select]').each(function(index,item){
                if ($(item).is(":checked")) {
                    pickerBody.pickedItem(item);
                };
            });
            $modal.modal('hide');
        },

        onClickBatchSelect: function (e) {
            $this = $(e.currentTarget);
            if ($this.is(':checked')) {
                $('[data-role=item-select]').prop('checked',true);
            } else {
                $('[data-role=item-select]').prop('checked',false);
            }
        },

        onClickItemBatchSelect: function(e){
            var checkedCount = 0;
            var length = this.$('[data-role=item-select]:visible').length;
            
            this.$('[data-role=item-select]').each(function() {
                    if ($(this).is(':checked')) {
                        checkedCount++;
                    }
            });
            
            if(checkedCount == length){
                this.$('[data-role=item-batch-select]:visible').prop('checked', true);
            } else {
                this.$('[data-role=item-batch-select]:visible').prop('checked', false);
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
                pickerBody.refreshPassedDivShow();
            });
        },

        refreshSeqs: function () {
            var seq = 1;
            $("#homework-table-tbody>tr").each(function(index,item) {
                var $tr = $(item);
                    $tr.find('td.seq').html(seq);
                    seq ++;
            });

            $('#homework_items_help').hide();
        },

        refreshPassedDivShow: function() {
            var hasEssay = false;
            $("#homework-table-tbody>tr").each(function() {
                if ($(this).data('type') == 'essay' || $(this).data('type') == 'material') {
                    hasEssay = true;
                }
            });

            if (hasEssay) {
                $(".correctPercentDiv").html('');
            } else {
                var html = '这是一份纯客观题的作业，正确率达到为' +
                    '<input type="text" name="correctPercent[]" class="form-control width-input width-input-mini correctPercent1" value="60" />％合格，'+
                    '<input type="text" name="correctPercent[]" class="form-control width-input width-input-mini correctPercent2" value="80" />％良好，'+
                    '<input type="text" name="correctPercent[]" class="form-control width-input width-input-mini correctPercent3" value="100" />％优秀';

                $(".correctPercentDiv").html(html);
            }
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