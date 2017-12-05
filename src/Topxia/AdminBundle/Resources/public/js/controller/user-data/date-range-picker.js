define(function (require, exports, module) {
  var eventEmitter = require('emitter');
  var DateRangePicker = require('bootstrap.daterangepicker');

  var OverviewDateRangePicker = function() {
    var dateRangePickerSelector = '.js-date-range-input';
    var maxDate = moment().format('YYYY/MM/DD');
    var minDate = moment().subtract(1,'years').format('YYYY/MM/DD');
    new DateRangePicker(dateRangePickerSelector,{'minDate' : minDate, 'maxDate' : maxDate});
    var self = this;
    this.$drp = $(dateRangePickerSelector);
    this.$drp.on('apply.daterangepicker', function() {
      $(this).closest('#date-range-picker').find('.js-quick-day-pick').removeClass('gray-darker');
      self.emit('date-picked', {startDate:self.getStartDate(), endDate:self.getEndDate()});
    });

    var quickDayPickerSelector = ' .js-quick-day-pick';
    $(quickDayPickerSelector).on('click', function() {
      $(this).addClass('gray-darker').siblings().removeClass('gray-darker');
      var days = $(this).data('days');
      var now = new Date();
      self.$drp.data('daterangepicker').setEndDate(now);
      now.setDate(now.getDate() - days + 1);
      self.$drp.data('daterangepicker').setStartDate(now);
      self.emit('date-picked', {startDate:self.getStartDate(), endDate:self.getEndDate()});
    });
  }

  OverviewDateRangePicker.prototype = new eventEmitter;

  OverviewDateRangePicker.prototype.getStartDate = function() {
    return this.$drp.data('daterangepicker').startDate.format('YYYY-MM-DD');
  },

  OverviewDateRangePicker.prototype.getEndDate = function() {
    return this.$drp.data('daterangepicker').endDate.format('YYYY-MM-DD');
  }

  module.exports = OverviewDateRangePicker;
})

