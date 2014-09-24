define(function(require, exports, module) {
    var Widget = require('widget');
    var Notify = require('common/bootstrap-notify');

    var UserSign = Widget.extend({
    	attrs: {
    		daysInMonth: [31,28,31,30,31,30,31,31,30,31,30,31],
            selectedDate: null,
            inited: false,
            signedRecords: [],
            signedRecordsUrl:null,
            signUrl:null
    	},
        events: {
            "click [data-role=sign]": "sign",
            "mouseenter [data-role=signed]": "signedIn",
            "mouseleave [data-role=signed]": "signedOut",
            "mouseenter .class_sign_main": "keep",
            "mouseleave .class_sign_main": "remove",
            "click [data-role=previous]": "previousMonth",
            "click [data-role=next]": "nextMonth"
        },
        setup: function() {
            var selectedDate = this.element.find('#title-month').data('time');
            var signedRecordsUrl = this.element.data('records');
            var signUrl = this.element.data('signurl');
            this.set('signedRecordsUrl', signedRecordsUrl);
            this.set('signUrl', signUrl);
            this.set('selectedDate', selectedDate);
        },
        keep: function() {
            this.element.find('.class_sign_main').addClass('keepShow');
        },
        remove: function() {
            this.element.find('.class_sign_main').removeClass('keepShow');
            this.hiddenSignTable();
        },
        getDaysInMonth: function(month,year) {
            if ((month==1)&&(year%4==0)&&((year%100!=0)||(year%400==0))){
                return 29;
            }else{
                return this.get('daysInMonth')[month];
            }
        },
        getWeekByDate: function(year,month,day) {
            return new Date(year + '/' + month + '/' + day).getDay();
        },
        sign: function() {
            var self = this;
            var today = new Date().getDate();
            $.ajax({
                url:this.get('signUrl'),
                dataType: 'json',
                success: function(data){
                    self.showSignTable();
                    self.initTable(true);
                    self.element.find("td:contains("+  today +")").addClass('signed_anime_day');
                },
                error: function(xhr){
                    if(xhr.status == 404) {
                        alert('非该班成员不能签到');
                    }else{
                        alert('签到异常');
                    }
                }
            });
        },
        signedIn: function() {
            if(!this.get('inited')) {
                this.initTable();
            }
            this.showSignTable();
        },
        signedOut: function() {
            var self = this;
            setTimeout(function(){
                if(self.element.find('.class_sign_main').hasClass('keepShow')) {
                    return;
                }else{
                    self.hiddenSignTable();
                }
            }, 1000);
        },
        showSignTable: function() {
            this.element.find('.class_sign_main').attr('style','display:block');
        },
        hiddenSignTable: function() {
            this.element.find('.class_sign_main').attr('style','display:none');
        },
        initTable: function(signedToday) {
            var selectedDate = this.get('selectedDate');
            selectedDate = selectedDate.split('/');
            var year = parseInt(selectedDate[0]);
            var month =  parseInt(selectedDate[1]);
            var days = this.getDaysInMonth(month - 1, year);
            var $tbody = this.element.find('tbody');
            var newtr = "<tr><td class='t-1-0'></td><td class='t-1-1'></td><td class='t-1-2'></td><td class='t-1-3'></td><td class='t-1-4'></td><td class='t-1-5'></td><td class='t-1-6'></td></tr>";

            var self = this;
            var url = this.get('signedRecordsUrl') + '?startDay=' + month+'-1-'+year + '&endDay='+ month+'-'+days+'-'+year;
          
            $tbody.append(newtr);
            var row = 1;
            var today = new Date().getDate();
            for(var day = 1; day <= days; day++)
            {
                var week = this.getWeekByDate(year, month, day);
                $tbody.find(".t-" + row + '-' + week).html(day);
                $tbody.find(".t-" + row + '-' + week).addClass('d-' + day);
              
                if(week == 6 && day != days) {
                    row++;
                    newtr = '<tr><td class="t-' + row + '-0"></td><td class="t-' + row + '-1"></td><td class="t-' + row + '-2"></td><td class="t-' + row + '-3"></td><td class="t-' + row + '-4"></td><td class="t-' + row + '-5"></td><td class="t-' + row + '-6"></td></tr>';
                    $tbody.append(newtr);
                }
            }

            $.ajax({
                url:url,
                dataType: 'json',
                async:true,//(默认: true) 默认设置下，所有请求均为异步请求。如果需要发送同步请求，请将此选项设置为 false。注意，同步请求将锁住浏览器，用户其它操作必须等待请求完成才可以执行。
                success: function(data){
                    self.set('signedRecords',data.records);
                    self.set('todayRank', data.todayRank);
                    self.set('signedNum', data.signedNum);
                    self.set('keepDays', data.keepDays);
                    for(var i=0;i<data.records.length;i++){ 
                        $tbody.find(".d-" + data.records[i]).addClass('signed_day');
                    }
                    self.element.find('#todayRank').html(data.todayRank);
                    self.element.find('#signedNum').html(data.signedNum);
                    self.element.find('#keepDays').html(data.keepDays);
                }
            });

            this.set('inited',true);
            if(signedToday) {
                var $signbtn = this.element.find('[data-role=sign]');
                $signbtn.data('role', 'signed');
                var self = this;
                $signbtn.on('mouseenter',function(){
                    self.signedIn();
                });
                $signbtn.on('mouseleave',function(){
                    self.signedOut();
                });
                $signbtn.on('click',false);
                $signbtn.html('已签到');
            }
          
        },
        previousMonth: function() {
            var currentDate = this.get('selectedDate');
            currentDate = currentDate.split('/');
            var currentYear = parseInt(currentDate[0]);
            var currentMonth =  parseInt(currentDate[1]);
            var nextMonth = 0;
            var nextYear = currentYear;
            if(currentMonth == 1) {
                nextMonth = 12
                nextYear = currentYear - 1;
            } else {
                nextMonth = currentMonth - 1;
            }
            this.set('selectedDate', nextYear + '/' + nextMonth);
            this.element.find('tbody').html('');
            this.element.find('#title-month').html(nextYear + '年' + nextMonth + '月');
            this.initTable();
        },
        nextMonth: function(){
            var currentDate = this.get('selectedDate');
            currentDate = currentDate.split('/');
            var currentYear = parseInt(currentDate[0]);
            var currentMonth =  parseInt(currentDate[1]);
            var nextMonth = 0;
            var nextYear = currentYear;
            if(currentMonth == (new Date().getMonth() + 1 ) && currentYear == (new Date().getFullYear())) {
                return;
            } else if(currentMonth == 12 ) {
                nextMonth = 1;
                nextYear =currentYear +1;
            } else {
                nextMonth = currentMonth + 1;
            }
            this.set('selectedDate', nextYear + '/' + nextMonth);
            this.element.find('tbody').html('');
            this.element.find('#title-month').html(nextYear + '年' + nextMonth + '月');
            this.initTable();
        }

    });

    module.exports = UserSign;

});


