<template>
  <div class="testResults">
    <e-loading v-if="isLoading"></e-loading>
    <div class="result-data" ref="data" v-if="result">
      <div class="result-data__item">
        本次得分
        <div class="result-data__bottom data-number-orange data-medium" v-if="isReadOver"><span class=" data-number">{{ result.score }}</span>分
        </div>
        <div class="result-data__bottom data-text-blue" v-else>待批阅</div>
      </div>
      <div class="result-data__item">
        正确率
        <div class="result-data__bottom data-number-green data-medium" v-if="isReadOver"><span class=" data-number">{{ result.rightRate }}</span>%
        </div>
        <div class="result-data__bottom data-text-blue" v-else>待批阅</div>
      </div>
      <div class="result-data__item">
        做题用时
        <div class="result-data__bottom data-number-gray data-medium"><span class=" data-number">{{ usedTime }}</span>分钟
        </div>
      </div>
    </div>
    <div class="result-tag" ref="tag">
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-green"></div>
        正确
      </div>
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-orange"></div>
        错误
      </div>
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-gray"></div>
        未作答
      </div>
      <div class="result-tag-item clearfix" v-show="!isReadOver">
        <div class="result-tag-item__circle circle-brown"></div>
        待批阅
      </div>
    </div>

    <div class="result-subject" :style="{height: calHeight}">
      <van-panel class="result-panel" v-for="(keyItem, index) in question_type_seq" :key="index" :title="obj[keyItem]">
        <ul class="result-list">
          <li :class="[ 'result-list__item testpaper-number', `circle-${color[item.status]}`]"
              v-for="(item, index) in subjectList[keyItem]" :key=index>{{ item.seq }}
          </li>
        </ul>
      </van-panel>

      <div class="result-footer" ref="footer">
        <van-button
          class="result-footer__btn"
          type="primary"
          :style="{marginRight: (isReadOver && !doTimes) ? '2vw' : 0}"
          @click="viewAnalysis()">
          查看解析
        </van-button>
        <van-button
          v-if="again && isReadOver && doTimes==0"
          class="result-footer__btn" type="primary"
          @click="startTestpaper()">
          再考一次
        </van-button>
        <van-button
          v-if="!again && remainTime && isReadOver && doTimes==0"
          class="result-footer__btn" type="primary"
          disabled
          style="line-height: 21px">
          在{{remainTime}}后可以再考一次
        </van-button>
      </div>
    </div>
  </div>
</template>

