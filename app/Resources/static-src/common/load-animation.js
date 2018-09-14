const loadAnimation = (fn, $element) => {
  let $loading = $element.find('.load-animation');
  if ($loading.length == 0) {
    $loading = $('<div class="load-animation"></div>');
    $loading.prependTo($element).nextAll().hide();
  } else {
    $loading.show();
  }
  let arr = [], l = fn.length;
  return (x) => {
    arr.push(x);
    $loading.hide().nextAll().show();
    /* eslint-disable */
    return arr.length < l ? arguments.callee : fn.apply(null, arr);
    /* eslint-enable */
  };
};

export default loadAnimation;
