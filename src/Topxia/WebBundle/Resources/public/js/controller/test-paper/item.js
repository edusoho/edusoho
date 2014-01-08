define(function(require, exports, module) {

    var ItemBase = require('./item-base');

    exports.run = function() {
    	var item = new ItemBase({
        	element: '#test-item-container'
	    });
    }

});