<script>
  import Api from '@/api';
  import { mapState, mapMutations, mapActions } from 'vuex';
  import * as types from '@/store/mutation-types';
  import examMixin from '@/mixins/lessonTask/exam.js';
  import { getdateTimeDown } from '@/utils/date-toolkit.js';

  export default {
    name: 'testpaperResult',
    data() {
      return {
        isReadOver: false, //是否已批阅
        resultId: null, // 考试结果ID
        again: 0,   // 是否再考一次
        result: {}, // 返回的考试结果对象
        calHeight: null,  // 题目列表高度
        subjectList: {},  // 题目列表对象
        question_type_seq: [],  // 考试已有题型
        targetId: null, // 任务ID
        doTimes: null, // 考试允许次数
        redoInterval: null, // 重考间隔
        remainTime: null,   // 再次重考剩余时间
        timeMeter: null,// 重考间隔倒计时
        testpaperTitle: null,// 考试题目
        obj: {              // 题型判断
          'single_choice': '单选题',
          'choice': '多选题',
          'essay': '问答题',
          'uncertain_choice': '不定项选择题',
          'determine': '判断题',
          'fill': '填空题',
          'material': '材料题'
        },
        color: {              // 题号标签状态判断
          'right': 'green',
          'none': 'brown',
          'wrong': 'orange',
          'partRight': 'orange',
          'noAnswer': 'gray',
        }
      };
    },
    mixins: [examMixin],
    computed: {
      ...mapState({
        isLoading: state => state.isLoading,
        user: state => state.user
      }),
      usedTime: function () {
        const timeInterval = parseInt(this.result.usedTime) || 0;
        return timeInterval <= 60 ? 1 : Math.round(timeInterval / 60);
      }
    },
    watch: {
      doTimes: function () {
        this.calSubjectHeight();
      },
    },
    created() {
      this.getTestpaperResult();
    },
    beforeRouteEnter(to, from, next) {
      document.getElementById('app').style.background = '#f6f6f6';
      next();
    },
    beforeRouteLeave(to, from, next) {
      document.getElementById('app').style.background = '';
      next();
    },
    beforeDestroy() { //清除定时器
      this.clearTime();
    },
    methods: {
      ...mapMutations({
        setNavbarTitle: types.SET_NAVBAR_TITLE
      }),
      ...mapActions('course', [
        'handExamdo',
      ]),
      async getTestpaperResult() {
        await Api.testpaperResult({
          query: {
            resultId: this.$route.query.resultId
          }
        })
          .then(res => {
            this.result = res.testpaperResult;
            this.question_type_seq = res.testpaper.metas.question_type_seq;
            this.isReadOver = this.result.status === 'finished';
            this.getSubjectList(res.items);
            this.calSubjectHeight();

            this.canDoing(this.result, this.user.id)
              .then(() => {
                this.startTestpaper('KeepDoing');
              })
              .catch(({ answer, endTime }) => {
                this.submitExam(answer, endTime);
              });
          });
        this.getInfo();
      },
      judgeTime() {
        const interval = this.redoInterval;
        let i = 0;
        if (interval == 0) {
          this.again = true;
          return;
        }
        const intervalTimestamp = parseInt(interval) * 60 * 1000;
        const nowTimestamp = new Date().getTime();
        const checkedTime = parseInt(this.result.checkedTime) * 1000;
        const sumTime = checkedTime + intervalTimestamp;
        this.again = nowTimestamp >= sumTime ? true : false;
        if (!this.again) {
          this.timeMeter = setInterval(() => {
            i = i++;
            this.remainTime = getdateTimeDown(sumTime, i);
            if (this.remainTime == '') {
              this.again = true;
              this.clearTime();
            }
          }, 1000);
        }
      },
      getSubjectList(resData) {
        for (let i in resData) {
          let final = [];
          resData[i].forEach((one) => {
            if (i === 'material') {
              one.subs.forEach((item) => {
                this.getStatus(item, final);
              });
            } else {
              this.getStatus(one, final);
            }
          });
          this.subjectList[i] = final;
        }
      },
      calSubjectHeight() {
        this.$nextTick(() => {
          const dataHeight = this.$refs.data.offsetHeight + this.$refs.tag.offsetHeight + 46;
          const allHeight = document.documentElement.clientHeight;
          const footerHeight = this.$refs.footer.offsetHeight || 0;
          const finalHeight = allHeight - dataHeight - footerHeight;
          this.calHeight = `${finalHeight}px`;
        });
      },
      getStatus(data, arr) {
        let obj = {};
        obj.seq = data.seq;
        if (data.testResult) {
          obj.status = data.testResult.status;
        } else {
          obj.status = 'noAnswer';
        }
        arr.push(obj);
      },
      submitExam(answer, endTime) {
        endTime = endTime ? endTime : new Date().getTime();
        let datas = {
          answer,
          resultId: this.result.id,
          userId: this.user.id,
          beginTime: Number(this.result.beginTime),
          endTime
        };
        //交卷+跳转到结果页
        this.handExamdo(datas)
          .then(res => {
            this.$router.replace({
              name: 'testpaperResult',
              query: {
                resultId: this.$route.query.resultId,
                testId: this.$route.query.testId,
                targetId: this.$route.query.targetId
              }
            });
          })
          .catch((err) => {
            Toast.fail(err.message);
          });
      },
      clearTime() {
        clearInterval(this.timeMeter);
        this.timeMeter = null;
      },
      startTestpaper() {
        this.$router.replace({
          name: 'testpaperDo',
          query: {
            testId: this.result.testId,
            targetId: this.targetId,
            title: this.testpaperTitle,
            action: 'redo'
          },
          params: {
            KeepDoing: true
          }
        });
      },
      getInfo() {
        this.testId = this.$route.query.testId;
        this.targetId = this.$route.query.targetId;
        Api.testpaperIntro({
          params: {
            targetId: this.targetId,
            targetType: 'task'
          },
          query: {
            testId: this.testId
          }
        })
          .then(res => {
            this.testpaperTitle = res.task.title;
            this.setNavbarTitle(res.task.title);
            this.doTimes = Number(res.task.activity.testpaperInfo.doTimes);
            this.redoInterval = Number(res.task.activity.testpaperInfo.redoInterval);
            this.judgeTime();
          });
      },
      //查看解析
      viewAnalysis() {
        this.$router.push({
          name: 'testpaperAnalysis',
          query: {
            resultId: this.$route.query.resultId,
            title: this.testpaperTitle,
          }
        });
      }
    }
  };
</script>
