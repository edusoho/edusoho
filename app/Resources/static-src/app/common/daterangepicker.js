import 'moment';
import 'bootstrap-daterangepicker';

export default class DateRangePicker {
  constructor(element, options = {}, locale = {}) {
    let localeOption = this.getLocalOptions(locale);
    options.locale = localeOption;
    console.log(options);
    $(element).daterangepicker(options);

  }

  getLocalOptions(locale) {
    let defaultLocale = this.getDefaultLocale();
    for (let argv in defaultLocale) {
      if(locale[argv]) {
        defaultLocale[argv] = locale[argv];
      }
    }
    return defaultLocale;
  }

  /**
     * 目前只支持中、英文，不支持其他语言，暂时不走yml
     */
  getDefaultLocale() {
    let locale_zh_CN = {
      'format': 'YYYY/MM/DD',
      'separator': '-',
      'applyLabel': '确定',
      'cancelLabel': '取消',
      'fromLabel': '起始时间',
      'toLabel': '结束时间',
      'customRangeLabel': '自定义',
      'weekLabel': 'W',
      'daysOfWeek': [
        '日',
        '一',
        '二',
        '三',
        '四',
        '五',
        '六'
      ],
      'monthNames': [
        '一月',
        '二月',
        '三月',
        '四月',
        '五月',
        '六月',
        '七月',
        '八月',
        '九月',
        '十月',
        '十一月',
        '十二月'
      ],
      'firstDay': 1
    };

    let locale_en = {
      'format': 'yyyy/mm/dd',
      'separator': '-',
      'applyLabel': 'Apply',
      'cancelLabel': 'Cancel',
      'fromLabel': 'From',
      'toLabel': 'To',
      'customRangeLabel': 'Custom',
      'weekLabel': 'W',
      'daysOfWeek': [
        'Su',
        'Mo',
        'Tu',
        'We',
        'Th',
        'Fr',
        'Sa'
      ],
      'monthNames': [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
      ],
      'firstDay': 1
    };

    if (app.lang === 'zh_CN') {
      return locale_zh_CN;
    }

    return locale_en;

  }
}