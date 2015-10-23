define(function(require, exports, module) {
	var Class = require('class');

  var BasePlugin = Class.create({
    initialize: function(toolbar) {
        this.toolbar = toolbar;
    }
  });

  module.exports = BasePlugin;
  
});