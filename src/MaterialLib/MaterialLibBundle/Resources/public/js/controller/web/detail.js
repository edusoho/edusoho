define(function(require, exports, module) {
    require('jquery.select2-css');
    require('jquery.select2');
    var Widget = require('widget');
    var Validator = require('bootstrap.validator');
    var Notify = require('common/bootstrap-notify');

    var DetailWidget = Widget.extend({
            attrs: {
                callback: ''
            },
            events: {
                'click .js-back': 'onClickBack',
                'click .js-cover': 'onClickCover',
                'click .js-info': 'onClickInfo',
                'click .js-img-set': 'onClickChangePic',
                'submit #info-form': 'onSubmitInfoForm',
                'submit #cover-form': 'onSubmitCoverForm',
            },
            setup: function() {
                this._initInfoForm();
            },
            _initInfoForm: function() {
                this.$('#tags').select2({
                    ajax: {
                        url: this.$('#tags').data('url') + '#',
                        dataType: 'json',
                        quietMillis: 100,
                        data: function(term, page) {
                            return {
                                q: term,
                                page_limit: 10
                            };
                        },
                        results: function(data) {
                            var results = [];
                            $.each(data, function(index, item) {
                                results.push({
                                    id: item.name,
                                    name: item.name
                                });
                            });
                            return {
                                results: results
                            };
                        }
                    },
                    initSelection: function(element, callback) {
                        var data = [];
                        $(element.val().split(",")).each(function() {
                            data.push({
                                id: this,
                                name: this
                            });
                        });
                        callback(data);
                    },
                    formatSelection: function(item) {
                        return item.name;
                    },
                    formatResult: function(item) {
                        return item.name;
                    },
                    width: 'off',
                    multiple: true,
                    placeholder: "请输入标签",
                    multiple: true,
                    createSearchChoice: function() {
                        return null;
                    },
                    maximumSelectionSize: 20
                });
            },
            onSubmitInfoForm: function(event) {
                var $target = $(event.currentTarget);
                $target.find('#info-save-btn').button('loading');

                $.ajax({
                    type:'POST',
                    url:$target.attr('action'),
                    data:this.$('#info-form').serialize()
                }).done(function(){
                    Notify.success('保存成功！');
                }).fail(function(){
                    Notify.danger('保存失败！');
                }).always(function(){
                    $target.find('#info-save-btn').button('reset');
                });

                event.preventDefault();
            },
            onSubmitCoverForm: function(event) {
                var $target = $(event.currentTarget);
                $target.find('#save-btn').button('loading');

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

                event.preventDefault();
            },
            onClickInfo: function(event) {
                var $target = $(event.currentTarget);
                this._changePane($target);
            },
            onClickCover: function(event) {
                var $target = $(event.currentTarget);
                this._changePane($target);
            },
            onClickChangePic: function(event) {
                var $target = $(event.currentTarget);
                var $coverTab =$target.closest('#cover-tab');
                $coverTab.find('.js-cover-img').attr('src', $target.attr('src'));
                $coverTab.find('#thumbNo').val($target.data('no'));
            },
            onClickBack: function() {
                this.back();
            },
            _changePane: function($target) {
                //change li
                $target.closest('.nav').find('li.active').removeClass('active');
                $target.addClass('active');

                //change content
                var $tabcontent = $target.closest('.content').find('.tab-content');
                $tabcontent.find('.tab-pane.active').removeClass('active');
                $tabcontent.find($target.data('target')).addClass('active');

            },
            back: function() {
                this.get('callback')();
                this.element.remove();
                this.destroy();
            }
    });

    module.exports = DetailWidget;

});