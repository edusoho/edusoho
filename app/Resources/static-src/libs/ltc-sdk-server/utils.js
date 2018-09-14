const script = (scripts, fn, target) => {
  if (!scripts.length) return;
  target = !target ? document.getElementsByTagName('body')[0] : target;
  (function callback(s = scripts.shift()) {
    !scripts.length ? loadJs(s, fn, target) : loadJs(s, callback, target);
  }());
}

const loadJs = (path, fn, target) => {
  let elem = document.createElement('script'), loaded;
  elem.onload = elem.onerror = elem.onreadystatechange = () => {
    if ((elem.readyState && !(/^c|loade/.test(elem.readyState))) || loaded) {
      return;
    }
    elem.onload = elem.onreadystatechange = null;
    loaded = 1;
    fn();
  };
  elem.async = 1;
  elem.src = path;
  target.appendChild(elem);
};


export {
  script
}