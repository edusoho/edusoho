define(function(require, exports, module) {
    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    var schedule = Widget.extend({
        sunday: null,
        year: null,
        month: null,
        daysInMonth: [31,28,31,30,31,30,31,31,30,31,30,31],
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
            "click span.previous-month": "previousMonth",
            "click button.lesson-remove": "removeLesson",
            "change select.viewType": "changeView"
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
            $("ul.course-item-list").each(function(){
                $(this).sortable("enable");
            });
            var lessonSort = $("ul.lesson-ul").sortable({
                group:'schedule-sort',
                drag:false,
                itemSelector:'.lesson-item',
                onDragStart: function (item, container, _super) {
                    // Duplicate items of the no drop area
                    if(!container.options.drop){
                        item.clone().insertAfter(item);
                    }
                    _super(item);
                },
                onDrop: function ($item, container, _super, event) {
                    var $li = $('<li></li>'),
                        img = '<img src="'+ $item.data('icon') +'"><br>'+ $item.data('title') +'</img>',
                        close = '<button type="button" class="close pull-right lesson-remove"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>';

                    $li.data('id', $item.data('id')).append(close).append(img);

                    $item.prop('outerHTML', $li.prop("outerHTML"));
                    _super($item);

                    var result = self.serializeContainer(container.el);
                    self.save(result);
                }
            });
            var courseSort = $("ul.course-item-list").sortable({
                distance:30,
                group:'schedule-sort',
                pullPlaceholder:false,
                drop:false
            });
        },
        //not work for sortable delegate,
        //find reason, beacause object is not only one.
        //will use this method replace bindSortableEvent in future.
        refreshSortableEvent: function() {
            $("ul.lesson-ul").sortable('refresh');
            $("ul.course-item-list").sortable('refresh');
        },
        changeYearMonth: function() {
            var sunday = this.sunday + '';
            var newYearMonth = sunday.substr(0,4) + ' 年' + sunday.substr(4,2) + ' 月';
            this.element.find('span.yearMonth').html(newYearMonth);
            this.year = sunday.substr(0,4);
            this.month = sunday.substr(4,2);
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
        getDaysInMonth: function(month,year) {
            if ((month==1)&&(year%4==0)&&((year%100!=0)||(year%400==0))){
                return 29;
            }else{
                return this.daysInMonth[month];
            }
        },
        getWeekByDate: function(year,month,day) {
            return new Date(year + '/' + month + '/' + day).getDay();
        },
        renderTable: function() {
            var year = parseInt(this.year);
            var month =  parseInt(this.month);
            var days = this.getDaysInMonth(month - 1, year);
            var $tbody = this.element.find('.schedule tbody');
            var newtr = "<tr><td class='t-1-0 not-in-month'></td><td class='t-1-1 not-in-month'></td><td class='t-1-2 not-in-month'></td><td class='t-1-3 not-in-month'></td><td class='t-1-4 not-in-month'></td><td class='t-1-5 not-in-month'></td><td class='t-1-6 not-in-month'></td></tr>";
            
            this.element.find('.schedule-body span.glyphicon').addClass('hidden');
            this.element.find('table').addClass('col-md-12').removeClass('col-md-10').addClass('month').removeClass('week');
            this.element.find('span.yearMonth').html(this.year + ' 年' + this.month + ' 月');
            $tbody.html('');
            $tbody.append(newtr);
            var row = 1;
            var queryDate = [], i = 0;

            for(var day = 1; day <= days; day++)
            {
                var week = this.getWeekByDate(year, month, day);
                var date = '' + year + (month >= 10 ? month : '0' + month) + (day >= 10 ? day : '0' + day);
                queryDate[i++] = date;
                $tbody.find(".t-" + row + '-' + week).addClass(date).removeClass('not-in-month').html(day);
                //$tbody.find(".t-" + row + '-' + week).addClass('d-' + day);
                if(week == 6 && day != days) {
                    row++;
                    newtr = '<tr><td class="t-' + row + '-0 not-in-month"></td><td class="t-' + row + '-1 not-in-month"></td><td class="t-' + row + '-2 not-in-month"></td><td class="t-' + row + '-3 not-in-month"></td><td class="t-' + row + '-4 not-in-month"></td><td class="t-' + row + '-5 not-in-month"></td><td class="t-' + row + '-6 not-in-month"></td></tr>';
                    $tbody.append(newtr);
                }
            }

            var pMonth = month - 1 == 0 ? 12 : month -1,
            pYear = pMonth == 12 ? year -1 : year,
            nMonth = month + 1 == 13 ? 1 : month + 1,
            nYear = nMonth == 1 ? year + 1 : year,
            pDays = this.getDaysInMonth(pMonth - 1, pYear),
            nDays = this.getDaysInMonth(nMonth - 1, nYear),
            plength = $tbody.find('tr:first .not-in-month').length,
            nlength =$tbody.find('tr:first .not-in-month').length;

            $tbody.find('tr:first .not-in-month').each(function(index){
                var day = pDays - plength + index + 1;
                var date = '' + pYear + (pMonth>=10?pMonth:'0'+pMonth) + (day>=10?day:'0'+day);
                queryDate[i++] = date;
                $(this).addClass(date).html(day);
            });
            $tbody.find('tr:last .not-in-month').each(function(index){
                var day = index + 1;
                var date = '' + nYear + (nMonth>=10?nMonth:'0'+nMonth) + (day>=10?day:'0'+day);
                queryDate[i++] = date;
                $(this).addClass(date).html(day);
            });

            this.disableSort();
            this.ajaxRenderTable(queryDate);
            this.popover();
        },
        save: function(data){
            $.post(this.get('saveUrl'), data, function(){

            });
        },
        removeLesson: function(e) {
            var $button = $(e.currentTarget),
                $li = $button.parent(),
                $ul = $li.parent();
            $li.remove();
            var result = this.serializeContainer($ul);
            this.save(result);
        },
        disableSort: function() {
            $("ul.course-item-list").each(function(){
                $(this).sortable("disable");
            });
        },
        ajaxRenderTable: function(queryDate) {
            var self = this;
            $.ajax({
                url: this.get('resetUrl'),
                data: {'previewAs':'month', 'date':queryDate},
                success: function(result) {
                    var schedules = result.schedules,
                        courses = result.courses,
                        lessons = result.lessons,
                        teachers = result.teachers;
                    for(var date in schedules) {
                        var count = schedules[date].length,
                        countSpan = "<span class='count'>("+ count+")</span>";
                        self.element.find('tr .'+date).append(countSpan);

                        var $warpper = $("<div class='hidden popover-content'></div>");
                        var $ul = $("<ul class='media-list'></ul>");
                        $warpper.append($ul);
                        var schedule = schedules[date];
                        for (var i = schedule.length - 1; i >= 0; i--) {
                            var $li = $("<li class='media'></li>");
                            var $a = $("<a class='pull-left' href=''></a>");
                            var $img = $('<img class="media-object" src="" alt="">');
                            var $div = $('<div class="media-body"></div>');
                            var $title = $('<h4 class="media-heading"></h4>');
                            var $name = $('<span></span>');
                            $img.attr("src", courses[lessons[schedule[i].lessonId].courseId].middlePicture);
                            $title.html(lessons[schedule[i].lessonId].title);
                            $name.html(teachers[courses[lessons[schedule[i].lessonId].courseId].teacherIds[0]].truename);
                            
                            $a.append($img);
                            $div.append($title).append($name);
                            $li.append($a).append($div);
                            $ul.append($li);
                        };
                        self.element.find('tr .'+date).append($warpper);
                        
                    }
                }
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
        changeView: function(e) {
            $(e.currentTarget).val() == 'week' ? this.reset({'sunday': this.sunday,'previewAs':'week'}) : this.renderTable(); 
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
            var cYear = parseInt(this.year),
            cMonth =  parseInt(this.month),
            nextMonth = cMonth + 1 == 13 ? 1: cMonth + 1,
            nextYear = nextMonth == 1 ? cYear + 1 : cYear;
            this.year = nextYear;
            this.month = nextMonth>=10 ? nextMonth:'0'+nextMonth;
            this.renderTable();
        },
        previousMonth: function() {
            var cYear = parseInt(this.year),
            cMonth =  parseInt(this.month),
            previousMonth = cMonth - 1 == 0 ? 12 : cMonth - 1,
            previousYear = previousMonth == 12 ? cYear - 1 : cYear;
            this.year = previousYear;
            this.month = previousMonth>=10 ? previousMonth:'0'+previousMonth;
            this.renderTable();
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
                    self.element.find('.schedule-body').html('').append(html);
                    self.element.find('tr.yearMonth') && self.changeYearMonth();
                    self.bindSortableEvent();
                }
            });  
        },
        popover: function() {
            $('.schedule tbody').popover({
                selector: 'td',
                trigger: 'hover',
                placement: 'auto',
                html: true,
                delay: 200,
                content: function() {
                    return $(this).find('.popover-content').html();
                },
            });
        }

    });

    module.exports = schedule;
});