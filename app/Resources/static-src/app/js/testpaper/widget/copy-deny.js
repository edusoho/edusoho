class CopyDeny {
  constructor($element = $('html')) {
    $element.attr('unselectable', 'on')
      .css('user-select', 'none')
      .on('selectstart', false);
  }
}

export default CopyDeny;