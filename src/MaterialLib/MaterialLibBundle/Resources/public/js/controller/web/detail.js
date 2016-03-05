define(function(require, exports, module) {
    var Widget = require('widget');
    var Cover = require('./plugins/cover');
    var Info = require('./plugins/info');

    var DetailWidget = Widget.extend({
            attrs: {
                callback: ''
            },
            events: {
                'click .js-back': 'onClickBack',
                'click .js-cover': 'onClickCover',
                'click .js-info': 'onClickInfo'
            },
            setup: function() {
                this.cover = new Cover({
                    element: '#cover-tab'
                });
                this.info = new Info({
                    element: '#info-tab'
                });
            },
            onClickInfo: function(event) {
                var $target = $(event.currentTarget);
                this._changePane($target);
            },
            onClickCover: function(event) {
                var $target = $(event.currentTarget);
                this._changePane($target);
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