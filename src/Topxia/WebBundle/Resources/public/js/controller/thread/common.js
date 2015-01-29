define(function(require, exports, module) {

    var Share=require('../../util/share.js');
    exports.run = function() {

        Share.create({
                selector: '.share',
                icons: 'itemsAll',
                display: ''
        });

        $('.course-exit-btn').on('click', function(){
        	var $btn = $(this);

        	if (!confirm('您真的要退出学习吗？')) {
        		return false;
        	}

        	$.post($btn.data('url'), function(){
        		window.location.href = $btn.data('goto');
        	});
        });

        $('#next-learn-btn').tooltip({placement: 'top'});
        $('#question-sign').tooltip({placement: 'right'});
    };

});