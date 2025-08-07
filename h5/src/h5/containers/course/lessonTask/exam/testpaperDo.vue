<template>
  <div class="paper-swiper">
    <out-focus-mask
      :type="outFocusMaskType"
      :isShow="isShowOutFocusMask"
      :reportType="reportType"
      @outFocusMask="outFocusMask"
    ></out-focus-mask>
    <e-loading v-if="isLoading" />

    <item-bank
      v-if="info.length > 0"
      :current.sync="cardSeq"
      :info="info"
      :answer.sync="answer"
      :slide-index.sync="slideIndex"
      :all="info.length"
      :exercise-info="testpaperResult"
    />

    <!-- 引导页 -->
    <guide-page />

    <!-- 底部 -->
    <ibs-footer
      :mode="'do'"
      :show-save-process-btn="false"
      @showcard="showCard"
      @submitPaper="submitPaper"
    />

    <!-- 答题卡 -->
    <van-popup v-model="cardShow" position="bottom">
      <div v-if="info.length > 0" class="card">
        <div class="card-title">
          <div>
            <span class="card-finish">{{ $t('courseLearning.completed') }}</span>
            <span class="card-nofinish">{{ $t('courseLearning.notCompleted') }}</span>
          </div>
          <i class="iconfont icon-no" @click="cardShow = false" />
        </div>
        <div class="card-list">
          <div v-for="(cards, name) in items" :key="name" class="card-item">
            <div class="card-item-title">{{ name | type }}</div>
            <div v-if="name != 'material'" class="card-item-list">
              <div
                v-for="craditem in items[name]"
                :class="[
                  'list-cicle',
                  formatStatus(craditem.type, craditem.id) == 1
                    ? 'cicle-active'
                    : '',
                ]"
                :key="craditem.id"
                @click="slideToNumber(craditem.seq)"
              >
                {{ craditem.seq }}
              </div>
            </div>
            <div v-if="name == 'material'" class="card-item-list">
              <template v-for="craditem in items[name]">
                <div
                  v-for="materialitem in craditem.subs"
                  :class="[
                    'list-cicle',
                    formatStatus(materialitem.type, materialitem.id) == 1
                      ? 'cicle-active'
                      : '',
                  ]"
                  :key="materialitem.id"
                  @click="slideToNumber(materialitem.seq)"
                >
                  {{ materialitem.seq }}
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </van-popup>

    <!-- 考试时间 -->
    <div :class="['time', timeWarn ? 'warn' : '']">
      {{ time }}
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState, mapActions } from 'vuex';
import * as types from '@/store/mutation-types';
import { Toast, Dialog } from 'vant';
import guidePage from '../component/guide-page';
import itemBank from '../component/itemBank';
import { getCountDown } from '@/utils/date-toolkit.js';
import examMixin from '@/mixins/lessonTask/exam.js';
import testMixin from '@/mixins/lessonTask/index.js';
import report from '@/mixins/course/report';
import OutFocusMask from '@/components/out-focus-mask.vue';
import i18n from '@/lang';
import IbsFooter from '@/src/components/common/footer.vue';

let backUrl = '';

