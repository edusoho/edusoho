define(function (require, exports, module) {
    var Backbone = require('backbone');
    var _ = require('underscore');
    var Validator = require('bootstrap.validator');
    require('common/validator-rules').inject(Validator);

    require("jquery.form");
    var Step1View = Backbone.View.extend({
        events: {
            'change input[type=file]': 'onChangeExcelFile'
        },

        initialize: function () {
            this.render()
        },

        render: function () {
            var template = _.template(this.model.get('template'));
            this.$el.html(template(this.model.toJSON()));
            var self = this;
            var $form = this.$el.find('form');

            // 重置按钮状态，确保从错误页面返回时按钮可用
            $('#start-import-btn').removeClass('disabled');

            $form.attr('action', this.model.get('checkUrl')).ajaxForm({
                beforeSubmit: function () {
                    var validated = true;
                    validator.execute(function (error) {
                        if (error) {
                            validated = false;
                        }
                    });
                    $('#start-import-btn').addClass('disabled');
                    return validated;
                },
                success: function (res) {
                    var status = res.status;
                    var eventListener = 'on' + status.charAt(0).toUpperCase() + status.substr(1);
                    if (Step1View.prototype.hasOwnProperty(eventListener)) {
                        self[eventListener](res);
                    } else {
                        throw new Error("UNKNOWN STATUS:" + status);
                    }
                },
                error: function (error) {
                    console.log('error:', error);
                    // 发生错误时也要重置按钮状态
                    $('#start-import-btn').removeClass('disabled');
                }
            });

            var validator = new Validator({
                element: $form,
                autoSubmit: false,
                failSilently: true
            });
            $form.data('validator', validator);
        },

        onChangeExcelFile: function (event) {
            var filename = $(event.currentTarget).val();
            if (filename === '') {
                $('#start-import-btn').addClass('disabled');
                return;
            }
            // 只有当文件不为空时才启用按钮
            $('#start-import-btn').removeClass('disabled');
            this.model.set('file', filename);
            this.$el.find('.filename').val(filename);
        },

        onDanger: function (data) {
            this.$el.find('.js-importer-message').addClass('alert-danger').html(data.message).removeClass('hidden');
            // 当出现危险状态时，重置按钮状态
            $('#start-import-btn').removeClass('disabled');
        },

        onError: function (data) {
            Backbone.trigger('step2-error', data);
        },

        onSuccess: function (data) {
            Backbone.trigger('step2-success', data);
        }
    });

    module.exports = Step1View;
});
