import StudentTrendency from './student-trendency';
import TaskDetail from './task-detail';
import FinishedRateTrend from './finished-rate-trend';

class CourseDashboard{
    constructor() {
        this.init();
        this.charts();
    }

    init(){
      new StudentTrendency();
      new FinishedRateTrend()
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