export default {
  computed: {
    needScore() {
      return !!Number(this.answerScene.need_score);
    }
  },
  methods: {
    itemSlideNext() {
      this.$refs.mySwiper.$swiper.slideNext();
    },
    itemSlidePrev() {
      this.$refs.mySwiper.$swiper.slidePrev();
    },
    slideNextTransitionEnd() {
      if (this.current === this.items.length - 1) {
        return;
      }
      this.current += 1;
      this.changeRenderItems(this.current);
      this.fastSlide();
    },
    slidePrevTransitionEnd() {
      if (this.current === 0) {
        return;
      }
      this.current -= 1;
      this.changeRenderItems(this.current);
      const item = this.items[this.current];
      const itemKey = `item${item.id}`;
      this.$nextTick(() => {
        const childSwiper = this.$refs[itemKey][0].$refs[
          `childSwiper${item.id}`
        ];
        let childSwiperSlide = Math.max(item.questions.length - 1, 0);
        childSwiper.$swiper.slideTo(childSwiperSlide, 0, false);
      });
      this.fastSlide();
    },
    changeRenderItems(current) {
      let renderItmes = [];
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
    }
  }
};
