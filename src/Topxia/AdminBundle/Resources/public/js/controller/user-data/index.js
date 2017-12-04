define(function (require, exports, module) {

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