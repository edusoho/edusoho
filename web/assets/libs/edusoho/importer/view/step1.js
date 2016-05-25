define(function (require, exports, module) {
    var Backbone = require('backbone');
    var _ = require('underscore');

    require("jquery.form");
    var Step1View = Backbone.View.extend({
        template: _.template(require('./../template/step1.html')),

        events: {
            'change input[type=file]': 'onChangeExcelFile'
        },

        initialize: function (options) {
            var context = {
                'templateUrl': options.templateUrl,
                'registerMode': options.registerMode
            };

            this.$el.html(this.template(context));
            var self = this;
            this.$el.find('form').attr('action', this.model.url + this.model.get('type')).ajaxForm({
                success: function (res) {
                    var status = res.status;
                    var eventListener = 'on' + status.charAt(0).toUpperCase() + status.substr(1);;
                    if (Step1View.prototype.hasOwnProperty(eventListener)) {
                        self[eventListener](res);
                    }else {
                        throw new Error("UNKNOWN STATUS:" + status);
                    }
                },
                error: function (error) {
                    console.log('error:', error);
                }
            });

        },

        onChangeExcelFile: function (event) {
            var filename = $(event.currentTarget).val();
            if(filename === ''){
                return;
            }
            this.model.set('file', filename);
            this.$el.find('.filename').val(filename);
        },
        
        onDanger: function (data) {
            this.$el.find('.js-importer-message').addClass('alert-danger').html(data.message).removeClass('hidden');
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
