define(function(require, exports, module) {

    var Widget = require('widget');

    var ThemeManage = Widget.extend({
        attrs: {
            config: {},
            allConfig: {},
            currentItem: null,
            currentIframe: null
        },

        setup: function() {
          this._initEvent();
          // this._setupBlockConfig();
          this._setupBottomConfig();
          this._setupColorConfig();
        },

        _initEvent: function() {
          var self = this;
          this.getElement().on('save_config', function(e, data){
            self._saveBlock(data)
          });
          this.getElement().on('save_sort',  function(e, data){
            self._saveSort(data)
          });
        },

        getElement: function() {
            return $(this.element);
        },

        _saveBlock: function(data) {
            this._saveConfig(data);
            this._send();
        },

        _saveSort: function(data) {
            this._saveConfig(data);
            this._sendSort();
        },

        _saveConfig: function(data) {
            var configs = this.get('config');
            configs.blocks.right = this._getBlockConfig(this.$('.theme-custom-right-block'));
            configs.bottom = this._getBottomConfig(this.$('.theme-custom-bottom-block'));
            // configs.navigation = this._getNavigation();
            configs.maincolor = this._getColorConfig(this.$('.theme-custom-color-block'));
            configs.navigationcolor = this._getColorConfig(this.$('.theme-custom-navigationcolor-block'));
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

        _setupColorConfig: function() {
            var config = this.get('config');
            this.$('.theme-custom-color-block').find('input[type=radio][value='+config.maincolor+']').prop('checked', true);
            this.$('.theme-custom-navigationcolor-block').find('input[type=radio][value='+config.navigationcolor+']').prop('checked', true);
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

        _getColorConfig: function($block) {
            return $($block).find('input[type=radio]:checked').val();
        },

        _send: function() {
            var self = this;
            var currentData = $('#'+$(this.get('currentItem')).attr('id')).data('config');
            var isChoiced = $('#'+$(this.get('currentItem')).attr('id')).find('.check-block').prop('checked');

            $.post(this.element.data('url'), {config:this.get('config'), currentData: currentData}, function(html){

                $('#'+$(html).attr('id')).replaceWith($(html));
                $('#'+$(html).attr('id')).data('config', currentData);
                if (isChoiced) {
                    $('#'+$(html).attr('id')).find('.check-block').prop('checked', true);
                    $('#'+$(html).attr('id')).find('.item-edit-btn,.item-set-btn').show();
                }
                
                self._flushIframe();
            });
        },

        _sendSort: function() {
            var self = this;

            $.post(this.element.data('url'), {config: this.get('config')}, function(html){
                 self._flushIframe();
            });
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

