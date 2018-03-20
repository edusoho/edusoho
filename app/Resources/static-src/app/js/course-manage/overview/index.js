import StudentTrendency from './student-trendency';
import TaskDetail from './task-detail';
import FinishedRateTrend from './finished-rate-trend';
import StudentDetail from './student-detail';

class CourseDashboard{
  constructor() {
    this.init();
  }

  init(){
    new StudentTrendency();
    new FinishedRateTrend();
    new StudentDetail($('#student-detail-chart'));
    new TaskDetail($('#task-data-chart'));
  }
}

let courseDashboard = new CourseDashboard();