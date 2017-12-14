define(function(require, exports, module) {

    var Widget = require('widget');

    var ThemeManage = Widget.extend({
        attrs: {
            config: {},
            allConfig: {},
            currentItem: null,
            currentIframe: null
        },

        events: {
            "save_config": "_saveBlock",
            "save_sort": "_saveSort"
        },

        setup: function() {
            this._setupBlockConfig();
            this._setupBottomConfig();
            this._setupColorConfig();
        },

        getElement: function() {
            return $(this.element);
        },

        setCurrentItem: function($item) {
            this.set('currentItem', $item,{override: true});
        },

        getCurrentItem: function() {
            return this.get('currentItem');
        },

        _saveBlock: function() {
            this._saveConfig();
            this._send();
        },

        _saveSort: function() {
            this._saveConfig();
            this._sendSort();
        },

        _saveConfig: function() {
            var configs = {maincolor: '',navigationcolor:'', blocks:{left:[], right:[]}, bottom: ''};

            configs.blocks.left = this._getBlockConfig(this.$('.theme-custom-left-block'));
            configs.blocks.right = this._getBlockConfig(this.$('.theme-custom-right-block'));
            configs.bottom = this._getBottomConfig(this.$('.theme-custom-bottom-block'));
            configs.navigation = this._getNavigation();
            configs.maincolor = this._getColorConfig(this.$('.theme-custom-color-block'));
            configs.navigationcolor = this._getColorConfig(this.$('.theme-custom-navigationcolor-block'));
            this.set('config', configs,{override: true});
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

        _getNavigation: function($block){
          var topNum = this.getElement().find('#topNavNum').val();
          var config = {};
          config.topNavNum = topNum;
          
          return config;
        },

        _getBottomConfig: function($block) {
            return $($block).find('input[type=radio]:checked').val();
        },

        _getColorConfig: function($block) {
            return $($block).find('input[type=radio]:checked').val();
        },

        _send: function() {
            var $iframe = this.get('currentIframe');
            var currentData = $('#'+$(this.get('currentItem')).attr('id')).data('config');
            var isChoiced = $('#'+$(this.get('currentItem')).attr('id')).find('.check-block').prop('checked');

            $.post(this.element.data('url'), {config:this.get('config'), currentData: currentData}, function(html){

                $('#'+$(html).attr('id')).replaceWith($(html));
                $('#'+$(html).attr('id')).data('config', currentData);
                if (isChoiced) {
                    $('#'+$(html).attr('id')).find('.check-block').prop('checked', true);
                    $('#'+$(html).attr('id')).find('.item-edit-btn,.item-set-btn').show();
                }
                var src = $iframe.attr('src') + "?t=" + Date.parse(new Date());
                $iframe.attr('src', src);
            });
        },

        _sendSort: function() {
            var $iframe = this.get('currentIframe');

            $.post(this.element.data('url'), {config:this.get('config')}, function(html){
                var src = $iframe.attr('src') + "?t=" + Date.parse(new Date());
                $iframe.attr('src', src);
            });
        },

        _getConfigfromAllConfig: function(id, allConfig){
            for (var itemConfig in allConfig) {
                if (allConfig[itemConfig].id == id) {
                    return allConfig[itemConfig];
                }
            }
        }
    })

    module.exports = ThemeManage;

})

