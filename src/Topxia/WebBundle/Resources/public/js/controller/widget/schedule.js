define(function(require, exports, module) {
    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    var schedule = Widget.extend({
        sunday: null,
        currentYearMonth: null,
        attrs: {
            saveUrl: null,
            resetUrl: null
        },
        events: {
            "click span.glyphicon-plus-sign": "expand",
            "click span.glyphicon-minus-sign": "collapse",
            "click span.next-week": "nextWeek",
            "click span.previous-week": "previousWeek",
            "click span.next-month": "nextMonth",
            "click span.previous-month": "previousMonth"
        },
        setup: function() {
            this.sunday = this.element.find('tr.day td:eq(0)').data('day');
            this.set('saveUrl', this.element.find('.schedule').data('save'));
            this.set('resetUrl', this.element.find('.schedule').data('reset'));
            this.element.find('tr.yearMonth') && this.changeYearMonth();
            this.bindSortableEvent();
            
        },
        bindSortableEvent: function() {
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
        changeYearMonth: function() {
            var sunday = this.sunday + '';
            var newYearMonth = sunday.substr(0,4) + ' 年' + sunday.substr(4,2) + ' 月';
            this.element.find('span.yearMonth').html(newYearMonth);
            this.currentYearMonth = newYearMonth;
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
        },
        nextWeek: function() {
            var sunday = this.nextSunday(true);
            this.reset({'sunday': sunday,'previewAs':'week'});
        },
        previousWeek: function() {
            var sunday = this.nextSunday(false);
            this.reset({'sunday': sunday,'previewAs':'week'});
        },
        nextMonth: function() {

        },
        previousMonth: function() {

        },
        nextSunday: function(plus) {
            var sunday = this.sunday +'';
            sunday = sunday.substr(0,4) + '/' + sunday.substr(4,2) + '/' + sunday.substr(6,2);
            var offset = plus ? 7 * 24 * 60 * 60 * 1000 : -7 * 24 * 60 * 60 * 1000;
            var nextSunday = new Date(new Date(sunday).getTime() + offset);
            var year = nextSunday.getFullYear();
            var month = nextSunday.getMonth() + 1;
            month = month >= 10 ? month : '0' + month;
            var day = nextSunday.getDate();
            day = day >= 10 ? day : '0' + day;  
            
            sunday = '' + year + month + day;
            this.sunday = sunday;
            return sunday;
            
        },
        reset: function(data) {
            var self = this;
            $.ajax({
                url: this.get('resetUrl'),
                data: data,
                success: function(html) {
                    self.element.find('.schedule tbody').html('').append(html);
                    self.element.find('tr.yearMonth') && self.changeYearMonth();
                    self.bindSortableEvent();
                }
            });  
        }

    });

    module.exports = schedule;
});