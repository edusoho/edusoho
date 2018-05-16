export default class Select {

  constructor(startDate, endDate, jsEvent, view, resource) {
    this.init(startDate, endDate, jsEvent, view, resource);
  }

  init(startDate, endDate, jsEvent, view, resource) {
    const $target = $('.fc-highlight');
    const targetTop = $target.css('top');
    const targetBottom = $target.css('bottom');
    const targetLength = $target.height();
    $target.popover({
      container: 'body',
      html: true,
      content: `<div class="cd-text-medium cd-mb8">排课时间：</div>
                <div class="schedule-popover-content__time cd-dark-minor cd-mb8">${startDate.format('L')}</div>
                <div class="cd-mb8"><input class="time-input js-time-start form-control" value=${startDate.format('HH:mm')} name="startTime"> — <input class="time-input js-time-end form-control" name="endTime" value=${endDate.format('HH:mm')}></div>`,
      template: `<div class="popover arrangement-popover">
                  <div class="arrangement-popover-content popover-content">
                  </div>
                </div>`,
      trigger: 'toggle'
    });
    $target.popover('show');

    $('.arrangement-popover').prevAll('.arrangement-popover').remove();

    this.initEvent();
    const event = {
      start: startDate.format(),
      end: endDate.format()
    };
    let events = [];
    events.push(event);
    console.log(events);
  }

  initEvent() {
    $('.js-time-start').change((event) => {
      const $target = $(event.target);
      localStorage.setItem('start', $target.val());
      console.log(localStorage.getItem('start'));
    });
    $('.js-time-end').change((event) => {
      const $target = $(event.target);
      localStorage.setItem('end', $target.val());
      console.log(localStorage.getItem('end'));
    });
  }
}