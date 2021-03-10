import ClassroomStudentTrend from './student-trend';
import StudentDetail from './student-detail';
import TaskDetail from './task-detail';

class ClassroomStatistics{
  constructor() {
    this.init();
  }

  init() {
    new ClassroomStudentTrend();
    new StudentDetail($('#student-detail-chart'));
    new TaskDetail($('#task-data-chart'));
  }
}

new ClassroomStatistics();