export let chapterAnimate = ()=> {
  $(".js-task-chapter").click(function(){
    $(this).nextUntil(".js-task-chapter").animate({height: 'toggle', opacity: 'toggle'}, "normal");
    let $icon = $(this).children('.js-remove-icon');
    if ($icon.hasClass('es-icon-remove')) {
        $icon.removeClass('es-icon-remove').addClass('es-icon-anonymous-iconfont');
    } else {
        $icon.removeClass('es-icon-anonymous-iconfont').addClass('es-icon-remove');
    }
  });
}