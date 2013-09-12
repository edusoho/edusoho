define(function(require, exports, module) {

    var Widget = require('widget'),
        Validator = require('bootstrap.validator');

    var ThreadShowWidget = Widget.extend({
        events: {
            'click [data-role=thread-delete]': 'deleteThread',
            'click [data-role=thread-stick]': 'stickThread',
            'click [data-role=thread-unstick]': 'unstickThread',
            'click [data-role=thread-elite]': 'eliteThread',
            'click [data-role=thread-unelite]': 'uneliteThread',
            'click [data-role=post-delete]': 'deletePost'
        },
        setup: function() {
            this.on('reload', this.onReload, this);
        },

        deleteThread: function(e) {

        },

        stickThread: function(e) {

        },

        unstickThread: function(e) {

        },

        eliteThread: function(e) {

        },

        uneliteThread: function(e) {

        },

        deletePost: function(e) {

        },

        onReload: function() {
            var that = this;
            var validator = new Validator({
                element: this.$('[data-role=post-form]'),
                autoSubmit: false
            });

            validator.addItem({
                element: '[name="post[content]"]',
                required: true
            });

            validator.on('formValidated', function(err, msg, ele) {
                if (err == true) {
                    return ;
                }

                var $form = this.element;
                $.post($form.attr('action'), $form.serialize(), function(html) {
                    that.$('[data-role=post-list]').append(html);
                    var number = parseInt(that.$('[data-role=post-number]').text());
                    that.$('[data-role=post-number]').text(number+1+'');
                    $form.find('textarea').val('');
                });

                return false;
            });
        }

    });

    module.exports = ThreadShowWidget;
});