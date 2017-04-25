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
    this.customClass = "es-intro-help multistep";
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
       doneLabel= '我知道了';
       this.customClass = "es-intro-help";
    }else {
       this.customClass = "es-intro-help multistep";
    }
    console.log(steps.length < 2);
    console.log(this.customClass);
    console.log(doneLabel);
    this.intro.setOptions({
      steps: steps,
      skipLabel: doneLabel,
      nextLabel: '继续了解',
      prevLabel: '上一步',
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
        $('.introjs-nextbutton').before('<a class="introjs-button  done-button js-skip">我知道了<a/>');
      }
      else {
        $('.js-skip').remove();
      }
    })
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
        intro: `<p class="title">功能升级</p>
        课程管理功能现已全新升级。`,
      },
      {
        element: '#step-1',
        intro: `<p class="title">计划任务</p>
        教学内容的编辑、管理请点击左侧“计划任务”的菜单项进入。`,
      },
      {
        element: '#step-2',
        intro: `<p class="title">营销设置</p>
        在“营销设置”中您可以通过设置决定课程如何销售、如何加入、如何学习。`,
      },
      {
        element: '#step-3',
        intro: `<p class="title">添加任务</p>
        您可以在这里选择各种不同的教学手段，然后上传文件/设置内容/设置学习完成条件。`,
      }
    ];
    //如果存在任务
    if (this.isInitTaskDetailIntro()) {
      arry.push({
        element: '[into-step-id="step-5"]',
        intro: `<p class="title">任务环节</p>
        在设计学习任务时，您可以按照课时去设置预习、学习、练习、作业、课外这几个环节，
        每个环节都可以通过各种教学手段来实现。`,
      })
      if (!store.get(COURSE_TASK_DETAIL_INTRO)) {
        store.set(COURSE_TASK_DETAIL_INTRO,true);
      }
    }
    return arry;
  }

  initNotTaskPageSteps() {
    return [
      {
        intro: `<p class="title">功能升级</p>
        课程管理功能现已全新升级。`,
      },
      {
        element: '#step-1',
        intro: `<p class="title">计划任务</p>
        教学内容的编辑、管理请点击左侧“计划任务”的菜单项进入。`,
      },
      {
        element: '#step-2',
        intro: `<p class="title">营销设置</p>
        在“营销设置”中您可以通过设置决定课程如何销售、如何加入、如何学习。`,
      }
    ];
  }

  initTaskSteps() {
    let arry = [
      {
        element: '#step-3',
        intro: `<p class="title">添加任务</p>
        您可以在这里选择各种不同的教学手段，然后上传文件/设置内容/设置学习完成条件。`,
      }
    ];
    //如果存在任务
    if (this.isInitTaskDetailIntro()) {
      arry.push({
        element: '#step-5',
        intro: `<p class="title">任务环节</p>
        在设计学习任务时，您可以按照课时去设置预习、学习、练习、作业、课外这几个环节，
        每个环节都可以通过各种教学手段来实现。`,
        position: 'bottom',
      })
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
        intro: `<p class="title">任务环节</p>
        在设计学习任务时，您可以按照课时去设置预习、学习、练习、作业、课外这几个环节，
        每个环节都可以通过各种教学手段来实现。`,
        position: 'bottom',
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
          只会显示课程公共的相关设置。`
      }
    ];
  }
  initResetStep(introBtnClassName='') {
    return [
      {
        element: '.js-intro-btn-group',
        intro: `<div class="btn-content"><p><a class='btn btn-success js-reset-intro ${introBtnClassName}'>查看引导</a></p>
        <a class='btn btn-info'>完整教程</a><div>`,
        position:'top'
      }
    ];
  }
}







