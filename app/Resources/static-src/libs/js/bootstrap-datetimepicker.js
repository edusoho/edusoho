import  'bootstrap-datetime-picker';
import  'bootstrap-datetime-picker';
import '!style?insertAt=top!css!nodeModulesDir/bootstrap-datetime-picker/css/bootstrap-datetimepicker.css';

$.fn.datetimepicker.dates['zh_CN'] = {
    days: ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六", "星期日"],
    daysShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六", "周日"],
    daysMin:  ["日", "一", "二", "三", "四", "五", "六", "日"],
    months: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
    monthsShort: ["一月", "二月", "三月", "四月", "五月", "六月", "七月", "八月", "九月", "十月", "十一月", "十二月"],
    today: "今天",
    suffix: [],
    meridiem: ["上午", "下午"]
};

// $.fn.datetimepicker.dates['en'] = {
//     days: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
//     daysShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
//     daysMin: ["Su", "Mo", "Tu", "We", "Th", "Fr", "Sa", "Su"],
//     months: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
//     monthsShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
//     today: "Today"
// };