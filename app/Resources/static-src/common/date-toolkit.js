/* eslint-disable no-mixed-operators */

// 秒转化为时间
const sec2Time = (sec, locale) => {
  let time = '';
  let h = parseInt((sec % 86400) / 3600);
  let s = parseInt((sec % 3600) / 60);
  let m = sec % 60;
  if (h > 0) {
    time += h + locale.hour;
  }
  if (s.toString().length < 2) {
    time += '0' + s + locale.minute;
  } else {
    time += s + locale.minute;
  }
  if (m.toString().length < 2) {
    time += '0' + m + locale.second;
  } else {
    time += m + locale.second;
  }
  return time;
};

const timeStampFormatTime = timeStamp => {
  const date = new Date(timeStamp * 1000);
  const year = date.getFullYear();
  const month = date.getMonth() + 1;
  const day = date.getDate();
  const hour = date.getHours();
  const minute = date.getMinutes();
  const second = date.getSeconds();
  return `${[year, month, day]
    .map(n => {
      n = n.toString();
      return n[1] ? n : `0${n}`;
    })
    .join('-')} ${[hour, minute, second]
    .map(n => {
      n = n.toString();
      return n[1] ? n : `0${n}`;
    })
    .join(':')}`;
};

// 将0-9的数字前面加上0，例1变为01
const checkTime = i => {
  if (i < 10 && i >= 0) {
    i = `0${i}`;
  }
  return i;
};

// 倒计时 01:01:01 传入时间戳
const getCountDown = (time, i) => {
  const nowTime = Number(time) - i * 1000;
  let minutes = parseInt((nowTime / 1000 / 60) % 60, 10); // 计算剩余的分钟
  let seconds = parseInt((nowTime / 1000) % 60, 10); // 计算剩余的秒数
  let hours = parseInt(nowTime / (1000 * 60 * 60), 10); // 计算剩余的小时
  minutes = checkTime(minutes);
  seconds = checkTime(seconds);
  hours = checkTime(hours);
  return { hours, minutes, seconds };
};

const isMobileDevice = () => {
  return navigator.userAgent.match(/(iPhone|iPod|Android|ios|iPad)/i);
};

export {
  getCountDown,
  sec2Time,
  timeStampFormatTime,
  isMobileDevice
};
