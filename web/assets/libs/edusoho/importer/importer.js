define(function (require, exports, module) {
    var Backbone = require('backbone');
    var Step1View = require('./view/step1');
    var Checker = require('./model/checker');
    var Importer = require('./model/importer');

    var Step2ErrorView = require('./view/step2-error.js');
    var Step2SuccessView = require('./view/step2-success.js');

    var _ = require('underscore');

    module.exports = Backbone.Router.extend({
        routes: {
            "index": 'index',
            'error': 'error',
            'success': 'success'
        },

        index: function () {
            var checker = new Checker(this.options);
            var ste1View = new Step1View({
                'model': checker
            });
            this.$el.html(ste1View.el);
        },

        error: function () {

            if(_.isEmpty(this.errors)){
                return;
            }

            var errorView = new Step2ErrorView(this.errors);
            this.$el.html(errorView.el);
            delete errors;
        },

        success: function () {
            if(_.isEmpty(this.success)){
                return;
            }

            var importer = new Importer(this.success);
            importer.set('type', this.options.type);
            var successView = new Step2SuccessView({
                model: importer
            });

            this.$el.html(successView.el);

            delete this.success;
        },

        initialize: function(options) {
            this.options = options;
            this.$el = $(options.element);
            this._initEvent();
            Backbone.history.start();
            this.navigate('index', {trigger: true});
        },

        _initEvent: function () {
            var self = this;
            Backbone.on('step2-error', function (data) {
                self.errors = data.errorInfo;
                self.navigate('error', {trigger: true});
            });

            Backbone.on('step2-success', function (data) {
                self.success = data;
                self.navigate('success', {trigger: true});
            });
        }
    });
});
