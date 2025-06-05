import ClassroomStudentTrend from './student-trend';
import StudentDetail from './student-detail';
import TaskDetail from './task-detail';
import ExportSecondaryVerification from '../../secondary-verification/export-secondary-verification';

class ClassroomStatistics{
  constructor() {
    this.init();
  }

  init() {
    new ClassroomStudentTrend();
    new StudentDetail($('#student-detail-chart'));
    new TaskDetail($('#task-data-chart'));
    new ExportSecondaryVerification({
      buttonSelector: '.classroom-student-chart-js-export-btn',
      formSelector: '#overview-student-detail',
      requestUrlBase: '/secondary/verification?exportFileName=overviewClassroomStudentDetail&targetFormId=' + $('#overview-student-detail').find('[name="classroomId"]').val()
    });
    new ExportSecondaryVerification({
      buttonSelector: '.classroom-course-chart-js-export-btn',
      formSelector: '#overview-task-list',
      requestUrlBase: '/secondary/verification?exportFileName=overviewClassroomStatisticsCourse&targetFormId=' + $('#overview-task-list').find('[name="classroomId"]').val()
    });
  }
}

new ClassroomStatistics();