import DateRangePicker from 'app/common/daterangepicker';
import Emitter from "component-emitter";

export default class CourseOverviewDateRangePicker extends Emitter {

  constructor(containerSelector) {

    super();

    let dateRangePickerSelector = containerSelector + ' .js-date-range-input';
    new DateRangePicker(dateRangePickerSelector);

    let self = this;

    this.$drp = $(dateRangePickerSelector);

    this.$drp.on('apply.daterangepicker', function () {
      self.emit('date-picked', {startDate:self.getStartDate(), endDate:self.getEndDate()});
    });


    let quickDayPickerSelector = containerSelector + ' .js-quick-day-pick';
    $(quickDayPickerSelector).on('click', function () {
      let days = $(this).data('days');
      let now = new Date();
      self.$drp.data('daterangepicker').setEndDate(now.toLocaleDateString());

      now.setDate(now.getDate() - days + 1);
      let endDate = now.toLocaleDateString();
      self.$drp.data('daterangepicker').setStartDate(endDate);

      self.emit('date-picked', {startDate:self.getStartDate(), endDate:self.getEndDate()});
    });

  }

  getStartDate() {
    return this.$drp.data('daterangepicker').startDate.format('YYYY-MM-DD');
  }

  getEndDate() {
    return this.$drp.data('daterangepicker').endDate.format('YYYY-MM-DD');
  }

}