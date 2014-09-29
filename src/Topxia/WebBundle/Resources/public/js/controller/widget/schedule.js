define(function(require, exports, module) {
    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    var schedule = Widget.extend({
        attrs: {
            saveUrl: null
        },
        events: {
            "click span.glyphicon-plus-sign": "expand",
            "click span.glyphicon-minus-sign": "collapse"
/*            "click": "nextWeek",
            "click": "previousWeek",
            "click": "nextMonth",
            "click": "previousMonth"*/
        },
        setup: function() {
            this.set('saveUrl',this.element.find('.schedule').data('url'));
            var self = this;
            var group = $("ul.lesson-ul").sortable({
                group:'schedule-sort',
                drag:false,
                onDragStart: function (item, container, _super) {
                    // Duplicate items of the no drop area
                    if(!container.options.drop){
                        item.clone().insertAfter(item);
                    }
                    _super(item);
                },
                onDrop: function ($item, container, _super, event) {
                    $item.html("<li data-id='" + $item.data('id') +"'><img src='"+$item.data('icon')+"'><br>"+ $item.data('title') +"</li>");
                    _super($item);

                    var result = self.serializeContainer(container.el);
                    self.save(result);
                }
            });
            $("ul.course-item-list").sortable({
                distance:30,
                group:'schedule-sort',
                pullPlaceholder:false,
                drop:false
            });
        },
        expand: function(e) {
            var target = e.currentTarget;
            $(target).removeClass('glyphicon-plus-sign').addClass('glyphicon-minus-sign');
            $(target).parent().find('.course-item-list-wrap').addClass('show').removeClass('hidden');
            
        },
        collapse: function(e) {
            var target = e.currentTarget;
            $(target).removeClass('glyphicon-minus-sign').addClass('glyphicon-plus-sign');
            $(target).parent().find('.course-item-list-wrap').addClass('hidden').removeClass('show');
        },
        renderTable: function() {

        },
        save: function(data){
            $.post(this.get('saveUrl'), data, function(){

            });
        },
        serializeData: function(object) {
            var result = {};
           for (var i = 0; i < object.length; i++) {
                object[i].children().each(function(index){
                    var one = {
                    id: $(this).data('id'),
                    day: object[i].data('day')
                   }
                   result[index] = one;
                });
           }
           return result;
        },
        serializeContainer: function(element) {
            var result = {
                day:element.data('day')
            };
            var ids = '';
            element.children().each(function(){
                ids += $(this).data('id') + ',';
            });
            result.ids = ids.substr(0,ids.length - 1);
            return result;
        }

    });

    module.exports = schedule;
});