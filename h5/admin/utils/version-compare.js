// 版本号比较工具 1.0.1  1.0.0
const needUpgrade = (supportVersion, currentVersion) => {
  if (!supportVersion || !currentVersion) return false;

  const index1 = supportVersion.indexOf('.');
  const index2 = currentVersion.indexOf('.');

  if (index1 === -1 && index2 === -1) return supportVersion > currentVersion;

  const num1 = supportVersion.slice(0, index1);
  const num2 = currentVersion.slice(0, index2);
  const remain1 = supportVersion.slice(index1 + 1);
  const remain2 = currentVersion.slice(index2 + 1);

  if (num1 !== num2) return num1 > num2;

  return remain1 && remain2 ? needUpgrade(remain1, remain2) : true;
};

export default needUpgrade;
