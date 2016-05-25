define(function (require, exports, module) {
    var Backbone = require('backbone');
    var Handlebars = require('handlebars');
    var Step1View = Backbone.View.extend({
         template: Handlebars.compile(require('./template/step1.html'))
    });
});
