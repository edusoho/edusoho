define(function (require, exports, module) {

  var LearnTimeTendency = require('./learn-time-tendency');

  exports.run = function() {
    popover();
    learnTimeChart();
  };

  var learnTimeChart = function() {
    new LearnTimeTendency({
      element: '.js-learn-data-tendency'
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