import 'store';
import Cookies from 'js-cookie';
const COURSE_BASE_INTRO = 'COURSE_BASE_INTRO';
const COURSE_TASK_INTRO = 'COURSE_TASK_INTRO';
const COURSE_TASK_DETAIL_INTRO = 'COURSE_TASK_DETAIL_INTRO';
const COURSE_LIST_INTRO = 'COURSE_LIST_INTRO';
const COURSE_LIST_INTRO_COOKIE = 'COURSE_LIST_INTRO_COOKIE';

export default class Intro {
  constructor() {
    this.intro = null;
    this.customClass = 'es-intro-help multistep';
    $('body').on('click','.js-skip',(event)=>{
      this.intro.exit();
    });
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

  isCourseListPage() {
    return !!$('#courses-list-table').length;
  }

  isTaskCreatePage() {
    return !!$('#step-3').length;
  }

  isInitTaskDetailIntro() {
    $('.js-task-manage-item').attr('into-step-id', 'step-5');
    return !!$('.js-settings-list').length;
  }

  introStart(steps) {
    let doneLabel = '<i class="es-icon es-icon-close01"></i>';
    this.intro = introJs();
    if(steps.length < 2) {
      doneLabel= Translator.trans('intro.confirm_hint');
      this.customClass = 'es-intro-help';
    }else {
      this.customClass = 'es-intro-help multistep';
    }
    console.log(steps.length < 2);
    console.log(this.customClass);
    console.log(doneLabel);
    this.intro.setOptions({
      steps: steps,
      skipLabel: doneLabel,
      nextLabel: Translator.trans('course_set.manage.next_label'),
      prevLabel: Translator.trans('course_set.manage.prev_label'),
      doneLabel: doneLabel,
      showBullets: false,
      tooltipPosition: 'auto',
      showStepNumbers: false,
      exitOnEsc: false,
      exitOnOverlayClick: false,
      tooltipClass:this.customClass,
    });
    
    this.intro.start().onexit(function(){
    }).onchange(()=>{
      console.log(this.intro);
      if(this.intro._currentStep ==(this.intro._introItems.length -1 ) ) {
        $('.introjs-nextbutton').before('<a class="introjs-button  done-button js-skip">'+Translator.trans('intro.confirm_hint')+'<a/>');
      }
      else {
        $('.js-skip').remove();
      }
    });
  }

  initTaskCreatePageIntro() {
    $('.js-task-manage-item:first .js-item-content').trigger('click');
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
      store.set(COURSE_TASK_DETAIL_INTRO,true);
      this.introStart(this.initTaskDetailSteps(element));
    }
  }

  initNotTaskCreatePageIntro() {
    if (!store.get(COURSE_BASE_INTRO)) {
      store.set(COURSE_BASE_INTRO, true);
      this.introStart(this.initNotTaskPageSteps());
    }
  }

  isSetCourseListCookies() {
    if (!store.get(COURSE_LIST_INTRO)) {
      Cookies.set(COURSE_LIST_INTRO_COOKIE, true);
    }
  }

  initCourseListPageIntro() {
    let listLength = $('#courses-list-table').find('tbody tr').length;
    
    if (!(listLength === 2) || store.get(COURSE_LIST_INTRO)|| !Cookies.get(COURSE_LIST_INTRO_COOKIE)) {
      return;
    }
    Cookies.remove(COURSE_LIST_INTRO_COOKIE);
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
      }, 100);
    }).then(() => {
      setTimeout(() => {
        this.initCourseListIntro('.js-sidenav');
        console.log('initCourseListIntro');
      }, 100);
    });
  }

  initCourseListIntro(element) {
    if (!store.get(COURSE_LIST_INTRO)) {
      store.set(COURSE_LIST_INTRO, true);
      this.introStart(this.initCourseListSteps(element));
    }
  }

  initAllSteps() {
    let arry = [
      {
        intro: Translator.trans('course_set.manage.upgrade_hint'),
      },
      {
        element: '#step-1',
        intro: Translator.trans('course_set.manage.upgrade_step1_hint'),
      },
      {
        element: '#step-2',
        intro: Translator.trans('course_set.manage.upgrade_step2_hint'),
      },
      {
        element: '#step-3',
        intro: Translator.trans('course_set.manage.upgrade_step3_hint'),
      }
    ];
    //如果存在任务
    if (this.isInitTaskDetailIntro()) {
      arry.push({
        element: '[into-step-id="step-5"]',
        intro: Translator.trans('course_set.manage.upgrade_step5_hint'),
      });
      if (!store.get(COURSE_TASK_DETAIL_INTRO)) {
        store.set(COURSE_TASK_DETAIL_INTRO,true);
      }
    }
    return arry;
  }

  initNotTaskPageSteps() {
    return [
      {
        intro: Translator.trans('course_set.manage.upgrade_hint'),
      },
      {
        element: '#step-1',
        intro: Translator.trans('course_set.manage.upgrade_step1_hint'),
      },
      {
        element: '#step-2',
        intro: Translator.trans('course_set.manage.upgrade_step2_hint'),
      }
    ];
  }

  initTaskSteps() {
    let arry = [
      {
        element: '#step-3',
        intro: Translator.trans('course_set.manage.upgrade_step3_hint'),
      }
    ];
    //如果存在任务
    if (this.isInitTaskDetailIntro()) {
      arry.push({
        element: '#step-5',
        intro: Translator.trans('course_set.manage.upgrade_step5_hint'),
        position: 'bottom',
      });
      if (!store.get(COURSE_TASK_DETAIL_INTRO)) {
        store.set(COURSE_TASK_DETAIL_INTRO,true);
      }
    }

    return arry;
  }

  initTaskDetailSteps(element) {
    return [
      {
        element: element,
        intro: Translator.trans('course_set.manage.activity_link_hint'),
        position: 'bottom',
      },
    ];
  }

  initCourseListSteps(element) {
    return [
      {
        element: element,
        intro: Translator.trans('course_set.manage.hint')
      }
    ];
  }
  initResetStep(introBtnClassName='') {
    return [
      {
        element: '.js-intro-btn-group',
        intro: Translator.trans('course_set.manage.all_tutorial', { 'introBtnClassName': introBtnClassName}),
        position:'top'
      }
    ];
  }
}