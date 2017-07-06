import TaskDetail from './task-detail';
import FinishedRateChart from './finished-rate-chart';

class CourseDashboard{
    constructor() {
      this.courseId = $('.js-course-statictics-dashboard').data('courseId');

        this.init();
        this.timeSelectEvent();
        this.tabToggle();
        this.charts();
    }

    init(){
        this.$timeSlectBtn = $('.is-date-change');
    }

    timeSelectEvent(){
        this.$timeSlectBtn.on('click', function() {
            let type = $(this).data('type');
            let time = $(this).data('time');
            $.post(url, {
                type: type,
                time: time
            }).done(() => {
                console.log('success');
            }).fail(() => {
                console.log('error');
            })
        });
    }

    tabToggle(){
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            e.target // 激活的标签页
            let $target = $(e.target);
            let $content = $($target.attr('href'));
            $content.trigger('init');
        })
    }

    charts(){
        let self = this;
        $('#task-data-detail').on('init', function(){
            if (self.taskDetail) return;
            self.taskDetail = new TaskDetail();
            self.taskDetail.show();
        });

      let finishedRateChart = new FinishedRateChart('finish-rate-chart', 3);
        finishedRateChart.show(this.courseId, '2016-06-01', '2017-06-30');
    }
}

let courseDashboard = new CourseDashboard()
