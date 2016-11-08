let loadAnimation = (fn,$element) => {
  let $loding = $('<div class="load-animation"></div>');
  $loding.prependTo($element).nextAll().hide();
  $element.append();
  var arr=[],
    l = fn.length;
  return (x) => { 
    arr.push(x);
    $loding.hide().nextAll().show(); 
    return arr.length < l ? arguments.callee : fn.apply(null,arr);
  }
}

export default loadAnimation;