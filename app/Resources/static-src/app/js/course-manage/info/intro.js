import 'store';
import Cookies from 'js-cookie';
const COURSE_BASE_INTRO = 'COURSE_BASE_INTRO';

export default class Intro {
  constructor() {
    this.intro = null;
    this.customClass = 'es-intro-help multistep';
    this.$intro = $('.js-plan-intro');
    this.init();
  }

  init() {
    if (!store.get(COURSE_BASE_INTRO)) {
      store.set(COURSE_BASE_INTRO, true);
      this.introStart(this.initAllSteps());
      this.$intro.addClass('hidden');
    }
    this.initEvent();
  }

  initEvent() {
    $('body').on('click','.js-skip', (event) => {
      this.intro.exit();
    });
    $('body').on('click', '.js-plan-intro-btn', (event) => {
      $('html').scrollTop(0);
      this.introStart(this.initSingleStep());
    });
  }
  
  introStart(steps) {
    let doneLabel = '<i class="es-icon es-icon-close01"></i>';
    this.intro = introJs();
    if (steps.length < 2) {
      doneLabel = Translator.trans('intro.confirm_hint');
      this.customClass = 'es-intro-help es-intro-single';
    } else {
      this.customClass = 'es-intro-help multistep';
    }
  
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
      tooltipClass: this.customClass,
    });
    const self = this;
    this.intro.start().onexit(function() {
      self.$intro.removeClass('hidden');
    }).onchange(() => {
      if (this.intro._currentStep !== 0) {
        this.intro.setOptions({
          tooltipClass: 'es-intro-help multistep es-intro-normal-tip',
        });
      } else {
        this.intro.setOptions({
          tooltipClass: 'es-intro-help multistep',
        });
      }
      console.log(this.intro);
      if (this.intro._currentStep == (this.intro._introItems.length - 1 ) ) {
        $('.introjs-nextbutton').before('<a class="introjs-button done-button js-skip">'+Translator.trans('intro.confirm_hint')+'<a/>');
        this.$intro.removeClass('hidden');
      } else {
        $('.js-skip').remove();
      }
    });
  }

  
  initAllSteps() {
    let arry = [
      {
        intro: Translator.trans('course_set.manage.img'), // 第一步
      },
      {
        element: '#step-1',
        intro: Translator.trans('course_set.manage.couseset_tab'),  // 第二步
      },
      {
        element: '#step-2',
        intro: Translator.trans('course_set.manage.single_plan'),  // 第二步
      },
      {
        element: '#step-3',
        intro: Translator.trans('course_set.manage.all_plan'), // 第三步
      },
      {
        element: '#step-4',
        intro: Translator.trans('course_set.manage.publish_courseset'), // 第四步
      }
    ];
  
    return arry;
  }

  initSingleStep() {
    let array = [
      {
        intro: Translator.trans('course_set.manage.img'), // 第一步
      },
    ];
    return array;
  }
  
}