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
          // this._setupBlockConfig();
          this._setupBottomConfig();
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
            var configs = this.get('config');
            configs.blocks.right = this._getBlockConfig(this.$('.theme-custom-right-block'));
            configs.bottom = this._getBottomConfig(this.$('.theme-custom-bottom-block'));
            configs = $.extend(configs, data);
            this.set('config', configs, {override: true});
        },

        _setupBlockConfig: function() {
            var config = this.get('config');
            var allConfig = this.get('allConfig');
            var self = this;
            this.$('.theme-custom-left-block').find('li').each(function(index, value){
                if ($(this).find('.check-block').prop('checked') == true) {
                    $(this).data('config', config.blocks.left[index]);
                } else {
                    var itemConfig = self._getConfigfromAllConfig($(this).attr('id'), allConfig.blocks.left);
                    $(this).data('config', itemConfig);
                }
            });
            this.$('.theme-custom-right-block').find('li').each(function(index, value){
                if ($(this).find('.check-block').prop('checked') == true) {
                    $(this).data('config', config.blocks.right[index]);
                } else {
                    var itemConfig = self._getConfigfromAllConfig($(this).attr('id'), allConfig.blocks.right);
                    $(this).data('config', itemConfig);
                }
            });
        },

        _setupBottomConfig: function() {
            var config = this.get('config');
            this.$('.theme-custom-bottom-block').find('input[type=radio][value='+config.bottom+']').prop('checked', true);
        },

        _getBlockConfig: function($block) {
            var config = [];
            $($block).find('input[type=checkbox]:checked').each(function(){
                config.push($(this).parents('li').data('config'));
            });

            return config;
        },

        _getBottomConfig: function($block) {
            return $($block).find('input[type=radio]:checked').val();
        },

        _getConfigfromAllConfig: function(id, allConfig){
            for (var itemConfig in allConfig) {
                if (allConfig[itemConfig].id == id) {
                    return allConfig[itemConfig];
                }
            }
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

