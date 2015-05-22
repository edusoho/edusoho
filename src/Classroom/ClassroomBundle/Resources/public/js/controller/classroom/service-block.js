define(function(require, exports, module) {

	exports.run = function() {
		$('[data-toggle="popover"]').popover();

		if ($('.class-list').length > 0) {

			$('.class-list .class-item').each(function(i,list){
				var $element = $(this).find('.list-unstyled li');
				serviceSort($element);
			})

		} else {
			var $element = $('.list-unstyled li');
			serviceSort($element);
		};
		

		 function serviceSort($element) {
			var activeIndex = 0;
			$element.each(function(index,item){

				var self = this;
				
				if ($(item).hasClass('active')) {
					
					if (index != activeIndex && index != 0) {
						$(item).insertBefore($element.eq(activeIndex));
						activeIndex ++ ;
					}
				}
			})
		}
		
	}
});