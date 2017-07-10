
import StudentTrendency from './student-trendency';
import TaskDetail from './task-detail';

class CourseDashboard{
    constructor() {
        this.init();
        this.timeSelectEvent();
        this.charts();
    }

    init(){
        this.$timeSlectBtn = $('.is-date-change');
        new StudentTrendency();
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

    charts(){
        this._taskDetailChart();
    }

    _taskDetailChart(){
        let self = this;

        if (self.taskDetail) return;

        let $taskChart = $('#task-data-chart');
        self.taskDetail = new TaskDetail($taskChart);

    }
}

let courseDashboard = new CourseDashboard();