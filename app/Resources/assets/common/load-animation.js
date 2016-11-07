let loadAnimation = (fn,$element)=> {
  let $loding = $('<div class="load-animation"></div>');
  $element.children().hide(); 
  $element.append($loding);
  var arr=[],
    l = fn.length;
  return (x) => { 
    arr.push(x);
    $loding.remove();
    $element.children().show(); 
    return arr.length < l ? arguments.callee : fn.apply(null,arr);
  }
}


export default loadAnimation;