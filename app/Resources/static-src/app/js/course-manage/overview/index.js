import StudentTrendency from './student-trendency';
import TaskDetail from './task-detail';
import FinishedRateTrend from './finished-rate-trend';

class CourseDashboard{
    constructor() {
        this.init();
        this.tabToggle();
        this.charts();
    }

    init(){
      new StudentTrendency();
      new FinishedRateTrend()
    }

    tabToggle(){
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            let $target = $(e.target);
            let $content = $($target.attr('href'));
            $content.trigger('init');
        })
    }

    charts(){
        let self = this;
        $('#task-data-detail').on('init', function(){
            if (self.taskDetail) return;
            self.taskDetail = new TaskDetail($('#task-data-chart'));
        })

    }
}

let courseDashboard = new CourseDashboard();