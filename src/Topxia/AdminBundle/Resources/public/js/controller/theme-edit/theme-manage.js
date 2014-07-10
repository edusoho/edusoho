define(function(require, exports, module) {

    var Widget = require('widget');

    var ThemeManage = Widget.extend({
        attrs: {
            config: {},
            currentItem: null
        },

        events: {
            "save_config": "_saveBlock"
        },

        setup: function() {
            // this._saveConfig();
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
            var configs = {color: '', blocks:{left:[], right:[], bottom:[]}};

            configs.blocks.left = this._getBlockConfig(this.$('.theme-custom-left-block'));
            configs.blocks.right = this._getBlockConfig(this.$('.theme-custom-right-block'));
            configs.blocks.bottom = this._getBlockConfig(this.$('.theme-custom-bottom-block'));
            configs.color = this._getColorConfig(this.$('.theme-custom-color-block'));
            this.set('config', configs,{override: true});
        },

        _getBlockConfig: function($block) {
            var config = [];

            $($block).find('input[type=checkbox]:checked').each(function(){

                config.push($(this).parents('li').data('config'));
            });
console.log(config);
            return config;
        },

        _getColorConfig: function($block) {
            return $($block).find('input[type=radio]').val();
        },

        _send: function() {
            $.post(this.element.data('url'), {config:this.get('config')}, function(response){
                // window.location.reload();
            });
        }
    })

    module.exports = ThemeManage;

})

