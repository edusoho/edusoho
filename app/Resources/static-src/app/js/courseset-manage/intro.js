import 'store';
const COURSE_All_INTRO = 'COURSE_All_INTRO';
const COURSE_BASE_INTRO = 'COURSE_BASE_INTRO'; 
const COURSE_TASK_INTRO = 'COURSE_TASK_INTRO';
const COURSE_TASK_DETAIL_INTRO = 'COURSE_TASK_DETAIL_INTRO';

export default class Intro {
  init() {
    if(this.isTaskCreatePage()) {
      this.initTaskCreatePageIntro();
      return;
    }
    this.initNotTaskCreatePageIntro();
  }

  introStart(steps){
    let intro = introJs();
    intro.setOptions({
      steps: steps,
      skipLabel:'我知道了',
      nextLabel:'下一步',
      prevLabel:'上一步'
    });

    intro.start();
  }

  isTaskCreatePage() {
    return $('#step-3').length;
  }

  initTaskCreatePageIntro() {
    if (store.get(COURSE_All_INTRO)) {
      return;
    }
    if (!store.get(COURSE_BASE_INTRO)) {
      store.set(COURSE_All_INTRO, true);
      this.introStart(this.initAllSteps());
      return;
    }
    if (!store.get(COURSE_TASK_INTRO)) {
      store.set(COURSE_TASK_INTRO, true);
      this.introStart(this.initTaskSteps());
    }
  }

  initTaskDetailIntro(element){
    if (!store.get(COURSE_TASK_DETAIL_INTRO)) {
      store.set(COURSE_TASK_DETAIL_INTRO);
      this.introStart(this.initTaskDetailSteps(element));
    }
  }

  initNotTaskCreatePageIntro() {
    if (store.get(COURSE_All_INTRO)) {
      return;
    }
    if (!store.get(COURSE_BASE_INTRO)) {
      console.log('ok');
      store.set(COURSE_BASE_INTRO, true);
      this.introStart(this.initNotTaskPageSteps());
    }
  }

  initNotTaskPageSteps() {
    return [
      { 
        intro: "课程管理功能现已全新升级!",
      },
      { 
        element: '#step-1',
        intro: "教学内容的编辑、管理请点击左侧“计划任务”的菜单项进入!",
      },
      { 
        element: '#step-2',
        intro: "在“营销设置”中您可以通过设置决定课程如何销售、如何加入、如何学习!",
      }
    ];
  }

  initAllSteps() {
    return [
      { 
        intro: "课程管理功能现已全新升级!",
      },
      { 
        element: '#step-1',
        intro: "教学内容的编辑、管理请点击左侧“计划任务”的菜单项进入!",
      },
      { 
        element: '#step-2',
        intro: "在“营销设置”中您可以通过设置决定课程如何销售、如何加入、如何学习!",
      },
      { 
        element: '#step-3',
        intro: "您可以在这里选择各种不同的教学手段，然后上传文件/设置内容/设置学习完成条件。!",
      }
    ];
  }

  initTaskSteps() {
    return [
      { 
        element: '#step-3',
        intro: "您可以在这里选择各种不同的教学手段，然后上传文件/设置内容/设置学习完成条件。!",
      }
    ];
  }

  initTaskDetailSteps(element) {
    return [
      { 
        element: element,
        intro: `在设计学习任务时，
        您可以按照课时去设置预习、学习、练习、作业、课外这几个环节，
        每个环节都可以通过各种教学手段来实现。!`,
      },
    ];
  }
}







