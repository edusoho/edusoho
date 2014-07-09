define(function(require, exports, module) {

    var Widget = require('widget');




    exports.run = function() {

        var themeManage = new ThemeManage({
            element: '#theme-manage',
            config: $.parseJSON($('#theme-config').html())
        });

        $('body').data('themeManage', themeManage);
    }



    var ThemeManage = Widget.extend({
        attrs: {
            config: {},
            currentItem: null
        },

        events: {
        },

        setup: function() {

        },

        saveConfig: function() {
            var config = {color: '', blocks:{left:[], right:[], bottom:[]}};

            config.blocks.left = this.getBlockConfig(this.$('.theme-custom-left-block'));
            config.blocks.right = this.getBlockConfig(this.$('.theme-custom-right-block'));
            config.blocks.bottom = this.getBlockConfig(this.$('.theme-custom-bottom-block'));
            config.color = this.getColorConfig();

            $.post(this.element.data('saveUrl'), config, function() {

            });
        },

        getBlockConfig: function($block) {
            var config = [];
            $($block).each(function(){
                if ($(this).find('.checkbox').isChecked()) {
                    config.push = $(this).data('config');
                }
            });

            return config;
        },

        getColorConfig: function() {

        }

    }



});

//modal.js

$('.confirm-btn').on('click', function(){
   
 var themeManage = $('body').data('themeManage');
    themeManage.get('currentItem').data('config', config);
    themeManage.saveConfig();

    $modal.close(); 
});
