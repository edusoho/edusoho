import { Browser } from 'common/utils';

class FloatConsult {
  constructor(prop) {
    this.ele = $(prop.ele);
    this.init();
  }

  init() {
    const $element = this.ele;
    const marginTop = (0 - $element.height() / 2) + 'px';

    if (!$element.length) {
      return;
    }

    if ($element.data('display') === 'off') {
      return;
    }

    $element.css({'margin-top': marginTop, 'visibility': 'visible'});

    if (Browser.ie10 || Browser.ie11 || Browser.edge) {
      $element.css('margin-right': '16px');
    }

    $element.find('.btn-group-vertical .btn').popover({
      placement: 'left',
      trigger: 'hover',
      html: true,
      content: function() {
        return $($(this).data('contentElement')).html();
      }
    });
  }
}

export default FloatConsult;