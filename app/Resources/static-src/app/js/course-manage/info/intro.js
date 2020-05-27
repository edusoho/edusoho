import 'store';
import Cookies from 'js-cookie';
import { debounce } from 'app/common/widget/debounce';
const COURSE_BASE_INTRO = 'COURSE_BASE_INTRO';

export default class Intro {
  constructor() {
    this.intro = null;
    this.customClass = 'es-intro-help multistep';
    this.$intro = $('.js-plan-intro');
    this.init();
  }

  init() {
    if (!this.$intro.length) {
      return;
    }
    let isS2b2cEnabled = $('#s2b2c_enabled').val();
    if (!store.get(COURSE_BASE_INTRO) && !isS2b2cEnabled) {
      store.set(COURSE_BASE_INTRO, true);
      this.introStart(this.initAllSteps());
      this.$intro.addClass('hidden');
    }
    this.initEvent();
  }

  initEvent() {
    const self = this;
    $('body').on('click','.js-skip', (event) => {
      this.intro.exit();
      this.$intro.removeClass('hidden');
    });
    $('body').on('click', '.js-plan-intro-btn', (event) => {
      $('html').scrollTop(0);
      this.introStart(this.initSingleStep());
    });
    window.addEventListener('scroll', debounce(self.scrollPosition, 100, true));
  }
  
  introStart(steps) {
    let doneLabel = '<i class="es-icon es-icon-close01"></i>';
    this.intro = introJs();
    this.customClass = steps.length < 2 ? 'es-intro-help js-intro-help es-intro-single' : 'es-intro-help js-intro-help multistep';
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
      } else {
        $('.js-skip').remove();
      }
    });
    $('.js-intro-help').parent().css('top', '0');
  }

  scrollPosition() {
    const $intro = $('.js-plan-intro');
    const scrollTop = $(document).scrollTop();
    if (scrollTop > 440) {
      $intro.addClass('course-manage-intro-float');
    } else {
      $intro.removeClass('course-manage-intro-float');
    }
  }
  
  initAllSteps() {
    let arry = [
      {
        intro: Translator.trans('course_set.manage.img'),
      },
      {
        element: '#step-1',
        intro: Translator.trans('course_set.manage.couseset_tab'),
      },
      {
        element: '#step-2',
        intro: Translator.trans('course_set.manage.single_plan'),
      },
      {
        element: '#step-3',
        intro: Translator.trans('course_set.manage.all_plan'),
      },
      {
        element: '#step-4',
        intro: Translator.trans('course_set.manage.publish_courseset'),
      }
    ];
  
    return arry;
  }

  initSingleStep() {
    let array = [
      {
        intro: Translator.trans('course_set.manage.img'),
      },
    ];
    return array;
  }
}