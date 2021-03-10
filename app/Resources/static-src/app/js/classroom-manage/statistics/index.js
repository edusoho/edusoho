import ClassroomStudentTrend from './student-trend';
import StudentDetail from './student-detail';

class ClassroomStatistics{
  constructor() {
    this.init();
  }

  init() {
    new ClassroomStudentTrend();
    new StudentDetail($('#student-detail-chart'));
  }
}

new ClassroomStatistics();