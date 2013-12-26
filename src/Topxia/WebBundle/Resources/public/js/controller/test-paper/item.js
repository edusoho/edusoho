define(function(require, exports, module) {

    var ItemBase = require('./item-base');
    require('jquery.sortable');

    exports.run = function() {
    	var item = new ItemBase({
        	element: '#test-item-container'
	    });

	    var $list = $('#test-item-table').sortable({
		    containerSelector: '[class=test-item-tbody]',
		    itemPath: '[class!=tab-pane]',
		    itemSelector: '[data-role=item]',
		    containerPath: '> .test-item-tbody',
		    placeholder: '<tr class="placeholder"/>',
		    onDrop: function (item, container, _super) {
                _super(item, container);
                var data = $list.sortable("serialize").get();
                console.log(data);
               /* $.post($list.data('sortUrl'), {ids:data}, function(response){

                });*/
                $list.find('[class=test-item-tbody][class!=tab-pane] [data-role=item]').each(function(index){
                    $(this).find('.number').text(index+1);
                });
            },
		    serialize: function(parent, children, isContainer) {
		    	// console.log(children);
		    	// console.log(isContainer);
		    	//.find('[class=test-item-tbody][class!=tab-pane]')
                return isContainer ? children : parent.attr('id');
            },

            // set item relative to cursor position
			onDragStart: function ($item, container, _super) {
				// console.log(item);
				// console.log(container);
				// console.log(_super);
			    // var offset = $item.offset(),
			    // pointer = container.rootGroup.pointer

			    // adjustment = {
			    //   left: pointer.left - offset.left,
			    //   top: pointer.top - offset.top
			    // }

			    // _super($item, container)
			},

			onDrag: function ($item, position) {
			    // $item.css({
			    //   left: position.left - adjustment.left,
			    //   top: position.top - adjustment.top
			    // })
			}


		})



    }

});