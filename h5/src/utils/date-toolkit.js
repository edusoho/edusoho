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

const formatFullTime = date => {
  const year = date.getFullYear();
  const month = date.getMonth() + 1;
  const day = date.getDate();

  return [year, month, day].map(n => {
    n = n.toString();
    return n[1] ? n : `0${n}`;
  }).join('-');
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

export {
  formatTime,
  formatFullTime,
  formatSimpleTime,
  formatTimeByNumber
};
