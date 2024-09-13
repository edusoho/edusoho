<template>
  <div class="paper-swiper">
    <out-focus-mask
      :type="outFocusMaskType"
      :isShow="isShowOutFocusMask"
      :reportType="reportType"
      @outFocusMask="outFocusMask"
    ></out-focus-mask>
    <e-loading v-if="isLoading" />

    <!-- 做题 -->
    <item-bank
      v-if="info.length > 0"
      :current.sync="cardSeq"
      :info="info"
      :answer.sync="answer"
      :show-score="false"
      :slide-index.sync="slideIndex"
      :all="info.length"
      :exerciseInfo="exerciseInfo"
      :type="$route.query.type"
      :assessment_id="exerciseInfo.testId"
      :exerciseMode="exerciseMode"
      :admission_ticket="exerciseInfo.admission_ticket"
      :mode="mode"
      @getData="getData"
      @goResults="goResults"
      @reviewedCount="reviewedCount"
    />

    <!-- 引导页 -->
    <guide-page />

    <!-- 底部 -->
    <div v-if="this.exerciseMode === '0'" class="paper-footer">
      <div>
        <span @click="cardShow = true">
          <i class="mb-8 iconfont icon-Questioncard" />
          {{ $t('courseLearning.questionCard') }}
        </span>
      </div>
      <div>
        <span @click="submitpaper">
          <i class="mb-8 iconfont icon-submit" />
          {{ $t('courseLearning.submit2') }}
        </span>
      </div>
    </div>

    <!-- 答题卡 -->
    <van-popup v-model="cardShow" position="bottom" :style="{ height: '100%' }">
      <div v-if="info.length > 0" class="card">
        <div class="card-title">
          <div>
            <span class="card-finish">{{ $t('courseLearning.completed') }}</span>
            <span class="card-nofinish">{{ $t('courseLearning.notCompleted') }}</span>
          </div>
          <i class="iconfont icon-no" @click="cardShow = false" />
        </div>
        <div class="card-list">
          <div class="card-homework-item">
            <div class="card-item-list">
              <div
                v-for="cards in info"
                :key="cards.id"
                :class="[
                  'list-cicle',
                  formatStatus(cards.type, cards.id) == 1 ? 'cicle-active' : '',
                ]"
                @click="slideToNumber(cards.seq)"
              >
                {{ cards.seq }}
              </div>
            </div>
          </div>
        </div>
      </div>
      <van-button
        v-if="exerciseMode === '1'"
        class="end-answer__btn"
        type="primary"
        @click="endAnswer"
        >{{ $t('courseLearning.endAnswer') }}</van-button
      >
      <van-button
        v-if="exerciseMode === '1' && revieweNumLast"
        class="end-answer__btn"
        type="primary"
        @click="goResults"
        >{{ $t('courseLearning.viewResult2') }}</van-button
      >
    </van-popup>
  </div>
</template>

<script>
import Api from '@/api';
import * as types from '@/store/mutation-types';
import { mapState, mapMutations, mapActions } from 'vuex';
import { Toast, Dialog } from 'vant';
import guidePage from '../component/guide-page';
import itemBank from '../component/itemBank';
import exerciseMixin from '@/mixins/lessonTask/exercise.js';
import testMixin from '@/mixins/lessonTask/index.js';
import report from '@/mixins/course/report';
import OutFocusMask from '@/components/out-focus-mask.vue';

