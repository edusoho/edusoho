define(function(require, exports, module) {
    var Widget = require('widget');
    var ThemeManage = Widget.extend({
        attrs: {
            config: {},
            allConfig: {},
            currentIframe: null
        },

        setup: function() {
          this._initEvent();
        },

        _initEvent: function() {
          var self = this;
          this.getElement().on('save_config', function(e, data){
            self._saveConfig(data)
          });
        },

        getElement: function() {
            return $(this.element);
        },

        _saveConfig: function(data) {
            var configs = this.get('config'), $iframe = this.get('currentIframe'), self = this;
            configs = $.extend(configs, data);
            this.set('config', configs, {override: true});
            $.post(this.element.data('url'), {config: this.get('config')}, function(){
              self._flushIframe();
            });
        },

        _flushIframe: function(){
          var time = Date.parse(new Date());
          var $iframe = this.get('currentIframe');
          if (!this.get('iframeSrc')) {
              this.set('iframeSrc', $iframe.attr('src'));
          }
          var src = this.get('iframeSrc') + "?t=" + time;
          $iframe.attr('src', src);
        }
    })

    module.exports = ThemeManage;
})

