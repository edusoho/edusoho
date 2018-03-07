export default  class Sing {
  constructor($element) {
    this.$element = $element;
    this.selectedDate = null;
    this.inited = false;
    this.daysInMonth = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    this.signedRecordsUrl = null;
    this.signUrl = null;
    this.initEvent();
    this.setup();
  }

  initEvent() {
    this.$element.on('click', '[data-role=sign]', () => this.sign());
    this.$element.on('mouseenter', '[data-role="signed"]', () => this.signedIn());
    this.$element.on('mouseleave', '[data-role="signed"]', () => this.signedOut(event));
    this.$element.on('mouseenter', '.sign_main', () => this.keep());
    this.$element.on('mouseleave', '.sign_main', () => this.remove());
    this.$element.on('click', '[data-role=previous]', () => this.previousMonth());
    this.$element.on('click', '[data-role=next]', () => this.nextMonth());
  }

  setup() {
    this.selectedDate = this.$element.find('#title-month').data('time');
    var signedRecordsUrl = this.$element.data('records');
    var signUrl = this.$element.data('signurl');
    this.signedRecordsUrl = signedRecordsUrl;
    this.signUrl = signUrl;
  }
  keep() {
    this.$element.find('.sign_main').addClass('keepShow');
  }
  remove() {
    this.$element.find('.sign_main').removeClass('keepShow');
    this.hiddenSignTable();
  }
  getDaysInMonth(month, year) {
    if ((month == 1) && (year % 4 == 0) && ((year % 100 != 0) || (year % 400 == 0))) {
      return 29;
    } else {
      return this.daysInMonth[month];
    }
  }
  getWeekByDate(year, month, day) {
    return new Date(year + '/' + month + '/' + day).getDay();
  }
  sign() {
    var today = new Date().getDate();
    $.ajax({
      url: this.signUrl,
      dataType: 'json',
      success:  (data) => {

        $('#sign').html('<div  class="sign-area" data-role="signed" onclick="return false;" >' + '<a class="btn-signin after" >' + Translator.trans('classroom.member_signed') + '<br>' + Translator.trans('classroom.sign_keep_days', {'keepDays' : data.keepDays}) +  '</a></div>');
        this.showSignTable();
        this.initTable(true);
        this.$element.find('.d-' + today).addClass('signed_anime_day');
        // window.location.reload();
      },
      error:  (xhr)=> {
      }
    });
  }
  signedIn() {
    if (!this.inited) {
      this.initTable();
    }
    this.showSignTable();
  }
  signedOut(e) {
    this.$element.find('.sign_main').removeClass('keepShow');
    setTimeout( () =>{
      if (this.$element.find('.sign_main').hasClass('keepShow')) {
        return;
      } else {
        this.hiddenSignTable();
      }
    }, 1000);
  }
  showSignTable() {
    this.$element.find('.sign_main').addClass('keepShow');
    this.$element.find('.sign_main').attr('style', 'display:block');
  }
  hiddenSignTable() {
    this.$element.find('.sign_main').removeClass('keepShow');
    this.$element.find('.sign_main').attr('style', 'display:none');
  }
  initTable(signedToday) {
    var selectedDate = this.selectedDate;
    selectedDate = selectedDate.split('/');
    var year = parseInt(selectedDate[0]);
    var month = parseInt(selectedDate[1]);
    var days = this.getDaysInMonth(month - 1, year);
    var $tbody = this.$element.find('tbody');
    var newtr = '<tr><td class=\'t-1-0 \'></td><td class=\'t-1-1 \'></td><td class=\'t-1-2 \'></td><td class=\'t-1-3 \'></td><td class=\'t-1-4 \'></td><td class=\'t-1-5 \'></td><td class=\'t-1-6 \'></td></tr>';

    var url = this.signedRecordsUrl + '?startDay=' + year + '-' + month + '-1' + '&endDay=' + year + '-' + month + '-' + days;

    $tbody.append(newtr);
    var row = 1;
    var today = new Date().getDate();
    for (var day = 1; day <= days; day++) {
      var week = this.getWeekByDate(year, month, day);
      $tbody.find('.t-' + row + '-' + week).html(day);
      $tbody.find('.t-' + row + '-' + week).addClass('d-' + day);

      if (week == 6 && day != days) {
        row++;
        newtr = '<tr><td class="day t-' + row + '-0 "></td><td class="day t-' + row + '-1 "></td><td class="day t-' + row + '-2 "></td><td class="day t-' + row + '-3 "></td><td class="day t-' + row + '-4 "></td><td class="day t-' + row + '-5 "></td><td class="day t-' + row + '-6 "></td></tr>';
        $tbody.append(newtr);
      }
    }

    $.ajax({
      url: url,
      dataType: 'json',
      async: true,//(默认: true) 默认设置下，所有请求均为异步请求。如果需要发送同步请求，请将此选项设置为 false。注意，同步请求将锁住浏览器，用户其它操作必须等待请求完成才可以执行。
      success: (data)=> {
        for (var i = 0; i < data.records.length; i++) {
          var day = parseInt(data.records[i]['day']);
          $tbody.find('.d-' + day).addClass('signed_day').attr('title', Translator.trans('classroom.sign_rank_hint', {'time' : data.records[i]['time'], 'rank' : data.records[i]['rank']}));
        }
        this.$element.find('.today-rank').html(data.todayRank);
        this.$element.find('.signed-number').html(data.signedNum);
        this.$element.find('.keep-days').html(data.keepDays);
      }
    });

    this.inited = true;
    if (signedToday) {
      var $signbtn = this.$element.find('[data-role=sign]');
      $signbtn.data('role', 'signed');
      $signbtn.on('mouseenter', function () {
        this.signedIn();
      });
      $signbtn.on('mouseleave', function () {
        this.signedOut();
      });
      $signbtn.on('click', false);
      $signbtn.addClass('sign-btn');
      $signbtn.find('.sign-text').html(Translator.trans('classroom.member_signed'));
    }

  }
  previousMonth() {
    var currentDate = this.selectedDate;
    currentDate = currentDate.split('/');
    var currentYear = parseInt(currentDate[0]);
    var currentMonth = parseInt(currentDate[1]);
    var nextMonth = 0;
    var nextYear = currentYear;
    if (currentMonth == 1) {
      nextMonth = 12;
      nextYear = currentYear - 1;
    } else {
      nextMonth = currentMonth - 1;
    }
    nextMonth = nextMonth < 10 ? '0' + nextMonth : nextMonth;
    this.selectedDate = nextYear + '/' + nextMonth;
    this.$element.find('tbody').html('');
    this.$element.find('[data-role=next]').removeClass('disabled-next');
    this.$element.find('#title-month').html(nextYear + Translator.trans('site.date.year') + nextMonth + Translator.trans('site.date.month'));
    this.initTable();
  }
  nextMonth() {
    var currentDate = this.selectedDate;
    currentDate = currentDate.split('/');
    var currentYear = parseInt(currentDate[0]);
    var currentMonth = parseInt(currentDate[1]);
    var nextMonth = 0;
    var nextYear = currentYear;
    if (currentMonth == (new Date().getMonth() + 1) && currentYear == (new Date().getFullYear())) {
      return;
    } else if (currentMonth == 12) {
      nextMonth = 1;
      nextYear = currentYear + 1;
    } else {
      nextMonth = currentMonth + 1;
    }
    if (nextMonth == (new Date().getMonth() + 1) && currentYear == (new Date().getFullYear())) {
      this.$element.find('[data-role=next]').addClass('disabled-next');
    }
    nextMonth = nextMonth < 10 ? '0' + nextMonth : nextMonth;
    this.selectedDate = nextYear + '/' + nextMonth;
    this.$element.find('tbody').html('');
    this.$element.find('#title-month').html(nextYear + Translator.trans('site.date.year') + nextMonth + Translator.trans('site.date.month'));
    this.initTable();
  }
}