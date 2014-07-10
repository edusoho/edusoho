define(function(require, exports, module) {

    var themeManage = $('body').data('themeManage'); 

    exports.run = function() {

        var $currentItem = themeManage.getCurrentItem();
        var currentConfig = $currentItem.data('config');
        // formInit($(".item-config-form"), currentConfig);

        $("#save-btn").on('click', function(){
            var config = formSerialize($($(this).data('form')));

            var code = $currentItem.data('code').split('_').pop();

            config.code = code;
            config.id = $currentItem.attr('id');

            $("#"+$currentItem.attr('id')).data('config', config);

            themeManage.getElement().trigger('save_config');

            $("#modal").modal('hide');
        });

    };

    var formInit = function ($form, obj) {
        $.each(obj, function(key, item){
            var $item = $form.find('[name=' + key + ']');
            if ($item.length > 0) {
                $item.val(item);
            }
        });
    }

    var formSerialize = function($form) {
        var config = {};
        $form.find('[name]').each(function(){
            var key = $(this).attr('name');
            var value = $(this).val();
            config[key] = value;
        });
        return config;
    }

});


// function saveList($list, config) {}

    // function save()
    // {
    //  var config = [];
    //  $('.left').each(function(){
    //      if ($(this).find('.checkbox').isChecked()) {
    //          config.push = $(this).data('config');
    //      }
    //  });
    // }

    // $.post('/xxx', config, {

    // });


// $('.confirm-btn').on('click', function(){
   
//  var themeManage = $('body').data('themeManage');
//     themeManage.get('currentItem').data('config', config);
//     themeManage.saveConfig();

//     $modal.close(); 
// });