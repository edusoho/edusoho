function TaskShowaction () {
  $(".js-task-chapter").click(function(){
    $(this).nextUntil(".js-task-chapter").animate({height: 'toggle', opacity: 'toggle'}, "normal");
    var $icon = $(this).children('.js-remove-icon');
    console.log($icon);
    if ($icon.hasClass('es-icon-remove')) {
        $icon.removeClass('es-icon-remove').addClass('es-icon-anonymous-iconfont');
    } else {
        $icon.removeClass('es-icon-anonymous-iconfont').addClass('es-icon-remove');
    }
  });
}
export default TaskShowaction;