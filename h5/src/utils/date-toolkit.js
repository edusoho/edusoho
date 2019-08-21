// 秒转化为时间
const formatTimeByNumber = time => {
  time = parseInt(time, 10);
  if (time < 0) {
    return time;
  }
  const hour = parseInt(time / 3600, 10);
  time %= 3600;
  const minute = parseInt(time / 60, 10);
  time %= 60;
  const second = time;
  if (hour <= 0) {
    return [minute, second].map(n => {
      n = n.toString();
      return n[1] ? n : `0${n}`;
    }).join(':');
  }
  return [hour, minute, second].map(n => {
    n = n.toString();
    return n[1] ? n : `0${n}`;
  }).join(':');
};

// 11-16
const formatSimpleTime = date => {
  const month = date.getMonth() + 1;
  const day = date.getDate();

  return [month, day].map(n => {
    n = n.toString();
    return n[1] ? n : `0${n}`;
  }).join('-');
};

// 2018-12-06
const formatFullTime = date => {
  const year = date.getFullYear();
  const month = date.getMonth() + 1;
  const day = date.getDate();

  return [year, month, day].map(n => {
    n = n.toString();
    return n[1] ? n : `0${n}`;
  }).join('-');
};

// 2018年12月6日
const formatchinaTime = date => {
  const year = date.getFullYear();
  const month = date.getMonth() + 1;
  const day = date.getDate();
  return `${year}年${month}月${day}日`;
};

// 2018/12/06 12:03
const formatTime = date => {
  const year = date.getFullYear();
  const month = date.getMonth() + 1;
  const day = date.getDate();
  const hour = date.getHours();
  const minute = date.getMinutes();
  const second = date.getSeconds();
  return `${[year, month, day].map(n => {
    n = n.toString();
    return n[1] ? n : `0${n}`;
  }).join('/')} ${[hour, minute, second].map(n => {
    n = n.toString();
    return n[1] ? n : `0${n}`;
  }).join(':')}`;
};

// 2018-12-06 12:03
const formatCompleteTime = date => {
  const reg = new RegExp('/', 'g');
  const time = formatTime(date).replace(reg, '-');
  return time.slice(0, -3);
};

const dateTimeDown = date => {
  const now = new Date().getTime();
  if (now > date) {
    return '已到期';
  }
  const diff = parseInt((date - now) / 1000, 10);
  let day = parseInt(diff / 24 / 60 / 60, 10);
  let hour = parseInt((diff / 60 / 60) % 24, 10);
  let minute = parseInt((diff / 60) % 60, 10);
  let second = parseInt(diff % 60, 10);
  day = day ? `${day}天` : '';
  hour = (day || hour) ? `${hour || '0'}小时` : '';
  minute = (day || hour || minute) ? `${minute || '0'}分` : '';
  second = `${second || '0'}秒`;
  const time = day + hour + minute + second;
  return time;
};

// days（传时间戳）
const getOffsetDays = (time1, time2) => {
  const offsetTime = Math.abs(time1 - time2);
  return Math.floor(offsetTime / (3600 * 24 * 1e3));
};

//倒计时 01:01:01 传入时间戳
const getCountDown =(time,i) => {
  let nowTime = Number(time)-(i * 1000);
  let minutes = parseInt(nowTime / 1000 / 60 % 60, 10);//计算剩余的分钟
  let seconds = parseInt(nowTime / 1000 % 60, 10);//计算剩余的秒数
  let hours = parseInt(nowTime / ( 1000 * 60 * 60), 10); //计算剩余的小时
  minutes = checkTime(minutes);
  seconds = checkTime(seconds);
  hours = checkTime(hours);
  return {hours,minutes,seconds}
};

//将0-9的数字前面加上0，例1变为01
const checkTime=(i)=>{ 
  if (i < 10) {
      i = "0" + i;
  }
  return i;
};
//剩余时间 1天1小时1分钟  秒只是在最后60秒显示
const getdateTimeDown = date => {
  const now = new Date().getTime();
  if (now > date) {
    return '';
  }
  const diff = parseInt((date - now) / 1000, 10);
  let day = parseInt(diff / 24 / 60 / 60, 10);
  let hour = parseInt((diff / 60 / 60) % 24, 10);
  let minute = parseInt((diff / 60) % 60, 10);
  let second = parseInt(diff % 60, 10);
  day = day ? `${day}天` : '';
  hour = (day || hour) ? `${hour || '0'}小时` : '';
  minute = (day || hour || minute) ? `${minute || '0'}分钟` : '';
  second = (!day && !hour && !minute) ? `${second || '0'}秒`:'';
  const time = day + hour + minute + second;
  return time;
};


export {
  formatTime,
  formatFullTime,
  formatchinaTime,
  formatSimpleTime,
  formatTimeByNumber,
  formatCompleteTime,
  dateTimeDown,
  getOffsetDays,
  getCountDown,
  getdateTimeDown
};
