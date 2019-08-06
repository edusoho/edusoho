// 版本号比较工具 1.0.1  1.0.0
function toNum(a) {
  const c = a.toString().split('.');
  const num_place = ['', '0', '00', '000', '0000'];
  const r = num_place.reverse();
  for (let i = 0; i < c.length; i++) {
    const len = c[i].length;
    c[i] = r[len] + c[i];
  }
  const res = c.join('');
  return res;
}

const needUpgrade = function needUpgrade(supportVersion, currentVersion) {
  const a = toNum(supportVersion); 
  const b = toNum(currentVersion);
  if (a <= b) {
    // 支持
    return true;
  }
  // 不支持
  return false;
};

export default needUpgrade;
