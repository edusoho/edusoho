const debounce = (fn, wait) => {
  let timeout = null; // 使用闭包，缓存变量
  return function() {
    if (timeout !== null) {
      console.log('清除定时器啦');
      clearTimeout(timeout); // 清除这个定时器
    }
    timeout = setTimeout(fn, wait);
  };
};
const throttle = (func, delay) => {
  console.log(func);
  let timer = null; // 使用闭包，缓存变量
  let prev = Date.now(); // 最开始进入滚动的时间
  return function() {
    const context = this; // this指向window
    const args = arguments;
    const now = Date.now();
    const remain = delay - (now - prev); // 剩余时间
    clearTimeout(timer);
    // 如果剩余时间小于0，就立刻执行
    if (remain <= 0) {
      func.apply(context, args);
      prev = Date.now();
    } else {
      timer = setTimeout(func, remain);
    }
  };
};
const GetUrlParam = paraName => {
  const url = document.location.toString();
  const arrObj = url.split('?');
  if (arrObj.length > 1) {
    const arrPara = arrObj[1].split('&');
    let arr;
    for (let i = 0; i < arrPara.length; i += 1) {
      arr = arrPara[i].split('=');
      if (arr != null && arr[0] === paraName) {
        return arr[1];
      }
    }
    return '';
  }
  return '';
};

export { debounce, throttle, GetUrlParam };
