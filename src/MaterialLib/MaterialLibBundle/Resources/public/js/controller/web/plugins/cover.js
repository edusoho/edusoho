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
            'click .js-set-default': 'onClickDefault',
            'click .js-set-select': 'onClickSelect',
            'submit #cover-form': 'onSubmitCoverForm',
        },
        setup: function() {
            this._initPlayer();
        },
        onClickReset: function(event) {
            this.$('#thumbNo').val('');
            this.$('.js-cover-img').attr('src', this.$('#orignalThumb').val());
        },
        onClickDefault: function(event) {
            this._changePane($(event.currentTarget));
        },
        onClickSelect: function(event) {
            this._changePane($(event.currentTarget));
        },
        _initPlayer: function() {
            this.$('#viewerIframe');
            var messenger = new Messenger({
                name: 'parent',
                project: 'PlayerProject',
                children: [document.getElementById('viewerIframe')],
                type: 'parent'
            });

            messenger.on("ready", function() {
                console.log('i am ready');
            });

            messenger.on("ended", function() {
                console.log('i am ended');
            });

            messenger.on("playing", function() {
                console.log('i am playing');
            });

            messenger.on("paused", function() {
                console.log('i am paused');
            });

            messenger.on("onMarkerReached", function(marker,questionId){
                console.log('MarkerReached');                
            });
        },
        _changePane: function($target) {
            $target.parent().find('a.disabled').removeClass('disabled');
            $target.addClass('disabled');
            var $container = $target.closest('#thumbnail-set');
            $container.find('.thumbnail-pane.active').removeClass('active');
            $container.find($target.attr('href')).addClass('active');
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