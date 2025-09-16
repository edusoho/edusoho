import Api from '@/api';
import Viewer from 'viewerjs';

export default {
  computed: {
    needScore() {
      return !!Number(this.answerScene.need_score);
    }
  },
  methods: {
    async getQuestion() {
      this.question = await Api.getExerciseQuestion({
        query: {
          answerRecordId: this.answerRecord.id,
          questionId: this.items[this.itemIndex].questions[this.questionIndex].id,
        },
      })
    },
    questionSlideChange(index) {
      this.questionIndex = index;
      this.aiAgentSdk && this.aiAgentSdk.hideReminder();
    },
    slideNextTransitionEnd() {
      this.questionIndex = 0;
      if (this.current === this.items.length - 1) {
        return;
      }
      this.itemIndex += 1;
      this.aiAgentSdk && this.aiAgentSdk.hideReminder();
      this.current += 1;
      this.changeRenderItems(this.current);
      this.fastSlide();
    },
    slidePrevTransitionEnd() {
      if (this.current === 0) {
        return;
      }
      this.questionIndex = 0;
      this.itemIndex -= 1;
      this.aiAgentSdk && this.aiAgentSdk.hideReminder();
      this.current -= 1;
      this.changeRenderItems(this.current);
      const item = this.items[this.current];
      const itemKey = `item${item.id}`;
      this.$nextTick(() => {
        const childSwiper = this.$refs[itemKey][0].$refs[
          `childSwiper${item.id}`
        ];
        const childSwiperSlide = Math.max(item.questions.length - 1, 0);
        childSwiper.$swiper.slideTo(childSwiperSlide, 0, false);
      });
      this.fastSlide();
    },
    itemSlideNext() {
      this.$refs.mySwiper.$swiper.slideNext();
    },
    itemSlidePrev() {
      this.$refs.mySwiper.$swiper.slidePrev();
    },
    changeRenderItems(current) {
      const renderItmes = [];
      if (this.items[current - 1]) {
        renderItmes.push(this.items[current - 1]);
      }
      if (this.items[current]) {
        renderItmes.push(this.items[current]);
      }
      if (this.items[current + 1]) {
        renderItmes.push(this.items[current + 1]);
      }
      this.renderItmes = renderItmes;
    },
    fastSlide() {
      if (this.current === 0) {
        this.$nextTick(() => {
          this.$refs.mySwiper.$swiper.slideTo(0, 0, false);
        });
      } else {
        this.$nextTick(() => {
          this.$refs.mySwiper.$swiper.slideTo(1, 0, false);
        });
      }
    },
    setSwiperHeight() {
      const offsetTopHeight = document.getElementById("ibs-item-bank")
        .offsetTop;
      const WINDOWHEIGHT = document.documentElement.clientHeight;
      this.height = WINDOWHEIGHT - offsetTopHeight;
    },
    getStem() {
      if (this.commonData.questionsType === "text") {
        return this.filterFillHtml(this.commonData.questionStem);
      }
      return this.commonData.questionStem;
    },
    filterFillHtml(text) {
      const reg = /\[\[\]\]/g;
      if (!text.match(reg)) {
        return text;
      }
      let index = 1;
      return text.replace(reg, function() {
        return `<span class="ibs-fill-bank">(${index++}）</span>`;
      });
    },
    isShowFooterShadow() {
      // 模式不为练习 并且不是最后一题,并且为答题模式
      const id = this.currentItem.questions[this.currentItem.questions.length - 1].id
      const lastQuestion = this.showShadow !== id
      if (this.mode === 'do' && lastQuestion && this.currentItem.type !== 'material' ) {
        // 模式不为练习，不是最后一题，是解析模式，并且题型不为材料题
        return true;
      }
      if (this.mode === 'report' && lastQuestion && this.currentItem.type !== 'material' ) {
        // 模式不为练习，不是最后一题，是解析模式，并且题型不为材料题
        return true;
      }
    },
    handleClickImage (container) {
      if (!container) return;
      const viewer = new Viewer(container, {
        navbar: false,
        title: false,
        toolbar: {
          zoomIn: true,
          zoomOut: true,
          oneToOne: true,
          reset: true,
          prev: false,
          play: false,
          next: false,
          rotateLeft: true,
          rotateRight: true,
          flipHorizontal: true,
          flipVertical: true,
        }
      });
    },
    refreshChoice(res) {
      if (res) {
        this.$nextTick(() => {
          this.question[0] = res
          this.refreshKey = !this.refreshKey
        })
        return
      }
      const obj = this.exerciseInfo
      this.$nextTick(() => {
        this.question = obj.filter(item => item.questionId+'' === this.itemdata.question.id)
        this.refreshKey = !this.refreshKey
      })
    },
  }
};
