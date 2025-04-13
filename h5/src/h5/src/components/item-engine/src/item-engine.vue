<template>
  <div id="ibs-item-bank" class="ibs-item-bank">
    <template v-if="this.section_responses.length > 0 && items.length > 0">
      <swiper
        ref="mySwiper"
        :height="height"
        :loop="false"
        :speed="500"
        @slideNextTransitionEnd="slideNextTransitionEnd"
        @slidePrevTransitionEnd="slidePrevTransitionEnd"
				:class="{'swiper-no-swiping': !touchable}"
      >
        <template v-for="item in renderItmes">
          <swiper-slide
            :key="`paper${item.id}`"
            :ref="`paper${item.id}`"
            :style="{ height: height + 'px' }"
						style="overflow-x: hidden; z-index: 1 !important;"
            class="ibs-paper-item"
          >
            <ibs-item
              :ref="`item${item.id}`"
              :item="item"
              :mode="mode"
              :doLookAnalysis="doLookAnalysis"
              :itemUserAnswer="getUserAnwer(item.sectionIndex, item.itemIndex)"
              :needScore="needScore"
              :all="Number(assessment.question_count)"
              :keys="[item.sectionIndex, item.itemIndex]"
              :current="current"
              :itemLength="items.length"
              :showAnalysis="doLookAnalysis"
              :reviewedCount="reviewedCount"
							:isAnswerFinished="isAnswerFinished"
							:items="items"
							:wrong="wrong"
							:exerciseInfo="exerciseInfo"
							:iscando="iscando"
							:fillStatus="fillStatus"
							:choiceIsCando="choiceIsCando"
							:reviewedQuestion="reviewedQuestion"
							:EssayRadio="EssayRadio"
							@changeIsCando="changeIsCando"
							@changeChoiceCando="changeChoiceCando"
							@changeTouch="changeTouch"
							@noChangeTouch="noChangeTouch"
							@updataIsAnswerFinished="updataIsAnswerFinished"
              @changeAnswer="changeAnswer"
              @itemSlideNext="itemSlideNext"
              @itemSlidePrev="itemSlidePrev"
							@changeEssayRadio="changeEssayRadio"
              @changeReviewedCount="changeReviewedCount"
							@submitedQuestionStatus="submitedQuestionStatus"
            />
          </swiper-slide>
        </template>
      </swiper>

      <!-- 答题卡 -->
      <card
        ref="card"
        v-model="cardShow"
        :mode="mode"
        :sections="assessment.sections"
        :section_responses="section_responses"
        :assessmentResponse="assessmentResponse"
        :all="Number(assessment.question_count)"
        :reviewedCount="reviewedCount"
        @slideTo="slideTo"
      ></card>

      <ibs-footer
        v-if="brushDo.exerciseModes === '0'"
        :mode="mode"
        :show-save-process-btn="showSaveProcessBtn"
        @showcard="showcard"
        @submitPaper="submitPaper"
      />
      <!-- 倒计时 -->
      <countDown
        :mode="mode"
        :limitedTime="Number(answerScene.limited_time)"
        :usedTime="Number(answerRecord.used_time)"
        :beginTime="Number(answerRecord.begin_time)"
        @reachTimeSubmitAnswerData="reachTimeSubmitAnswerData"
      />
    </template>
  </div>
</template>
<script>
import ibsItem from "@/src/components/item/src/item.vue";
import card from "@/src/components/common/card.vue";
import countDown from "./component/count-down";
import ibsFooter from "@/src/components/common/footer.vue";
import { compareNowTime, timeStampFormatTime } from "@/src/utils/date-toolkit";
import { Dialog, Toast } from "vant";
import itemBankMixins from "@/src/mixins/itemBankMixins.js"
import Api from '@/api';
import aiAgent from '@/mixins/aiAgent';

