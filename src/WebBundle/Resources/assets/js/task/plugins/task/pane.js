class TaskPane {
  constructor(option) {
    this.plugin= option.plugin;
    this.toolbar = this.plugin.toolbar;
    this.$element = option.element;
    this.init();
  }

  init() {
    console.log(this.$element);
    this.$element.perfectScrollbar();
    $.get('http://www.esdev.com/lessonplugin/lesson/list', {
          courseId:toolbar.courseId,
    },html=> {
      console.log(html);
      console.log("123");
      // that.element.html(html).show();
      // that._setLessonItemActive(toolbar.get('lessonId'));

      // var lessons = [];
      // that.element.find('.lesson-item').each(function(index, item) {
      //       var $item = $(item);
      //       lessons.push(parseInt($item.data('id')));
      // });
      // toolbar.setLessons(lessons);
      // var num=$('.lesson-item-'+toolbar.get('lessonId')).data('num')-5;
      // $('.course-item-list-in-toolbar-pane').perfectScrollbar({wheelSpeed:50});
      // $(".course-item-list-in-toolbar-pane").scrollTop(num*30);
      // $(".course-item-list-in-toolbar-pane").perfectScrollbar('update');
    });
  }

  show() {
    this.toolbar.showPane(this.plugin.code);
  }

}
export default TaskPane;
