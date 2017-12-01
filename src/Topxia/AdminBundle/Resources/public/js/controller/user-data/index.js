define(function (require, exports, module) {

  var Notify = require('common/bootstrap-notify');
  var Validator = require('bootstrap.validator');
  require('moment2');
  require('bootstrap-daterangepicker.css');
  require('bootstrap-daterangepicker');
  require('common/validator-rules').inject(Validator);
  var DateRangePicker = require('bootstrap.daterangepicker');
  require('echarts');
  var OverviewDateRangePicker = require('./date-range-picker');
  var LearnTimeTrendency = require('./learn-time-trendency');

  exports.run = function() {
    popover();
    learnTimeChart();
  };

  var learnTimeChart = function() {
    new LearnTimeTrendency({
      element: '.js-learn-data-trendency'
    });
  }

  var popover = function() {
    $('.js-user-data-popover').popover({
      html: true,
      trigger: 'hover',
      placement: 'top',
      template: '<div class="popover" role="tooltip"><div class="popover-content"></div></div>',
      content: function() {
        var html = $(this).siblings('.popover-content').html();
        return html;
      }
    });
  }
});