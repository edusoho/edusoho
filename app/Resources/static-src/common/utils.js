import 'jquery-base64';

const Browser = {};
let userAgent = navigator.userAgent.toLowerCase();
let s;

(s = userAgent.match(/msie ([\d.]+)/)) ? Browser.ie = s[1]:
  (s = userAgent.match(/firefox\/([\d.]+)/)) ? Browser.firefox = s[1] :
    (s = userAgent.match(/chrome\/([\d.]+)/)) ? Browser.chrome = s[1] :
      (s = userAgent.match(/opera.([\d.]+)/)) ? Browser.opera = s[1] :
        (s = userAgent.match(/version\/([\d.]+).*safari/)) ? Browser.safari = s[1] : 0;

Browser.ie10 = /MSIE\s+10.0/i.test(navigator.userAgent);
Browser.ie11 = (/Trident\/7\./).test(navigator.userAgent);
Browser.edge = /Edge\/13./i.test(navigator.userAgent);


const isMobileDevice = () => {
  return navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i);
};

const isMobileUpdateDevice = () => {
  return !!navigator.userAgent.match(/(Android|Linux|webOS|iPhone|iPod|BlackBerry|Windows Phone|miui|1plus)/i);
};

const delHtmlTag = (str) => {
  return str.replace(/<[^>]+>/g, '').replace(/&nbsp;/ig, '');
};

const initTooltips = () => {
  $('[data-toggle="tooltip"]').tooltip({
    html: true,
  });
};

const initPopover = () => {
  $('[data-toggle="popover"]').popover({
    html: true,
  });
};

const sec2Time = (sec) => {
  let time = '';
  let h = parseInt((sec % 86400) / 3600);
  let s = parseInt((sec % 3600) / 60);
  let m = sec % 60;
  if (h > 0) {
    time += h + ':';
  }
  if (s.toString().length < 2) {
    time += '0' + s + ':';
  } else {
    time += s + ':';
  }
  if (m.toString().length < 2) {
    time += '0' + m;
  } else {
    time += m;
  }
  return time;
};

const time2Sec = (time) => {
  let arry = time.split(':');
  let sec = 0;
  for (let i = 0; i < arry.length; i++) {
    if (arry.length > 2) {
      if (i == 0) {
        sec += arry[i] * 3600;
      }
      if (i == 1) {
        sec += arry[i] * 60;
      }
      if (i == 2) {
        sec += parseInt(arry[i]);
      }
    }
    if (arry.length <= 2) {
      if (i == 0) {
        sec += arry[i] * 60;
      }
      if (i == 1) {
        sec += parseInt(arry[i]);
      }
    }
  }
  return sec;
};

const isLogin = () => $('meta[name=\'is-login\']').attr('content') == 1;

const isEmpty = (obj) => {
  return obj === null || obj === '' || obj === undefined || Object.keys(obj).length === 0;
};

const arrayToJson = (formArray) => {
  const dataArray = {};
  $.each(formArray, function() {
    if (dataArray[this.name]) {
      if (!dataArray[this.name].push) {
        dataArray[this.name] = [dataArray[this.name]];
      }
      dataArray[this.name].push(this.value || '');
    } else {
      dataArray[this.name] = this.value || '';
    }
  });

  return dataArray;
};

const arrayIndex = function(data, index){
  let newData = {};

  for(var i in data) {
    newData[data[i][index]] = data[i];
  }

  return newData;
};

const strToBase64 = (str) => {
  return (typeof btoa === 'undefined') ? $.base64.encode(str) : btoa(str);
};

const plainText = (text, length) => {
  text = $.trim(text);
  length = parseInt(length);

  if ((length > 0) && (text.length > length)) {
    text = text.slice(0, length);
    text += '...';
  }

  return text;
};

export {
  Browser,
  isLogin,
  isMobileDevice,
  delHtmlTag,
  initTooltips,
  initPopover,
  sec2Time,
  time2Sec,
  arrayToJson,
  isEmpty,
  strToBase64,
  arrayIndex,
  plainText,
  isMobileUpdateDevice
};