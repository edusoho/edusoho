var loadAnimation = function(fn,$element) {
  let _args = [],
    $loding = $('<div class="load-animation"></div>');
  $loding.prependTo($element).nextAll().hide(); 
  return () => {
    if (arguments.length == 0) {
      return fn.apply(this, _args)
    }
    Array.prototype.push.apply(_args, arguments);
    $loding.hide().nextAll().show();
  }
}
export default loadAnimation;
