import StudentTrendency from './student-trendency';
import TaskDetail from './task-detail';
import FinishedRateTrend from './finished-rate-trend';
import StudentDetail from './student-detail';

class CourseDashboard{
    constructor() {
        this.init();
        this.charts();
    }

    init(){
      new StudentTrendency();
      new FinishedRateTrend();
      new StudentDetail($('#student-detail-chart'));
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