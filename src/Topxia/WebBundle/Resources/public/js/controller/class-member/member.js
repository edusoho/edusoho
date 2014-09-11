define(function(require, exports, module) {
        
	exports.run = function() {

        $('.class-student-grids').popover({
            selector: '.grid',
            trigger: 'hover',
            placement: 'auto',
            html: true,
            delay: 200,
            viewport: { selector: '.class-student-grids', padding: 0 },
            content: function() {
                return $(this).find('.student-card-content').html();
            },
        });

        $('.class-student-grids').on('mouseenter', '.popover', function(){
            $(this).addClass('keep-hovering');
        });

        $('.class-student-grids').on('mouseleave', '.popover', function() {
            $(this).removeClass('keep-hovering');
            $(this).prev().popover('hide');
        });

        $('.class-student-grids').on('hide.bs.popover', function () {
            if ($(this).find('.popover').hasClass('keep-hovering')) {
                return false;
            }
            return true;
        });

	};
    
});