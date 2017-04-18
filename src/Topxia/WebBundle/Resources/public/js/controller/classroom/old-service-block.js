define(function(require, exports, module) {
  //2.0直接移除
	exports.run = function() {
		$('[data-toggle="popover"]').popover();
	}
});