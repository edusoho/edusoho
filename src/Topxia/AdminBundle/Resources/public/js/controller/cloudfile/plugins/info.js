define(function(require, exports, module) {
    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');
    require('jquery.select2-css');
    require('jquery.select2');

    var Info = Widget.extend({
        attrs: {
            callback: ''
        },
        events: {
            'submit #info-form': 'onSubmitInfoForm',
        },
        setup: function() {
            this._init();
        },
        _init: function() {
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
                placeholder: Translator.trans('请输入标签'),
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
                Notify.success(Translator.trans('保存成功！'));
            }).fail(function(){
                Notify.danger(Translator.trans('保存失败！'));
            }).always(function(){
                $target.find('#info-save-btn').button('reset');
            });

            event.preventDefault();
        }
    });

    module.exports = Info;

});