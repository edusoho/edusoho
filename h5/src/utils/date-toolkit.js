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
  formatTimeByNumber
};
