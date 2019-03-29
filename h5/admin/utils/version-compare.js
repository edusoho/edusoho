// 版本号比较工具 1.0.1  1.0.0
const needUpgrade = (supportVersion, currentVersion) => {
  const supportVArray = supportVersion.split('.')
  const currentVArray = currentVersion.split('.')
  const supportVLength = supportVArray.length
  const currentVLength = currentVArray.length
  const length = Math.min(supportVLength, currentVLength)
  for (let i = 0; i < length; i++) {
    if (supportVArray[i] > currentVArray[i]) return true
    if (supportVArray[i] < currentVArray[i]) return false
  }
  return currentVLength < supportVLength
}

export default needUpgrade;