export default {
  name: 'TestpaperDo',
  components: {
    IbsFooter,
    itemBank,
    guidePage,
    OutFocusMask,
  },
  filters: {
    type: function(type) {
      switch (type) {
        case 'single_choice':
          return i18n.t('courseLearning.singleChoice');
        case 'choice':
          return i18n.t('courseLearning.choice');
        case 'essay':
          return i18n.t('courseLearning.essay');
        case 'uncertain_choice':
          return i18n.t('courseLearning.uncertainChoice');
        case 'determine':
          return i18n.t('courseLearning.determine');
        case 'fill':
          return 'courseLearning.fill';
        case 'material':
          return i18n.t('courseLearning.material');
      }
    },
  },
  mixins: [examMixin, testMixin, report],
  data() {
    return {
      cardSeq: 0, // 点击题卡要滑动的指定位置的索引
      testpaper: {},
      testpaperResult: {},
      scene: {},
      info: [], // 试卷信息
      answer: {}, // 答案
      cardShow: false, // 答题卡显示标记
      items: {}, // 分组题目
      time: null, // 倒计时
      timeMeter: null, // 计时器
      timeWarn: false, // 倒计时警告标记
      isHandExam: false, // 是否已交卷
      localtime: null, // 本地时间计时器
      localtimeName: null, // 本地存储的时间key值
      localanswerName: null, // 本地存储的答案key值
      localuseTime: null,
      lastAnswer: null, // 本地存储的答案
      lastTime: null, // 本地存储的时间
      startTime: null,
      backUrl: '',
      slideIndex: 0, // 题库组件当前所在的划片位置
      forceLeave: false, // 强制离开考试
      interval: null,
      loadTime: null,
    };
  },
  watch: {
    answer: {
      handler() {
        window.localStorage.setItem(this.localanswerName, JSON.stringify(this.answer))
      },
      deep: true
    }
  },
  async mounted() {
    this.initReport();
    this.getData();
    this.saveAnswerInterval();
    this.loadTime = new Date().getTime();
  },
  beforeRouteEnter(to, from, next) {
    if (from.fullPath === '/') {
      backUrl = '/';
    } else {
      backUrl = '';
    }
    next();
  },
  beforeRouteLeave(to, from, next) {
    this.clearTime();
    this.interval && clearInterval(this.interval)
    // 可捕捉离开提醒
    if (
      this.info.length == 0 ||
      this.isHandExam ||
      this.forceLeave ||
      this.testpaperResult.status != 'doing'
    ) {
      next();
    } else {
      this.submitPaper()
        .then(() => {
          next();
        })
        .catch(() => {
          next(false);
        });
    }
  },
  beforeDestroy() {
    // 清除定时器
    this.clearTime();
    Dialog.close();
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
      user: state => state.user,
      selectedPlanId: state => state.course.selectedPlanId,
    }),
  },
  methods: {
    ...mapActions(['setCloudAddress']),
    ...mapActions('course', ['handExamdo', 'saveAnswerdo']),
    showCard() {
      this.cardShow = true;
    },
    // 初始化上报数据
    initReport() {
      this.initReportData(
        this.selectedPlanId,
        this.$route.query.targetId,
        'testpaper',
      );
    },
    // 请求接口获取数据
    getData() {
      const testId = this.$route.query.testId;
      const targetId = this.$route.query.targetId;
      const action = this.$route.query.action;
      Api.getExamInfo({
        query: {
          testId,
        },
        data: {
          action,
          targetId: targetId,
          targetType: 'task',
        },
      })
        .then(res => {
          this.afterGetData(res);
        })
        .catch(err => {

          /**
           * 4032207:考试正在批阅中
           * 4032204：考试只能考一次，不能重复考试
           */
          if (err.code == 4032207 || err.code == 4032204) {
            this.toIntro();
          } else {
            Toast.fail(err.message);
            // 跳转到结果页
            this.showResult();
          }
        });
    },
    // 获取到数据后进行操作
    afterGetData(res) {
      // 设置导航栏题目
      this.$store.commit(types.SET_NAVBAR_TITLE, this.$route.query.title);
      // 赋值数据
      this.items = res.items;
      this.testpaper = res.testpaper;
      this.testpaper.courseId = res.courseId;
      res.testpaperResult.limitedTime = Number(res.testpaperResult.limitedTime);
      this.testpaperResult = res.testpaperResult;
      this.scene = res.scene;

      // 判断是否做题状态
      if (this.isDoing()) {
        return;
      }

      this.localanswerName = `${this.user.id}-${this.testpaperResult.id}`;
      this.localtimeName = `${this.user.id}-${this.testpaperResult.id}-time`;
      this.lastTime = localStorage.getItem(this.localtimeName);
      this.lastAnswer = JSON.parse(localStorage.getItem(this.localanswerName));

      // 处理数据格式
      this.formatData(res);

      this.interruption();

      // 本地实时存储时间
      this.saveTime();

      // 如果有限制考试时长，开始计时
      this.timer();
    },
    // 判断是否做题状态
    isDoing() {
      if (this.testpaperResult.status != 'doing') {
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
        this.canDoing(this.testpaperResult, this.user.id)
          .then(() => {})
          .catch(({ answer, endTime }) => {
            this.submitExam(answer, endTime);
          });
      }
    },
    // 遍历数据类型去做对应处理
    formatData(res) {
      const paper = res.items;
      Object.keys(paper).forEach(key => {
        if (key != 'material') {
          paper[key].forEach(item => {
            const detail = this.sixType(item.type, item, this.lastAnswer);
            this.$set(this.answer, item.id, detail.answer);
            this.info.push(detail.item);
          });
        }
        if (key == 'material') {
          // 材料题下面有子题需要特殊处理
          paper[key].forEach(item => {
            const title = Object.assign({}, item, { subs: '' });
            item.subs.forEach((sub, index) => {
              sub.parentTitle = title; // 材料题题干
              sub.parentType = item.type; // 材料题题型
              sub.materialIndex = index + 1; // 材料题子题的索引值，在页面要显示
              sub.attachments = sub.attachments.concat(item.attachments)

              const detail = this.sixType(sub.type, sub, this.lastAnswer);
              this.$set(this.answer, sub.id, detail.answer);
              this.info.push(detail.item);
            });
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
    // 考试倒计时
    timer(timeStr) {
      let time = null;
      if (this.testpaper.examMode == '0') {
        if (this.scene.valid_period_mode == '3') {
          time = this.scene.end_time * 1000 - new Date().getTime();
          if (time <= 0) {
            return;
          }
        } else {
          time = this.testpaperResult.limitedTime * 60 * 1000;
          if (time <= 0) {
            return;
          }
          // 如果考试过程中中断，剩余时间=考试限制时间-中断时间
          if (this.lastTime) {
            const gotime = Math.ceil(
              new Date().getTime() - this.testpaperResult.beginTime * 1000,
            );
            time = time - gotime;
          }
        }

        let i = 0;
        this.timeMeter = setInterval(() => {
          const { hours, minutes, seconds } = getCountDown(time, i++);

          this.time = `${hours}:${minutes}:${seconds}`;

          if (hours == 0 && minutes == 0 && seconds < 60) {
            this.timeWarn = true;
          }

          if (
            (Number(hours) == 0 &&
              Number(minutes) == 0 &&
              Number(seconds) == 0) ||
            Number(seconds) < 0
          ) {
            this.clearTime();
            this.submitExam();
          }
        }, 1000);

        return;
      }

      if (this.testpaper.examMode == '1') {
        const usedTime = this.testpaperResult.usedTime;
        const localUsedTime = localStorage.getItem(this.localuseTime);
        let time = Math.max(usedTime, localUsedTime) * 1000;

        if (usedTime > localUsedTime) {
          localStorage.setItem(this.localuseTime, usedTime)
        }

        const { hours, minutes, seconds } = getCountDown(time, 0);

        this.time = `${hours}:${minutes}:${seconds}`;

        this.timeMeter = setInterval(() => {
          time += 1000

          const { hours, minutes, seconds } = getCountDown(time, 0);

          this.time = `${hours}:${minutes}:${seconds}`;

          if (this.scene.valid_period_mode != '3') {
            if (this.testpaper.limitedTime > 0 && (time === this.testpaper.limitedTime * 60 * 1000)) {
              Dialog.confirm({
                cancelButtonText: this.$t('courseLearning.handInThePaper'),
                confirmButtonText: this.$t('courseLearning.continueAnswer'),
                message: this.$t('courseLearning.examTotalTime', { number: parseInt(minutes) }),
              }).catch(() => {
                this.submitExam();
              })
            }
          } else {
            if (time === this.scene.end_time * 1000 - this.loadTime) {
              Dialog.confirm({
                cancelButtonText: this.$t('courseLearning.handInThePaper'),
                confirmButtonText: this.$t('courseLearning.continueAnswer'),
                message: this.$t('courseLearning.examTotalTime', { number: parseInt(minutes) }),
              }).catch(() => {
                this.submitExam();
              })
            }
          }
        }, 1000);
      }
    },
    // 清空定时器
    clearTime() {
      clearInterval(this.timeMeter);
      this.timeMeter = null;

      clearInterval(this.localtime);
      this.localtime = null;
    },
    // 提交试卷
    submitPaper() {
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

      this.saveAnswerInterval();

      return new Promise((resolve, reject) => {
        Dialog.confirm({
          title: this.$t('courseLearning.handInThePaper'),
          cancelButtonText: this.$t('courseLearning.confirmSubmission'),
          confirmButtonText: this.$t('courseLearning.check'),
          message: message,
					className: 'backDialog'
        })
          .then(res => {
						// 销毁dialog Dom
						document.getElementsByClassName('backDialog')[0].remove();
            // 显示答题卡
            this.cardShow = true;
            reject(res);
          })
          .catch(() => {
						document.getElementsByClassName('backDialog')[0].remove();
            this.clearTime();
            this.submitExam(answer)
              .then(res => {
                resolve();
              })
              .catch(err => {
                reject(err);
              });
          });
      });
    },
    // dispatch给store，提交答卷
    submitExam(answer, endTime) {
      // 如果已经遍历过answer就不要再次遍历，直接用
      if (!answer) {
        answer = JSON.parse(JSON.stringify(this.answer));
        Object.keys(answer).forEach(key => {
          answer[key] = answer[key].filter(t => t !== '');
        });
      }
      // 考试结束时间，没有传结束时间则为当前时间
      endTime = endTime || new Date().getTime();

      const datas = {
        answer,
        resultId: this.testpaperResult.id,
        userId: this.user.id,
        endTime,
        beginTime: Number(this.testpaperResult.beginTime),
        courseId: this.$route.query.courseId
      };

      return new Promise((resolve, reject) => {
        this.handExamdo(datas)
          .then(res => {
            this.isHandExam = true;
            resolve();
            // 跳转到结果页
            this.showResult();
          })
          .catch(err => {
            if (err.code == 50095204) {
              Dialog.confirm({
                title: '你已提交过答题，当前页面无法重复提交',
                showCancelButton: false,
                confirmButtonText: '退出答题'
              }).then(() => this.exitPage())
              return
            }

            reject(err);
            Toast.fail(err.message);
            this.isHandExam = true;
            this.showResult();
          }).finally(() => {
            localStorage.removeItem(this.localuseTime)
          })
      });
    },
    saveAnswerInterval() {
      clearInterval(this.interval);

      this.interval = setInterval(() => {
        this.saveAnswerAjax();
      }, 30 * 1000)
    },
    saveAnswerAjax() {
      const used_time = localStorage.getItem(this.localuseTime) || 0;
      this.saveAnswerdo({
        admission_ticket: this.testpaperResult.admission_ticket,
        answer: JSON.parse(JSON.stringify(this.answer)),
        resultId: this.testpaperResult.id,
        used_time,
        courseId: this.$route.query.courseId
      })
      .catch((error) => {
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
      this.$router.push(`/course/${this.testpaper.courseId}`)
    },
    // 实时存储时间
    saveTime() {
      this.localuseTime = `${this.user.id}-${this.testpaperResult.id}-usedTime`;
      this.localtime = setInterval(() => {
        const time = localStorage.getItem(this.localuseTime) || 0;
        localStorage.setItem(this.localuseTime, Number(time) + 1);
        localStorage.setItem(this.localtimeName, new Date().getTime());
      }, 1000);
    },
    showResult() {
      this.$router.replace({
        name: 'testpaperResult',
        query: {
          resultId: this.testpaperResult.id,
          testId: this.$route.query.testId,
          targetId: this.$route.query.targetId,
          backUrl: backUrl,
        },
      });
    },
    toIntro() {
      this.$router.replace({
        name: 'testpaperIntro',
        query: {
          testId: this.$route.query.testId,
          targetId: this.$route.query.targetId,
        },
      });
    },
  },
};
</script>
