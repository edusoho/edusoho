define(function(require, exports, module) {

    module.exports = function($element) {
        $element.on('click', '[data-role=batch-select]', function(){
           if( $(this).is(":checked") == true){
				$('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-download').attr('disabled', false);
                $element.find('[data-role=batch-select], [data-role=batch-item]').prop('checked', true);
            } else {
				$('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-download').attr('disabled', true);
                $element.find('[data-role=batch-select], [data-role=batch-item]').prop('checked', false);
            }
        });

        $element.on('click', '[data-role=batch-item]', function(){

        	var length = $element.find('[data-role=batch-item]').length;
        	var checked_count = 0;
        	$element.find('[data-role=batch-item]').each(function(){
        		if ($(this).is(":checked")) {
        			checked_count++;
        		};
        	})

        	if (checked_count == length){
        		$element.find('[data-role=batch-select]').prop('checked',true);
        	} else {
        		$element.find('[data-role=batch-select]').prop('checked',false);
			}
			if (checked_count !== 0) {
				$('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-download').attr('disabled', false);
			} else {
				$('.js-batch-tag-btn, .js-batch-delete-btn, .js-batch-download').attr('disabled', true);
			}
        	
        })

    };

});