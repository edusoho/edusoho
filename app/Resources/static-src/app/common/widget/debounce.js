export const debounce = function(fn, delay, isDebounce) {
  let timer;
  let lastCall = 0;
  return function (...args) {
    if (isDebounce) {
      // 抖动
      if (timer) clearTimeout(timer);
      timer = setTimeout(() => {
        fn(...args);
      }, delay);
    } else {
      // 节流
      const now = new Date().getTime();
      if (now - lastCall < delay) return;
      lastCall = now;
      fn(...args);
    }
  };
};