export default {
  name: "item-engine",
  mixins: [itemBankMixins, aiAgent],
  components: {
    ibsItem,
    card,
    countDown,
    ibsFooter,
  },
  props: {
    exerciseId: {
      type: [String, Number],
      default: ""
    },
    mode: {
      type: String,
      default: "do"
    },
    assessmentResponse: {
      type: Object,
      default: () => {}
    },
    answerScene: {
      type: Object,
      default: () => {}
    },
    answerRecord: {
      type: Object,
      default: () => {}
    },
    answerReport: {
      type: Object,
      default: () => {}
    },
    assessment: {
      type: Object,
      default: () => {}
    },
    showSaveProcessBtn: {
      type: Boolean,
      default: true
    },
		exerciseInfo: {
			type: [Array, Object],
      default: () => []
		},
		wrong: {
			type: Boolean,
			default: false
		}
  },
  provide() {
    return {
      itemEngine:this
    }
  },
  inject: ["brushDo"],
  data() {
    return {
      section_responses: [],
      height: 0, // swiper高低
      cardShow: false,
      count: 0, // 计时累加数
      intervalId: null, // 计时器
      currentItemIndex: 0, // 当前题目索引
      sourceMap: {},
      current: 0,
      items: [],
      renderItmes: [],
      hasResponses: false,
      continueDo: null,
      reviewedCount: null,
      isAnswerFinished: null,
			touchable: true,
			allItems: [],
			iscando: [],
			choiceIsCando: [],
			reviewedQuestion: [],
			fillStatus: [],
			EssayRadio: [],
      question: {},
      itemIndex: 0,
      questionIndex: 0,
      aiAgentSdk: null,
    };
  },
  beforeDestroy() {
    this.clearTime();
  },
  // 离开页面守卫
  beforeRouteLeave(to, from, next) {
    next();
  },
  computed: {
    needScore() {
      return !!Number(this.answerScene.need_score);
    },
    doLookAnalysis() {
      return !!Number(this.answerScene.doing_look_analysis);
    }
  },
  async mounted() {
    this.$nextTick(()=> {
			this.items.forEach((item,index) => {
				item.questions.forEach((sub)=> {
					this.allItems.push(sub);
				})
				this.choiceIsCando[index] = false
			})
			this.allItems.forEach((item, index) => {
        this.iscando[index] = this.exerciseInfo.filter(subItem => subItem.questionId + '' === item.id).length <= 0;
			});
      this.getQuestion();
      this.tryInitAIAgentSdk();
		})
    if (compareNowTime(Number(this.answerScene.start_time) * 1000)) {
      this.noStartTool();
      return;
    }
    this.setSwiperHeight();
    this.getSectionResponses();
    if (this.brushDo.exerciseModes === '0') {
      this.countTime();
    }

  },
  methods: {
    tryInitAIAgentSdk() {
      Api.getItemBankExercise({
        query: {
          id: this.$route.query.exerciseId,
        }
      }).then(res => {
        if (res.aiTeacherDomain) {
          this.aiAgentSdk = this.initAIAgentSdk(this.$store.state.user.aiAgentToken, {
            domainId: res.aiTeacherDomain,
          }, 80, 20);
          if (res.studyPlanGenerated) {
            this.aiAgentSdk.setVariable('studyPlanGenerated' ,true)
          }
          this.aiAgentSdk.boot();
          this.aiAgentSdk.showReminder({
            title: "Hi，我是小知老师～",
            content: "我将在你答题过程中随时为你答疑解惑",
            duration: 5000,
          });

          const btn = document.getElementById('agent-sdk-floating-button');
          if (!btn) return;
          btn.addEventListener('click', () => {
            this.aiAgentSdk.showReminder({
              title: "遇到问题啦？",
              content: "小知老师来为你理清解题思路～",
              buttonContent: 'teacher.question',
              duration: 5000,
              workflow: {
                workflow: 'teacher.question.idea',
                inputs: {
                  domainId: res.aiTeacherDomain,
                  question: this.items[this.itemIndex].questions[this.questionIndex].id,
                }
              },
              chatContent: this.question.content,
            });
          });
        }
      })
        .catch(err => {
          console.log(err);
        })
    },
		changeIsCando(index, flag) {
			this.iscando[index] = flag
		},
		changeChoiceCando(index, flag) {
			this.choiceIsCando[index] = flag
		},
    // 题卡定位
    slideTo(keys) {
      const itemKey = `item${keys.itemId}`;
      this.current = keys.itemIndex - 1;
      this.changeRenderItems(this.current);
      this.fastSlide();

      this.$nextTick(() => {
        const childSwiper = this.$refs[itemKey][0].$refs[
          `childSwiper${keys.itemId}`
        ];
        const childSwiperSlide = Math.max(keys.questionIndex, 0);
        childSwiper.$swiper.slideTo(childSwiperSlide, 0, false); // 子级 questions的swiper滑动
      });
    },
    judgelNoAnswer(section_responses) {
      let noAnswerNum = 0;
      section_responses.forEach(section => {
        section.item_responses.forEach(item => {
          item.question_responses.forEach(question => {
            const doItem = question.response.some(answer => {
              return answer != "";
            });
            if (!doItem) {
              noAnswerNum += 1;
            }
          });
        });
      });
      return noAnswerNum;
    },
    noStartTool() {
      Dialog.alert({
        title: "考试说明",
        message: `考试未开始，请在${timeStampFormatTime(
          this.answerScene.start_time
        )}之后再来`,
        getContainer: "#ibs-item-bank"
      }).then(() => {});
    },
    getSectionResponses() {
      if (
        this.mode === "do" &&
        this.assessmentResponse.section_responses.length > 0
      ) {
        this.section_responses = this.assessmentResponse.section_responses;
        this.hasResponses = true;
      }

      this.formateSections();
    },
    // 遍历获取答案体结构
    formateSections() {
      this.assessment.sections.forEach((item, sectionIndex) => {
        if (this.hasResponses) {
          this.formateItems(item.items, sectionIndex);
        } else {
          this.section_responses.push({
            section_id: item.id,
            item_responses: this.formateItems(item.items, sectionIndex)
          });
        }
      });
      this.changeRenderItems(0);
      this.slideToContinue();
    },
    formateItems(items, sectionIndex) {
      const item_responses = [];

      items.forEach((item, ItemIndex) => {
        item.sectionIndex = sectionIndex;
        item.itemIndex = ItemIndex;
        if (this.hasResponses) {
          this.items.push(item);
          this.formateQuestions(item, item.questions, sectionIndex, ItemIndex);
        } else {
          this.items.push(item);
          item_responses.push({
            item_id: item.id,
            question_responses: this.formateQuestions(
              item,
              item.questions,
              sectionIndex,
              ItemIndex
            )
          });
        }
      });
      return item_responses;
    },
    formateQuestions(item, questions, s, i) {
      const question_responses = [];
      questions.forEach((question, q) => {
        if (this.hasResponses) {
          this.getContinueDo(s, i, q, item);
        } else {
          if (Number(question.isDelete) == 1) {
            question.response_points = [];
          }
          const length = question.response_points.length
            ? question.response_points.length
            : 0;
          question_responses.push({
            question_id: question.id,
            response: this.initResponse(question.answer_mode, length)
          });
        }
      });
      return question_responses;
    },
    getContinueDo(s, i, q, item) {
      const userAnwer = this.assessmentResponse.section_responses[s]
        .item_responses[i].question_responses[q].response;
      const doItem = userAnwer.some(item => {
        return item != "";
      });
      if (!this.continueDo && !doItem) {
        this.continueDo = {
          sectionsIndex: s,
          itemIndex: Number(item.seq),
          questionIndex: q
        };
      }
    },
    slideToContinue() {
      if (this.continueDo) {
        const item = this.items[this.continueDo.itemIndex - 1];
        const keys = {
          sectionsIndex: this.continueDo.sectionsIndex,
          itemIndex: item.seq,
          questionIndex: this.continueDo.questionIndex,
          itemId: item.id
        };
        this.slideTo(keys);
      }
    },
    setSourceMap(item, question, s, i, q) {
      item.sectionIndex = s;
      item.ItemIndex = i;
      question.sectionIndex = s;
      question.ItemIndex = i;
      question.questionIndex = q;
      this.sourceMap[`item_${item.id}`] = item;
      this.sourceMap[`question_${question.id}`] = question;
    },
    showcard() {
      this.cardShow = true;
    },
    // 初始化答案数据
    initResponse(mode, lengths) {
      let response = [];
      if (mode === "text") {
        response = Array(lengths).fill("");
      } else if (
        mode === "single_choice" ||
        mode === "rich_text" ||
        mode === "true_false"
      ) {
        response = [""];
      }
      return response;
    },
    getUserAnwer(s, i) {
      return this.section_responses[s].item_responses[i];
    },
    changeAnswer(value, keys) {
      this.section_responses[keys[0]].item_responses[keys[1]] = value;
    },
    // 开始计时
    countTime() {
      const saveProgressInterval = 180; // 单位秒

      if (this.intervalId != null) {
        return;
      }
      // 计时器为空，操作
      this.intervalId = setInterval(() => {
        this.count += 1;
        if (this.count % saveProgressInterval === 0) {
          this.trigger();
        }
      }, 1000);
    },
    // 提交前提示
    submitPaper(flge = false) {
      const noAnswerNum = this.judgelNoAnswer(this.section_responses);
      const title = flge ? "提交" : "保存进度";
      let message = flge
        ? "题目已经做完，确认提交吗?"
        : "题目已经做完，确认保存进度，下次继续吗?";
      if (noAnswerNum) {
        message = flge
          ? `还有${noAnswerNum}题未做，确认提交吗？`
          : `还有${noAnswerNum}题未做，确认保存进度，下次继续吗？`;
      }
      return new Promise((resolve, reject) => {
        Dialog.confirm({
          title: title,
          cancelButtonText: this.isAnswerFinished == 1 ? '取消' : "确认",
          confirmButtonText: this.isAnswerFinished == 1 ? '退出答题' : "继续答题",
          message: this.isAnswerFinished == 1 ? '题目已经做完，是否返回列表？' : message,
          getContainer: "#ibs-item-bank",
          className: 'backDialog'
        })
          .then(() => {
            if (this.isAnswerFinished == 1) {
              this.goResult()
              document.getElementsByClassName('backDialog')[0].remove();
              resolve();
            } else {
              document.getElementsByClassName('backDialog')[0].remove();
              resolve();
            }
          })
          .catch(() => {
            if (this.isAnswerFinished == 1) {
              document.getElementsByClassName('backDialog')[0].remove();
            } else {
              if (this.brushDo.exerciseModes == '1') {
                this.finishAnswer()
              } else {
                flge ? this.answerData() : this.saveAnswerData();
                document.getElementsByClassName('backDialog')[0].remove();
                resolve();
              }
            }

          });
      });
    },
    // 三分钟保存进度
    trigger() {
      const data = this.getResponse();
      data.used_time = this.count;
      this.$emit("timeSaveAnswerData", data);
      Toast({
        message: "已保存",
        getContainer: () => {
          return document.querySelector("#ibs-item-bank");
        }
      });
    },
    // 自动提交
    reachTimeSubmitAnswerData() {
      const finalData = this.getResponse();
      finalData.used_time = this.count + Number(this.answerRecord.used_time);
      this.$emit("reachTimeSubmitAnswerData", finalData);
      this.clearTime();
    },
    // 手动保存进度
    saveAnswerData() {
      const finalData = this.getResponse();
      finalData.used_time = this.count + Number(this.answerRecord.used_time);
      this.$emit("saveAnswerData", finalData);
      this.clearTime();
    },
    // 手动提交
    answerData() {
      const finalData = this.getResponse();
      finalData.used_time = this.count + Number(this.answerRecord.used_time);
      this.$emit("getAnswerData", finalData);
      this.clearTime();
    },
    clearTime() {
      // 立即提交的时候清空计时器;
      clearInterval(this.intervalId); // 清除计时器
      this.intervalId = null; // 设置为null
    },
    getResponse() {
      const finalData = {};
      finalData.assessment_id = this.assessment.id;
      finalData.answer_record_id = this.answerRecord.id;
      finalData.section_responses = this.section_responses;
      finalData.exerciseId = this.exerciseId;
      finalData.type = 'exercise'
      return finalData;
    },
    changeReviewedCount(reviewedCount, isAnswerFinished) {
      this.reviewedCount = reviewedCount;
      this.isAnswerFinished = isAnswerFinished
    },
    finishAnswer() {
      Api.finishAnswer({
        query: {
          id: this.brushDo.recordId
        }
      }).then(() =>{
        this.goResult()
      }).catch(err =>{
        Toast.fail(err.message)
      })
    },
    goResult() {
      const isLeave = true;
      const query = {
        type: 'chapter',
        title: this.$route.query.title,
        exerciseId: this.$route.query.exerciseId,
        categoryId: this.$route.query.categoryId,
        moduleId: this.$route.query.moduleId,
        isLeave,
        backUrl: `/item_bank_exercise/${this.$route.query.exerciseId}?categoryId=${this.$route.query.categoryId}`,
      };
      const answerRecordId = this.assessmentResponse.answer_record_id;
      this.$router.replace({
        path: `/brushResult/${answerRecordId}`,
        query,
      });
    },
		changeTouch(val) {
			this.touchable = val
		},
		noChangeTouch(val) {
			this.touchable = val
		},
		updataIsAnswerFinished(val, flag, data, questionId) {
			if (!this.reviewedQuestion.includes(questionId)) {
				this.reviewedQuestion.push(data)
			}
			this.isAnswerFinished = val
			if (flag) {
				this.reviewedCount = this.reviewedCount + 1
			}
		},
		submitedQuestionStatus(data) {
			this.fillStatus.push(data)
		},
		changeEssayRadio(data) {
			const repeatRadio =  this.EssayRadio.filter(item => item.questionId === data.questionId)
			if (repeatRadio.length === 0) {
				this.EssayRadio.push(data)
			}
		}
  }
};
</script>
