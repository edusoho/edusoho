define(function(require, exports, module) {

    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    require('mediaelementplayer');

    var gid = 0;

    var AudioPlayer = Widget.extend({

        attrs: {
            player: null
        },

        events: {
            'click .retry': 'onRetry'
        },

        _endedTriggered : false,

        onRetry: function() {
            this.setup();
        },

        setup: function() {
            var self = this;

            var fileId = this.element.data('fileId');

            $.get('/question_ajax/file_url', {id:fileId}, function(response) {
                if (response.status == 'waiting') {
                    Notify.warning(response.message);
                    self.element.html('<a href="javascript:;" class="retry"><span class="glyphicon glyphicon-volume-up text-danger"></span> 请重试</a>');
                    return ;
                }

                if (response.status == 'error') {
                    Notify.danger(response.message);
                    self.element.html('<a href="javascript:;" class="retry"><span class="glyphicon glyphicon-volume-up text-danger"></span> 请重试</a>');
                    return ;
                }

                if (response.status != 'ok') {
                    Notify.danger('音频载入失败，请重试。');
                    self.element.html('<a href="javascript:;" class="retry"><span class="glyphicon glyphicon-volume-up text-danger"></span> 请重试</a>');
                    return ;
                }

                var id = 'audio-player-' + gid;

                self.element.html(
                    '<audio id="' + id + '" style="display:inline-block;width:1px;height:1px;" class="hide"></audio>' +
                    '<span id="' + id + '-flag" class="glyphicon glyphicon-volume-up text-info" style="display:none;"></span>'
                );

                self.element.find('.glyphicon').fadeIn('slow');

                var audioPlayer = new MediaElement(id, {
                    type: ['audio/mp3'],
                    mode:'auto_plugin',
                    enablePluginDebug: false,
                    enableAutosize:true,
                    success: function(media) {
                        media.addEventListener('ended', function() {
                            if (self._endedTriggered) {
                                return ;
                            }
                            $('#' + id + '-flag').fadeOut('slow');
                            self.trigger('ended');
                            self._endedTriggered = true;
                        });

                        var sources = [
                            { src: response.url, type: 'audio/mp3' }
                        ];
                        media.setSrc(sources);
                        media.load();
                        media.play();
                    }
                });

                gid ++;

                self.set('player', audioPlayer);

            }, 'json');
        },



    });

    module.exports = AudioPlayer;

});