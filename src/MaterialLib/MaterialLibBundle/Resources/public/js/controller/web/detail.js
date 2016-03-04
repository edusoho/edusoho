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
                'submit #info-form': 'onSubmitInfoForm'
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
            onClickBack: function() {
                this.back();
            },
            back: function() {
                this.get('callback')();
                this.element.remove();
                this.destroy();
            }
    });

    module.exports = DetailWidget;

});