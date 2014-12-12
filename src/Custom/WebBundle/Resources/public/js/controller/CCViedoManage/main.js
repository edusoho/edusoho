define(function(require, exports, module) {
    var Widget = require('widget');

    var CCViedoMain = Widget.extend({
        attrs: {
        },
        events: {
        },
        setup: function() {
            var swfID = this.element.find('object').attr('id');
            this.player = this.getSWF(swfID);
        },
        showModal: function(event) {
            var $btn = $(event.currentTarget);
            var player = this.player;
            var position = player.getPosition();
            var newUrl = $btn.data('url')+'?position='+position;
            $('[data-role=hide-add-btn').data('url', newUrl).click();
            player.pause();
        },
        delete: function(event) {
            $ele = $(event.currentTarget);
            $ele.data('url') && $.post($ele.data('url'), function(){
                $ele.parents('tr').remove();
            });
        },
        _init_player: function() {

        },
        getSWF: function( swfID ) {
            if (window.document[ swfID ]) {
              return window.document[ swfID ];
            } else if (navigator.appName.indexOf("Microsoft") == -1) {
              if (document.embeds && document.embeds[ swfID ]) {
                return document.embeds[ swfID ];
              }
            } else {
              return document.getElementById( swfID );
            }
        }
    });

    module.exports = CCViedoMain;
});