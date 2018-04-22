let params = {
  right: 0,
  top: 0,
  currentX: 0,
  currentY: 0,
  flag: false,
  currentRight: 0,
  currentTop: 0
};

const getCss = (o, key) => {
  return o.currentStyle ? o.currentStyle[key] : document.defaultView.getComputedStyle(o, false)[key];
};

const setCss = (o, key, value) => {
  o.style[key] = value;
};

// 获取位置
const getLocation = (target) => {
  if (getCss(target, 'right') !== 'auto') {
    params.right = getCss(target, 'right');
  }
};

//拖拽的实现
const drag = (bar, target, callback) => {
  getLocation(target);

  //o是移动对象
  bar.onmousedown = function (event) {
    params.flag = true;
    let e = event;
    params.currentX = e.clientX;
    params.currentY = e.clientY;
  };

  document.onmouseup = function () {
    if (!params.flag) {
      return;
    }
    setCss(target, 'cursor', 'pointer');
    params.flag = false;
    getLocation(target);

    if (typeof callback == 'function' && params.currentRight) {
      callback(params.currentRight, params.currentTop);
    }
  };

  document.onmousemove = function (event) {
    if (params.flag) {
      let e = event;
      e.preventDefault();

      let nowX = e.clientX;
      let nowY = e.clientY;
      let disX = nowX - params.currentX;
      let disY = nowY - params.currentY;
      let rightNum = parseInt(params.right) - disX;
      const width = target.parentNode.offsetWidth - target.offsetWidth;
      if (rightNum <= 0) {
        rightNum = 0;
      } 
      if (rightNum >= width) {
        rightNum = width;
      }

      let right = rightNum + 'px';
      setCss(target, 'right', right);
      setCss(target, 'cursor', 'move');
      params.currentRight = right;
    }
  };
};

export default drag;