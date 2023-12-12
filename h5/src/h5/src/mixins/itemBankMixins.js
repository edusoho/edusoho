import { ImagePreview } from 'vant'

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
			// const a = []
			// this.items.forEach((item) => {
			// 	item.questions.forEach((value) => {
			// 		const newObj = {};
			// 		Object.assign(newObj, item);
			// 		newObj.questions = [value];
			// 		newObj.seq = value.seq;
			// 		a.push(newObj);
			// 	});
			// });
			// this.items = a
			// console.log(a);

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
			
			// // 只有练习才有 isExercise --- 是不是练习解析页
			// if (this.isExercise) {
			// 	// 不是最后一题，练习模式为测验。并且不是材料题
			// 	if (this.mode === 'exercise' && lastQuestion && this.parentType !== 'material') {
			// 		return true;
			// 	} else if (this.mode === 'exercise' && lastQuestion && this.parentType === 'material') {
			// 		// 是练习解析页，不是最后一题，是材料题返回false
			// 		return false;
			// 	}
			// } 

			// // 是练习模式 并且为答题模式
			// if (this.mode === 'exercise' && this.canDo) {
			// 	// 为一题一答模式，不是最后一题，一题一答做题（true为可以选择，false为不可选，表示已提交）有没有提交
			// 	if (this.exerciseMode === '1' && lastQuestion && this.disabledData) {
			// 		return true
			// 	} 
			// 	// 一题一答，不是材料题，不是最后一题
			// 	if (this.exerciseMode === '1' && lastQuestion && this.parentType !== 'material') {
			// 		return true
			// 	}

			// 	if ( this.exerciseMode === '0' && lastQuestion && this.canDo ) {
			// 		return true
			// 	}
			// }
		},
		handleClickImage (imagesUrl) {
      if (imagesUrl === undefined) return;
      event.stopPropagation();//  阻止冒泡
      const images = [imagesUrl]
      ImagePreview({
        images
      })
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
