define(function(require, exports, module) {
        
    exports.run = function() {
        var $element = $('#thread-table-container');
        require('../../util/short-long-text')($element);
        require('../../util/batch-select')($element);
        require('../../util/batch-delete')($element);
        require('../../util/item-delete')($element);

        $(".promoted-label").on('click', function(){

			var $self = $(this);
			var span = $self.find('span');
			var spanClass = span.attr('class');
			var postUrl = "";

			if(spanClass == "label label-default"){
				postUrl = $self.data('setUrl');
				$.post(postUrl, function(response) {
					var labelStatus = "label label-success";
					span.attr('class',labelStatus)
				});
			}else{
				postUrl = $self.data('cancelUrl');
				$.post(postUrl, function(response) {
					var labelStatus = "label label-default";
					span.attr('class',labelStatus)
				});
			}

		});		
    };

  });

