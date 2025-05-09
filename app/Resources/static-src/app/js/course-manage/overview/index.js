import StudentTrendency from './student-trendency';
import TaskDetail from './task-detail';
import FinishedRateTrend from './finished-rate-trend';
import StudentDetail from './student-detail';
import ExportSecondaryVerification from '../../secondary-verification/export-secondary-verification';

class CourseDashboard{
  constructor() {
    this.init();
  }

  init(){
    new StudentTrendency();
    new FinishedRateTrend();
    new StudentDetail($('#student-detail-chart'));
    new TaskDetail($('#task-data-chart'));
    new ExportSecondaryVerification({
      buttonSelector: '.course-student-detail-js-export-btn',
      formSelector: '#overview-student-detail',
      requestUrlBase: '/secondary/verification?exportFileName=overviewStudentDetail&targetFormId=' + $('#overview-student-detail').find('[name="courseId"]').val()
    });
    new ExportSecondaryVerification({
      buttonSelector: '.course-student-task-detail-js-export-btn',
      formSelector: '#overview-task-list',
      requestUrlBase: '/secondary/verification?exportFileName=overviewTaskList&targetFormId=' + $('#overview-task-list').find('[name="courseId"]').val()
    });
  }
}

let courseDashboard = new CourseDashboard();