// 由于会重定向到说明页或者结果页，为了避免跳转后不能返回，添加backUrl机制
let backUrl = '';
export default {
  name: 'ExerciseDo',
  components: {
    itemBank,
    guidePage,
    OutFocusMask,
  },
  mixins: [exerciseMixin, testMixin, report],
  data() {
    return {
      info: [], // 练习信息
      answer: {}, // 答案
      lastAnswer: null,
      cardSeq: 0, // 点击题卡要滑动的指定位置的索引
      exercise: null,
      cardShow: false, // 答题卡显示标记
      localanswerName: null,
      localtimeName: null,
      lastUsedTime: null,
      usedTime: null, // 使用时间，本地实时计时
      isHandExercise: false, // 是否已经交完练习
      slideIndex: 0, // 题库组件当前所在的划片位置
      forceLeave: false,
      interval: null,
      exerciseMode: this.$route.query.exerciseMode,
      exerciseInfo: null,
      isLeave: false,
      mode: 'exercise',
      revieweNumLast: false
    };
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
      user: state => state.user,
    }),
  },
  mounted() {
    this.getData();
    this.initReport();
    // 一题一答不需要自动保存
    if(this.exerciseMode === '0'){
      this.saveAnswerInterval();
    }
  },
  beforeRouteEnter(to, from, next) {
    document.getElementById('app').style.background = '#F5F5F7';

    // 通过链接进来
    if (from.fullPath === '/') {
      backUrl = '/';
    } else {
      backUrl = '';
    }
    next();
  },
  beforeRouteLeave(to, from, next) {
    document.getElementById('app').style.background = '';

    this.interval && clearInterval(this.interval)
    // 可捕捉离开提醒
    if (
      this.info.length == 0 ||
      this.isHandExercise ||
      this.forceLeave ||
      this.exercise.status != 'doing' ||
      this.isLeave
    ) {
      next();
    } else {
      if (this.submitpaper()) {
        next();
      } else {
        next(false);
      }
    }
  },
  beforeDestroy() {
    // 清除定时器
    this.clearTime();
    Dialog.close();
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE,
    }),
    ...mapActions('course', ['handExercisedo', 'saveAnswerdo']),
    // 请求接口获取数据
    getData() {
      const exerciseId = this.$route.query.exerciseId;
      const targetId = this.$route.query.targetId;
      Api.getExerciseInfo({
        query: {
          exerciseId,
        },
        data: {
          targetId,
          targetType: 'task',
          exerciseMode: this.exerciseMode
        },
      })
        .then(res => {
          this.exerciseMode = res.exerciseMode
          this.exerciseInfo = res
          this.afterGetData(res);
        })
        .catch(err => {
          const toast = Toast.fail(err.message);
          /**
           * 4036706:试卷正在批阅中
           */
          if (err.code == '4036706') {
            setTimeout(() => {
              this.toIntro();
              toast.clear();
            }, 2000);
          }
        });
    },
    // 初始化上报数据
    initReport() {
      this.initReportData(
        this.$route.query.courseId,
        this.$route.query.targetId,
        'exercise',
      );
    },
    // 获取到数据后进行操作
    afterGetData(res) {
      this.setNavbarTitle(res.paperName);

      this.exercise = res;

      // 判断是否做题状态
      if (this.isDoing()) {
        return;
      }

      this.getLocalData();

      this.formatData(res);

      this.interruption();

      this.saveTime();
    },
    // 判断是否做题状态
    isDoing() {
      if (this.exercise.status != 'doing') {
        this.showResult();
        return true;
      } else {
        return false;
      }
    },
    // 异常中断
    interruption() {
      if (!this.$route.params.KeepDoing) {
        // 异常中断或者刷新页面
        this.canDoing(this.exercise, this.user.id)
          .then(() => {})
          .catch(({ answer }) => {
            this.submitExercise(answer);
          });
      }
    },
    // 遍历数据类型去做对应处理
    formatData(res) {
      const paper = res.items;
      paper.forEach(item => {
        if (item.type != 'material') {
          const detail = this.sixType(item.type, item, this.lastAnswer);
          this.$set(this.answer, item.id, detail.answer);
          this.info.push(detail.item);
        }
        if (item.type == 'material') {
          const title = Object.assign({}, item, { subs: '' });
          item.subs.forEach((sub, index) => {
            sub.parentTitle = title; // 材料题题干
            sub.parentType = item.type; // 材料题题型
            sub.materialIndex = index + 1; // 材料题子题的索引值，在页面要显示

            const detail = this.sixType(sub.type, sub, this.lastAnswer);
            this.$set(this.answer, sub.id, detail.answer);
            this.info.push(detail.item);
          });
        }
      });
    },
    // 答题卡状态判断,finish 0是未完成  1是已完成
    formatStatus(type, id) {
      let finish = 0;
      // 单选题、多选题、不定项选择题、判断题
      if (
        (type == 'single_choice' ||
          type == 'choice' ||
          type == 'uncertain_choice' ||
          type == 'determine') &&
        this.answer[id].length > 0
      ) {
        finish = 1;
        return finish;
      }
      // 问答题
      if (type == 'essay' && this.answer[id][0] != '') {
        finish = 1;
        return finish;
      }
      // 填空题，规则：只要填了一个就算完成
      if (type == 'fill') {
        finish = Number(
          this.answer[id].some(item => {
            return item != '';
          }),
        );
        return finish;
      }
      return finish;
    },
    // 答题卡定位
    slideToNumber(num) {
      const index = Number(num);
      this.cardSeq = index;
      // 关闭弹出层
      this.cardShow = false;
    },
    // 获取本地数据
    getLocalData() {
      this.localanswerName = `exercise-${this.user.id}-${this.exercise.id}`;
      this.localuseTime = `exercise-${this.user.id}-${this.exercise.id}-usedTime`;
      this.lastAnswer = JSON.parse(localStorage.getItem(this.localanswerName));
    },
    // 实时存储时间
    saveTime() {
      let time = localStorage.getItem(this.localuseTime) || 0;
      this.usedTime = setInterval(() => {
        localStorage.setItem(this.localuseTime, ++time);
      }, 1000);
    },
    clearTime() {
      clearInterval(this.usedTime);
      this.usedTime = null;
    },
    // 提交练习
    submitpaper() {
      let index = 0;
      let message = this.$t('courseLearning.sureSubmit');
      const answer = JSON.parse(JSON.stringify(this.answer));
      Object.keys(answer).forEach(key => {
        // 去除空数据
        answer[key] = answer[key].filter(t => t !== '');
        // 统计未做个数
        if (answer[key].length === 0) {
          index++;
        }
      });

      if (index > 0) {
        message = this.$t('courseLearning.notSureSubmit', { number: index });
      }
      const confirmButtonText = this.revieweNumLast ? this.$t('courseLearning.viewResult2'): this.$t('courseLearning.check')
      const cancelButtonText = this.revieweNumLast ? this.$t('courseLearning.returnList') : this.$t('courseLearning.submitNow')

      this.saveAnswerInterval();

      Dialog.confirm({
        title: this.revieweNumLast ? '' : this.$t('courseLearning.submit2'),
        cancelButtonText: cancelButtonText,
        confirmButtonText: confirmButtonText,
        message: this.revieweNumLast ? this.$t('courseLearning.doYouToResults') : message,
        className: 'backDialog'
      })
        .then(() => {
          document.getElementsByClassName('backDialog')[0].remove();
          if(this.revieweNumLast) return this.goResults()
          // 显示答题卡
          this.cardShow = true;
          return false;
        })
        .catch(() => {
          document.getElementsByClassName('backDialog')[0].remove();
          this.clearTime();
          if(this.revieweNumLast) return this.toCourseList()
          // 提交练习
          if (this.exerciseMode === '1') {
            this.endCueentAnswer()
          } else {
            this.submitExercise(answer)
              .then(res => {
                return true;
              })
              .catch(() => {
                return false;
              });
          }
        });
      // })
    },
    // dispatch给store，提交答卷
    submitExercise(answer) {
      // 如果已经遍历过answer就不要再次遍历，直接用
      if (!answer) {
        answer = JSON.parse(JSON.stringify(this.answer));
        Object.keys(answer).forEach(key => {
          answer[key] = answer[key].filter(t => t !== '');
        });
      }

      const datas = {
        answer,
        exerciseId: this.$route.query.exerciseId,
        userId: this.user.id,
        exerciseResultId: this.exercise.id,
        courseId: this.$route.query.courseId
      };

      return new Promise((resolve, reject) => {
        this.handExercisedo(datas)
          .then(res => {
            this.isHandExercise = true;
            resolve();
            // 上报完成作业课时
            this.reprtData({ eventName: 'finish' });
            // 跳转到结果页
            this.showResult();
          })
          .catch(err => {
            /**
             * 4036705：已经提交过此次练习，直接去结果页
             */
            if (err.code == '4036705') {
              const toast = Toast.fail(err.message);
              setTimeout(() => {
                this.isHandExercise = true;
                toast.clear();
                resolve();
                this.showResult();
              }, 2000);
              return
            }

            if (err.code == '50095204') {
              // 试卷已提交 -- 退出答题
              Dialog.confirm({
                title: '你已提交过答题，当前页面无法重复提交',
                showCancelButton: false,
                confirmButtonText: '退出答题'
              }).then(() => this.exitPage())
              return
            }

            Toast.fail(err.message)
            reject(err);
          });
      });
    },
    saveAnswerInterval() {
      clearInterval(this.interval);
      this.interval = setInterval(() => {
        this.saveAnswerAjax()
      }, 30 * 1000)
    },
    saveAnswerAjax() {
      this.saveAnswerdo({
        admission_ticket: this.exercise.admission_ticket,
        answer: JSON.parse(JSON.stringify(this.answer)),
        resultId: this.exercise.id,
        courseId: this.$route.query.courseId
      }).catch((error) => {
        const { code: errorCode, message, traceId } = error;

        if (errorCode === 50095204) {
          // 试卷已提交 -- 退出答题
          Dialog.confirm({
            title: '你已提交过答题，当前页面无法重复提交',
            showCancelButton: false,
            confirmButtonText: '退出答题'
          }).then(() => this.exitPage())
          return
        }

        if (errorCode === 50095209) {
          // 不能同时多端答题
          Dialog.confirm({
            title: '有新答题页面，请在新页面中继续答题',
            showCancelButton: false,
            confirmButtonText: '确定'
          }).then(() => this.exitPage())
          return
        }

        if (traceId) {
          Dialog.confirm({
            title: '答题保存失败，请保存截图后，联系技术支持处理',
            message: `【${message}】【${traceId}】`,
            confirmButtonText: '退出答题'
          }).then(() => this.exitPage())
          return
        }

        Toast.fail(err.message);

        Dialog.confirm({
          title: '网络连接不可用，自动保存失败',
          showCancelButton: false,
          confirmButtonText: '重新保存'
        }).then(() => this.saveAnswerAjax())
      })
    },
    exitPage() {
      this.forceLeave = true
      this.$router.push(`/course/${this.exercise.courseId}`)
    },
    // 跳转到结果页
    showResult() {
      this.$router.replace({
        name: 'exerciseResult',
        query: {
          exerciseId: this.$route.query.exerciseId,
          exerciseResultId: this.exercise.id,
          taskId: this.$route.query.targetId,
          backUrl: backUrl,
          courseId: this.$route.query.courseId,
        },
      });
    },
    // 跳转到说明页
    toIntro() {
      this.$router.replace({
        name: 'exerciseIntro',
        query: {
          courseId: this.$route.query.courseId,
          taskId: this.$route.query.targetId,
          backUrl: backUrl,
        },
      });
    },
    // 结束提示框
    endAnswer() {
      Dialog.confirm({
        title: `是否结束本次答题`,
        confirmButtonText: '是',
        cancelButtonText: '否'
      })
      .then(() => this.endCueentAnswer())
      .catch(() => {
      });
    },
    toCourseList() {
      this.isLeave = true;
      this.$router.replace({
        path: `/course/${this.$route.query.courseId}`
      });
    },
    // 结束答题
    endCueentAnswer() {
      Api.finishAnswer({
        query: {
          id: this.exerciseInfo.id
        }
      }).then(res =>{
        this.isLeave = true;
        this.showResult()
      }).catch(err =>{
        Toast.fail(err.message)
      })
    },
    goResults() {
      this.isLeave = true;
      this.showResult()
    },
    reviewedCount() {
      this.revieweNumLast = true
    }
  },
};
</script>
<style scoped lang="scss">
/deep/.van-popup__close-icon--top-left {
  color: #333333;
}
</style>
