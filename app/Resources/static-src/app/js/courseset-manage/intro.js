import 'store';
import Cookies from 'js-cookie';
import { showSettings } from 'app/js/course-manage/help';
const COURSE_BASE_INTRO = 'COURSE_BASE_INTRO';
const COURSE_TASK_INTRO = 'COURSE_TASK_INTRO';
const COURSE_TASK_DETAIL_INTRO = 'COURSE_TASK_DETAIL_INTRO';
const COURSE_LIST_INTRO = 'COURSE_LIST_INTRO';
const COURSE_LIST_INTRO_COOKIE = 'COURSE_LIST_INTRO_COOKIE';

export default class Intro {
  constructor() {
    showSettings();
    $('body').on('click', '.js-reset-intro', () => {
      this.isRestintroType();
    })
    this.isRestintroType();
  }
  
  resetButtonRender(show = false) {
    let $btn = $('.js-intro-btn-group');
    show ? $btn.removeClass('hidden') : $btn.addClass('hidden');
  }

  introType() {
    if (this.isTaskCreatePage()) {
      this.initTaskCreatePageIntro();
      return;
    }
    if (!this.isCourseListPage()) {
      this.initNotTaskCreatePageIntro();
      return;
    }
    this.initCourseListPageIntro();
  }

  isRestintroType() {
    if (this.isTaskCreatePage()) {
      store.remove(COURSE_TASK_INTRO);
      this.initTaskCreatePageIntro();
      return;
    }
    if (!this.isCourseListPage()) {
      store.remove(COURSE_BASE_INTRO);
      this.initNotTaskCreatePageIntro();
    }
    store.remove(COURSE_LIST_INTRO);
    this.initCourseListPageIntro();
  }

  isCourseListPage() {
    return !!$('#courses-list-table').length;
  }

  isTaskCreatePage() {
    return !!$('#step-3').length;
  }

  isInitTaskDetailIntro() {
    $('.js-task-manage-item').attr('id', 'step-5');
    return !!$('.js-settings-list').length;
  }

  introStart(steps) {
    let intro = introJs();
    intro.setOptions({
      steps: steps,
      skipLabel: '<i class="es-icon es-icon-close01"></i>',
      nextLabel: '继续了解',
      prevLabel: '上一步',
      doneLabel: '<i class="es-icon es-icon-close01"></i>',
      showBullets: false,
      tooltipPosition: 'auto',
      // positionPrecedence:['left', 'right', 'bottom', 'top'],
      showStepNumbers: false,
    });
    intro.start();
  }

  initTaskCreatePageIntro() {
    $('.js-task-manage-item:first').trigger('mouseenter');
    if (!store.get(COURSE_BASE_INTRO) && !store.get(COURSE_TASK_INTRO)) {
      store.set(COURSE_BASE_INTRO, true);
      store.set(COURSE_TASK_INTRO, true);
      this.introStart(this.initAllSteps());
    } else if (!store.get(COURSE_TASK_INTRO)) {
      store.set(COURSE_TASK_INTRO, true);
      this.introStart(this.initTaskSteps());
    }
  }

  initTaskDetailIntro(element) {
    if (!store.get(COURSE_TASK_DETAIL_INTRO)) {
      store.set(COURSE_TASK_DETAIL_INTRO);
      this.introStart(this.initTaskDetailSteps(element));
    }
  }

  initNotTaskCreatePageIntro() {
    if (!store.get(COURSE_BASE_INTRO)) {
      console.log('ok');
      store.set(COURSE_BASE_INTRO, true);
      this.introStart(this.initNotTaskPageSteps());
    }
  }

  isSetCourseListCookies() {
    if (!store.get(COURSE_LIST_INTRO)) {
      console.log('ok');
      Cookies.set(COURSE_LIST_INTRO_COOKIE, true);
    }
  }

  initCourseListPageIntro() {
    let listLength = $('#courses-list-table').find('tbody tr').length;
    if (!(listLength === 2) || store.get(COURSE_LIST_INTRO)) {
      return;
    }
    new Promise((resolve, reject) => {
      setTimeout(function () {
        let $courseMenu = $('.js-sidenav-course-menu');
        if (!$courseMenu.length) {
          resolve();
          return;
        }
        $('.js-sidenav-course-menu').slideUp(function () {
          resolve();
        });
      }, 500);
    }).then(() => {
      setTimeout(() => {
        this.initCourseListIntro('.js-sidenav');
        console.log('initCourseListIntro');
      }, 500);
    });
  }

  initCourseListIntro(element) {
    if (!store.get(COURSE_LIST_INTRO)) {
      store.set(COURSE_LIST_INTRO, true);
      this.introStart(this.initCourseListSteps(element));
      Cookies.remove(COURSE_LIST_INTRO_COOKIE);
    }
  }

  initAllSteps() {
    let arry = [
      {
        intro: `<p class="title">功能升级</p>
        课程管理功能现已全新升级!`,
      },
      {
        element: '#step-1',
        intro: `<p class="title">计划任务</p>
        教学内容的编辑、管理请点击左侧“计划任务”的菜单项进入!`,
      },
      {
        element: '#step-2',
        intro: `<p class="title">营销设置</p>
        在“营销设置”中您可以通过设置决定课程如何销售、如何加入、如何学习!`,
      },
      {
        element: '#step-3',
        intro: `<p class="title">添加任务</p>
        您可以在这里选择各种不同的教学手段，然后上传文件/设置内容/设置学习完成条件。!`,
      }
    ];
    //如果存在任务
    if (this.isInitTaskDetailIntro()) {
      arry.push({
        element: '#step-5',
        intro: `<p class="title">任务环节</p>
        在设计学习任务时，您可以按照课时去设置预习、学习、练习、作业、课外这几个环节，
        每个环节都可以通过各种教学手段来实现。!`,
      })
    }

    return arry;
  }

  initNotTaskPageSteps() {
    return [
      {
        intro: `<p class="title">功能升级</p>
        课程管理功能现已全新升级!`,
      },
      {
        element: '#step-1',
        intro: `<p class="title">计划任务</p>
        教学内容的编辑、管理请点击左侧“计划任务”的菜单项进入!`,
      },
      {
        element: '#step-2',
        intro: `<p class="title">营销设置</p>
        在“营销设置”中您可以通过设置决定课程如何销售、如何加入、如何学习!`,
      }
    ];
  }

  initTaskSteps() {
    let arry = [
      {
        element: '#step-3',
        intro: `<p class="title">添加任务</p>
        您可以在这里选择各种不同的教学手段，然后上传文件/设置内容/设置学习完成条件。!`,
      }
    ];
    //如果存在任务
    if (this.isInitTaskDetailIntro()) {
      arry.push({
        element: '#step-5',
        intro: `<p class="title">任务环节</p>
        在设计学习任务时，您可以按照课时去设置预习、学习、练习、作业、课外这几个环节，
        每个环节都可以通过各种教学手段来实现。!`,
      })
    }

    return arry;
  }

  initTaskDetailSteps(element) {
    return [
      {
        element: element,
        intro: `<p class="title">任务环节</p>
        在设计学习任务时，您可以按照课时去设置预习、学习、练习、作业、课外这几个环节，
        每个环节都可以通过各种教学手段来实现。!`,
      },
    ];
  }

  initCourseListSteps(element) {
    return [
      {
        element: element,
        intro: `
          <p class="title">多个教学计划</p>
          恭喜你创建了多个教学计划！左侧的功能菜单会有所简化，
          只会显示课程公共的相关设置。`,
      }
    ];
  }
}







