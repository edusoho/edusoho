define(function(require, exports, module) {

  require("jquery.bootstrap-datetimepicker");

  exports.run = function() {
    var $element = $('.js-table-container');
    require('../../../util/short-long-text')($element);
  };

});