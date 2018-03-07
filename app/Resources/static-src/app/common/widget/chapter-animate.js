export let toggleIcon = (target, $expandIconClass, $putIconClass) => {
  let $icon = target.find('.js-remove-icon');
  let $text = target.find('.js-remove-text');
  if ($icon.hasClass($expandIconClass)) {
    $icon.removeClass($expandIconClass).addClass($putIconClass);
    $text ? $text.text(Translator.trans('收起')): '';
  } else {
    $icon.removeClass($putIconClass).addClass($expandIconClass);
    $text ? $text.text(Translator.trans('展开')): '';
  }
};

export let chapterAnimate = (
  delegateTarget = 'body',
  target = '.js-task-chapter',
  $expandIconClass = 'es-icon-remove',
  $putIconClass = 'es-icon-anonymous-iconfont') => {
  $(delegateTarget).on('click', target, (event) => {
    let $this = $(event.currentTarget);
    $this.nextUntil(target).animate({ height: 'toggle', opacity: 'toggle' }, 'normal');
    toggleIcon($this, $expandIconClass, $putIconClass);
  });
};