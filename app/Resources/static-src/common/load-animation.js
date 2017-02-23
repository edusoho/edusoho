const loadAnimation = (fn, $element) => {
  let $loading = $('<div class="load-animation"></div>');
  $loading.prependTo($element).nextAll().hide();
  $element.append();
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
