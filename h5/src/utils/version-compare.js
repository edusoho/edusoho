// 版本号比较工具 1.0.1  1.0.0
function toNum(a) {
  let c = a.toString().split('.');
  let num_place = ["", "0", "00", "000", "0000"];
  let r = num_place.reverse();
  for (let i = 0; i < c.length; i++) {
    let len = c[i].length;
    c[i] = r[len] + c[i];
  }
  let res = c.join('');
  return res;
}

const needUpgrade = function needUpgrade(supportVersion, currentVersion) {
  let a = toNum(supportVersion);
  let b = toNum(currentVersion);
  if (a >= b) {
    //支持
    return true;
  } else {
    //不支持
    return false;
  }
}

export default needUpgrade;
