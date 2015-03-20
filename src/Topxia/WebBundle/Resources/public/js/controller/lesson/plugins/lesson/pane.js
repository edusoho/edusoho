define(function(require, exports, module) {

      var Widget = require('widget');

      require('jquery.perfect-scrollbar')

      var LessonPane = Widget.extend({

            _dataInitialized: false,

            setup: function() {
                  var that = this,
                        toolbar = this.get('toolbar');

                  if (this._dataInitialized) {
                        return;
                  }

                  $.get(this.get('plugin').api.list, {
                        courseId: toolbar.get('courseId')
                  }, function(html) {
                        that.element.html(html).show();
                        that._setLessonItemActive(toolbar.get('lessonId'));

                        var lessons = [];
                        that.element.find('.lesson-item').each(function(index, item) {
                              var $item = $(item);
                              lessons.push(parseInt($item.data('id')));
                        });
                        toolbar.setLessons(lessons);
                        var num=$('.lesson-item-'+toolbar.get('lessonId')).data('num')-5;
                        $('.course-item-list-in-toolbar-pane').perfectScrollbar({wheelSpeed:50});
                        $(".course-item-list-in-toolbar-pane").scrollTop(num*30);
                        $(".course-item-list-in-toolbar-pane").perfectScrollbar('update');

                  });

                  toolbar.on('change:lessonId', function(lessonId) {
                        that._setLessonItemActive(lessonId);
                  });

                  toolbar.on('learnStatusChange', function(data) {
                        var $item = $("#course-item-list").find('.lesson-item-' + data.lessonId);
                        $item.removeClass('lesson-item-learning').removeClass('lesson-item-finished');
                        $item.addClass('lesson-item-' + data.status);
                  });
            },

            show: function() {
                  this.get('toolbar').showPane(this.get('plugin').code);
            },

            _setLessonItemActive: function(lessonId) {
                  $("#course-item-list").find('.item').removeClass('item-active');
                  $("#course-item-list").find('.lesson-item-' + lessonId).addClass('item-active');
            }
      });

      module.exports = LessonPane;

});