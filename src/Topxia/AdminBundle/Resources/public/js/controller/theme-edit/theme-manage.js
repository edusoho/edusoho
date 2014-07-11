define(function(require, exports, module) {

    var Widget = require('widget');

    var ThemeManage = Widget.extend({
        attrs: {
            config: {},
            allConfig: {},
            currentItem: null
        },

        events: {
            "save_config": "_saveBlock"
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
            this.set('currentItem', $item);
        },

        getCurrentItem: function() {
            return this.get('currentItem');
        },

        _saveBlock: function() {

            this._saveConfig();
            this._send();
        },

        _saveConfig: function() {
            var configs = {color: '', blocks:{left:[], right:[]}, bottom: ''};

            configs.blocks.left = this._getBlockConfig(this.$('.theme-custom-left-block'));
            configs.blocks.right = this._getBlockConfig(this.$('.theme-custom-right-block'));
            configs.bottom = this._getBottomConfig(this.$('.theme-custom-bottom-block'));
            configs.color = this._getColorConfig(this.$('.theme-custom-color-block'));
            this.set('config', configs,{override: true});
        },

        _setupBlockConfig: function() {
            var config = this.get('config');
            var allConfig = this.get('allConfig');
            this.$('.theme-custom-left-block').find('li').each(function(index, value){
                if ($(this).find('.check-block').prop('checked') == true) {
                    $(this).data('config', config.blocks.left[index]);
                } else {
                    $(this).data('config', allConfig.blocks.left[index]);
                }
            });
            this.$('.theme-custom-right-block').find('li').each(function(index, value){
                if ($(this).find('.check-block').prop('checked') == true) {
                    $(this).data('config', config.blocks.right[index]);
                } else {
                    $(this).data('config', allConfig.blocks.right[index]);
                }
            });
        },

        _setupBottomConfig: function() {
            var config = this.get('config');
            this.$('.theme-custom-bottom-block').find('input[type=radio][value='+config.bottom+']').prop('checked', true);
        },

        _setupColorConfig: function() {
            var config = this.get('config');
            this.$('.theme-custom-color-block').find('input[type=radio][value='+config.color+']').prop('checked', true);
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
            $.post(this.element.data('url'), {config:this.get('config')}, function(response){
                // window.location.reload();
            });
        }
    })

    module.exports = ThemeManage;

})

