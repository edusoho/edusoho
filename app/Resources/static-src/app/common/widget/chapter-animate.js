export let chapterAnimate = (delegateTarget = 'body', target = '.js-task-chapter') => {
  $(delegateTarget).on('click', target, (event) => {
    let $this = $(event.currentTarget);
    console.log(event);
    $this.nextUntil(".js-task-chapter").animate({ height: 'toggle', opacity: 'toggle' }, "normal");
    let $icon = $this.children('.js-remove-icon');
    if ($icon.hasClass('es-icon-remove')) {
      $icon.removeClass('es-icon-remove').addClass('es-icon-anonymous-iconfont');
    } else {
      $icon.removeClass('es-icon-anonymous-iconfont').addClass('es-icon-remove');
    }
  })
}