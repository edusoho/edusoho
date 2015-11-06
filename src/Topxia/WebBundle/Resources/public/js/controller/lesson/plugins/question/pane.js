define(function(require, exports, module) {

    var Widget = require('widget'),
    Validator = require('bootstrap.validator'),
    ThreadShowWidget = require('../../../course-thread/show-widget');
    require('jquery.perfect-scrollbar');

    var QuestionPane = Widget.extend({
        _dataInitialized: false,
        attrs: {},
        events: {
            'click .show-question-item' : 'showItem',
            'click .back-to-list' : 'backToList'
        },
        setup: function() {
            this.get('plugin').toolbar.on('change:lessonId', function(id) {
            });
        },
        showList: function() {
            var pane = this,
                toolbar = pane.get('plugin').toolbar;

            // if (!pane._dataInitialized) {
                $.get(pane.get('plugin').api.init, {courseId:toolbar.get('courseId'), lessonId:toolbar.get('lessonId')}, function(html) {
                    pane._dataInitialized = true;
                    pane.element.html(html);
                    pane._showListPane();
                    pane._showWidget = new ThreadShowWidget({
                        element: pane.$('[data-role=show-pane]')
                    });
                });
                
            // } else {
            //     pane._showListPane();
            // }
        },
        show: function() {
          this.get('plugin').toolbar.showPane(this.get('plugin').code);
          this.showList();
        },
        showItem: function(e) {
            var pane = this,
                toolbar = pane.get('plugin').toolbar,
                $thread = $(e.currentTarget);
            
            $.get(pane.get('plugin').api.show, {courseId:toolbar.get('courseId'), id:$thread.data('id')}, function(html) {
                pane._showItemPane().html(html);
                pane._showWidget.trigger('reload');
                $('[data-role=marker-time]').click(function(){
                    $("#lesson-video-content").trigger("onMarkerTimeClick",$(this).data("marker-time"));
                });
            });
        },
        backToList: function(e) {
            this.showList();
        },
        _showListPane: function() {
            this.$('[data-role=show-pane]').hide();
            this.$('[data-role=list-pane]').show();
            $('[data-role=marker-time]').click(function(){
                $("#lesson-video-content").trigger("onMarkerTimeClick",$(this).data("marker-time"));
            });
            this.element.find('.question-list-pane').perfectScrollbar({wheelSpeed:50});
            return this.$('[data-role=list-pane]');
        },
        _showItemPane: function() {
            this.$('[data-role=list-pane]').hide();
            return this.$('[data-role=show-pane]').show();
        }
    });

    

    module.exports = QuestionPane;

});