define(function(require, exports, module) {

    var themeModal = require('./theme-modal');

    exports.run = function() {

        var x = themeModal.getAll();

        // $('xxx').on('click', function(){
            
        //     // themeModal.set('name', config);

        //     $('#modal').modal('hide');

        // })

    };

    function saveList($list, config) {}

    function save()
    {
    	var config = [];
    	$('.left').each(function(){
    		if ($(this).find('.checkbox').isChecked()) {
    			config.push = $(this).data('config');
    		}
    	});
    }

    $.post('/xxx', config, {

    });



});