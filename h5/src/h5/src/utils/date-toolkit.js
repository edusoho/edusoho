/**
 *
 * @param {*} time  单位是毫秒
 */
const compareNowTime = time => {
  const now = Date.parse(new Date());
  const startTime = time;
  return now < startTime;
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
    .join("-")} ${[hour, minute, second]
    .map(n => {
      n = n.toString();
      return n[1] ? n : `0${n}`;
    })
    .join(":")}`;
};

export { compareNowTime, timeStampFormatTime };
