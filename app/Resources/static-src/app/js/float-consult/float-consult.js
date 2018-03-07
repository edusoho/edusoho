import { Browser } from 'common/utils';

export const floatConsult = ($element, popoverBtnClass) => {

  const marginTop = (0 - $element.height() / 2) + 'px';

  if (Browser.ie10 || Browser.ie11 || Browser.edge) {
    $element.css({'margin-right': '16px'});
  }

  $element.css({'margin-top': marginTop, 'visibility': 'visible'});

  if (!$element.length) {
    return;
  }

  if ($element.data('display') === 'off') {
    return;
  }

  $element.find(popoverBtnClass).popover({
    placement: 'left',
    trigger: 'hover',
    html: true,
    content: function() {
      return $($(this).data('contentElement')).html();
    }
  });
};