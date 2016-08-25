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
                        var $itemStatusIcon = $item.find('.status-icon');
                        var status = data.status == 'learning' ? 'doing' : 'done1';

                        var $itemCourseTypeIcon = $item.find('.course-type').find('.es-icon');

                        $itemCourseTypeIcon.removeClass('es-icon-lock');

                        if (data.type == 'video') {
                            $itemCourseTypeIcon.addClass('es-icon-videoclass');
                        } else if (data.type == 'audio') {
                            $itemCourseTypeIcon.addClass('es-icon-audioclass');
                        } else if (data.type == 'text') {
                            $itemCourseTypeIcon.addClass('es-icon-graphicclass');
                        } else if (data.type == 'testpaper') {
                            $itemCourseTypeIcon.addClass('es-icon-check');
                        } else if (data.type == 'ppt') {
                            $itemCourseTypeIcon.addClass('es-icon-pptclass');
                        } else if (data.type == 'document') {
                            $itemCourseTypeIcon.addClass('es-icon-description');
                        } else if (data.type == 'flash') {
                            $itemCourseTypeIcon.addClass('es-icon-flashclass');
                        } else {
                            $itemCourseTypeIcon.addClass('es-icon-videocam');
                        }

                        if (status == 'done1') {
                            var $nextItem = $item.next();

                            var $nextItemCourseTypeIcon = $nextItem.find('.course-type').find('.es-icon');

                            $nextItemCourseTypeIcon.removeClass('es-icon-lock');

                            if (data.type == 'video') {
                                $nextItemCourseTypeIcon.addClass('es-icon-videoclass');
                            } else if (data.type == 'audio') {
                                $nextItemCourseTypeIcon.addClass('es-icon-audioclass');
                            } else if (data.type == 'text') {
                                $nextItemCourseTypeIcon.addClass('es-icon-graphicclass');
                            } else if (data.type == 'testpaper') {
                                $nextItemCourseTypeIcon.addClass('es-icon-check');
                            } else if (data.type == 'ppt') {
                                $nextItemCourseTypeIcon.addClass('es-icon-pptclass');
                            } else if (data.type == 'document') {
                                $nextItemCourseTypeIcon.addClass('es-icon-description');
                            } else if (data.type == 'flash') {
                                $nextItemCourseTypeIcon.addClass('es-icon-flashclass');
                            } else {
                                $nextItemCourseTypeIcon.addClass('es-icon-videocam');
                            }
                        }
                        
                        $itemStatusIcon.removeClass('es-icon-doing').removeClass('es-icon-done1')
                                    .removeClass('es-icon-undone').removeClass('color-primary');
                        $itemStatusIcon.addClass('color-primary').addClass('es-icon-'+status);
                  });
            },

            show: function() {
                  this.get('toolbar').showPane(this.get('plugin').code);
            },

            _setLessonItemActive: function(lessonId) {
                  $("#course-item-list").find('.lesson-item').removeClass('item-active');
                  $("#course-item-list").find('.lesson-item-' + lessonId).addClass('item-active');
            }
      });

      module.exports = LessonPane;

});