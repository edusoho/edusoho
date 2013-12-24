define(function(require, exports, module) {

    var ItemBase = require('./item-base');
    exports.run = function() {
    	$element = '#test-item-container';
    	var item = new ItemBase({
        	element: $element
	    });
    }
    

});