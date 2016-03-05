define(function(require, exports, module) {
    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    var Cover = Widget.extend({
        attrs: {
            callback: ''
        },
        events: {
            'click .js-img-set': 'onClickChangePic',
            'click .js-reset-btn': 'onClickReset',
            'submit #cover-form': 'onSubmitCoverForm',
        },
        setup: function() {
        },
        onClickReset: function(event) {
            this.$('#thumbNo').val('');
            this.$('.js-cover-img').attr('src', this.$('#orignalThumb').val());
        },
        onSubmitCoverForm: function(event) {
            var $target = $(event.currentTarget);
            $target.find('#save-btn').button('loading');
            if ($target.find('#thumbNo').val()) {
                $.ajax({
                    type:'POST',
                    url:$target.attr('action'),
                    data:$target.serialize()
                }).done(function(){
                    Notify.success('保存成功！');
                }).fail(function(){
                    Notify.danger('保存失败！');
                }).always(function(){
                    $target.find('#save-btn').button('reset');
                });
            } else {
                Notify.success('保存成功！');
                $target.find('#save-btn').button('reset');
            }

            
            event.preventDefault();
        },
        onClickChangePic: function(event) {
            var $target = $(event.currentTarget);
            var $coverTab =$target.closest('#cover-tab');
            $coverTab.find('.js-cover-img').attr('src', $target.attr('src'));
            $coverTab.find('#thumbNo').val($target.data('no'));
        }
    });

    module.exports = Cover;

});