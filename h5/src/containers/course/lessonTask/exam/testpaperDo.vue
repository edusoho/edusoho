<template>
  <div class="paper-swiper">
    <e-loading v-if="isLoading"></e-loading>

    <item-bank
      v-if="info.length>0"
      :current.sync="cardSeq"
      :info="info"
      :answer.sync="answer"
      :slideIndex.sync="slideIndex"
      :all="info.length"
    />

    <!-- 引导页 -->
    <guide-page/>

    <!-- 底部 -->
    <div class="paper-footer">
      <div>
        <span @click="cardShow=true">
          <i class="iconfont icon-Questioncard"></i>
          题卡
        </span>
      </div>
      <div>
        <span @click="submitPaper()">
        <i class="iconfont icon-submit"></i>
        交卷
        </span>
      </div>
    </div>


    <!-- 答题卡 -->
    <van-popup
      v-model="cardShow"
      position="bottom"
    >
      <div class="card" v-if="info.length>0">
        <div class="card-title">
          <div>
            <span class="card-finish">已完成</span>
            <span class="card-nofinish">未完成</span>
          </div>
          <i class="iconfont icon-no" @click="cardShow=false"> </i>
        </div>
        <div class="card-list">
          <div class="card-item" v-for="(cards,name) in items" :key="name">
            <div class="card-item-title">{{name | type}}</div>
            <div class="card-item-list" v-if="name!='material'">
              <div
                :class="['list-cicle',formatStatus(craditem.type,craditem.id)==1 ? 'cicle-active' :'']"
                v-for="(craditem) in items[name]"
                :key="craditem.id" @click="slideToNumber(craditem.seq)">
                {{craditem.seq}}
              </div>
            </div>
            <div class="card-item-list" v-if="name=='material'">
              <template v-for="(craditem) in items[name]">
                <div
                  :class="['list-cicle',formatStatus(materialitem.type,materialitem.id)==1 ? 'cicle-active' :'']"
                  v-for="(materialitem) in craditem.subs"
                  :key="materialitem.id" @click="slideToNumber(materialitem.seq)">
                  {{materialitem.seq}}
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>
    </van-popup>

    <!-- 考试时间 -->
    <div :class="['time',timeWarn ? 'warn' : '']">
      {{time}}
    </div>
  </div>
</template>

<script>
  import Api from '@/api';
  import { mapState, mapMutations, mapActions } from 'vuex';
  import * as types from '@/store/mutation-types';
  import { Toast, Overlay, Popup, Dialog, Lazyload } from 'vant';

  import guidePage from '../component/guide-page';
  import itemBank from '../component/itemBank';

  import { getCountDown } from '@/utils/date-toolkit.js';


  import examMixin from '@/mixins/lessonTask/exam.js';

  let backUrl = '';

  export default {
    name: 'testpaperDo',
    mixins: [examMixin],
    data() {
      return {
        cardSeq: 0,//点击题卡要滑动的指定位置的索引
        testpaper: {},
        testpaperResult: {},
        info: [], //试卷信息
        answer: {}, //答案
        cardShow: false,//答题卡显示标记
        items: {},//分组题目
        time: null,//倒计时
        timeMeter: null,//计时器
        timeWarn: false,//倒计时警告标记
        isHandExam: false,//是否已交卷
        localtime: null,//本地时间计时器
        localtimeName: null,//本地存储的时间key值
        localanswerName: null,//本地存储的答案key值
        localuseTime: null,
        lastAnswer: null,//本地存储的答案
        lastTime: null,//本地存储的时间
        startTime: null,
        backUrl: '',
        slideIndex: 0,//题库组件当前所在的划片位置
      };
    },
    created() {
      this.getData();
    },
    components: {
      itemBank,
      guidePage,
      vanOverlay: Overlay
    },
    filters: {
      type: function (type) {
        switch (type) {
          case 'single_choice':
            return '单选题';
            break;
          case 'choice':
            return '多选题';
            break;
          case 'essay':
            return '问答题';
            break;
          case 'uncertain_choice':
            return '不定项选择题';
            break;
          case 'determine':
            return '判断题';
            break;
          case 'fill':
            return '填空题';
            break;
          case 'material':
            return '材料题';
            break;
        }
      }
    },
    beforeRouteEnter(to, from, next) {
      if (from.fullPath === '/') {
        backUrl = '/';
      } else {
        backUrl = '';
      }
      next();
    },
    beforeRouteLeave(to, from, next) { //可捕捉离开提醒
      if (this.info.length == 0 || this.isHandExam || this.testpaperResult.status != 'doing') {
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
    beforeDestroy() { //清除定时器
      this.clearTime();
      Dialog.close();
    },
    computed: {
      ...mapState({
        isLoading: state => state.isLoading,
        user: state => state.user
      }),
    },
    watch: {
      answer: {
        handler: 'saveAnswer',
        deep: true
      }
    },
    methods: {
      ...mapActions('course', [
        'handExamdo',
      ]),
      //请求接口获取数据
      getData() {
        let testId = this.$route.query.testId;
        let targetId = this.$route.query.targetId;
        let action = this.$route.query.action;
        Api.getExamInfo({
          query: {
            testId
          },
          data: {
            action,
            targetId: targetId,
            targetType: 'task'
          }
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
            }
          });
      },
      //获取到数据后进行操作
      afterGetData(res) {
        //设置导航栏题目
        this.$store.commit(types.SET_NAVBAR_TITLE, this.$route.query.title);
        //赋值数据
        this.items = res.items;
        this.testpaper = res.testpaper;
        res.testpaperResult.limitedTime = Number(res.testpaperResult.limitedTime);
        this.testpaperResult = res.testpaperResult;

        //判断是否做题状态
        if (this.isDoing()) {
          return;
        }

        this.localanswerName = `${this.user.id}-${this.testpaperResult.id}`;
        this.localtimeName = `${this.user.id}-${this.testpaperResult.id}-time`;
        this.lastTime = localStorage.getItem(this.localtimeName);
        this.lastAnswer = JSON.parse(localStorage.getItem(this.localanswerName));

        //处理数据格式
        this.formatData(res);

        this.interruption();

        //本地实时存储时间
        this.saveTime();

        //如果有限制考试时长，开始计时
        this.timer();

      },
      //判断是否做题状态
      isDoing() {
        if (this.testpaperResult.status != 'doing') {
          this.showResult();
          return true;
        } else {
          return false;
        }
      },
      //异常中断
      interruption() {
        if (!this.$route.params.KeepDoing) {
          //异常中断或者刷新页面
          this.canDoing(this.testpaperResult, this.user.id)
            .then(() => {

            })
            .catch(({ answer, endTime }) => {
              this.submitExam(answer, endTime);
              return;
            });
        }
      },
      //遍历数据类型去做对应处理
      formatData(res) {
        let paper = res.items;
        let info = [];
        let answer = [];
        Object.keys(paper)
          .forEach(key => {
            if (key != 'material') {
              paper[key].forEach(item => {
                this.sixType(item.type, item);
              });
            }
            if (key == 'material') {//材料题下面有子题需要特殊处理
              paper[key].forEach(item => {
                let title = Object.assign({}, item, { subs: '' });
                item.subs.forEach((sub, index) => {
                  sub.parentTitle = title;//材料题题干
                  sub.parentType = item.type;//材料题题型
                  sub.materialIndex = index + 1;//材料题子题的索引值，在页面要显示
                  this.sixType(sub.type, sub);
                });
              });
            }
          });
      },
      //处理六大题型数据
      sixType(type, item) {
        if (type == 'single_choice') {
          let length = item.metas.choices.length;
          //刷新页面或意外中断回来数据会丢失，因此要判断本地是否有缓存数据，如果有要把数据塞回
          if (this.lastAnswer) {
            this.$set(this.answer, item.id, this.lastAnswer[item.id]);
          } else {
            this.$set(this.answer, item.id, []);
          }
          this.info.push(item);
        }
        if (type == 'choice' || type == 'uncertain_choice') {
          if (this.lastAnswer) {
            this.$set(this.answer, item.id, this.lastAnswer[item.id]);
          } else {
            this.$set(this.answer, item.id, []);
          }
          this.info.push(item);
        }
        if (type == 'essay') {
          if (this.lastAnswer) {
            this.$set(this.answer, item.id, this.lastAnswer[item.id]);
          } else {
            this.$set(this.answer, item.id, ['']);
          }
          this.info.push(item);
        }

        if (type == 'fill') {
          let fillstem = item.stem;
          let { stem, index } = this.fillReplce(fillstem, 0);
          item.stem = stem;
          item.fillnum = index;
          if (this.lastAnswer) {
            this.$set(this.answer, item.id, this.lastAnswer[item.id]);
          } else {
            this.$set(this.answer, item.id, new Array(index).fill(''));
          }
          this.info.push(item);
        }

        if (type == 'determine') {
          if (this.lastAnswer) {
            this.$set(this.answer, item.id, this.lastAnswer[item.id]);
          } else {
            this.$set(this.answer, item.id, []);
          }
          this.info.push(item);
        }
      },
      //处理富文本，并统计填空题的空格个数
      fillReplce(stem, index) {
        const reg = /\[\[.+?\]\]/;
        while (reg.exec(stem)) {
          stem = stem.replace(reg, () => {
            return `<span class="fill-bank">（${++index}）</span>`;
          });
        }
        return { stem, index };
      },
      //答题卡状态判断,finish 0是未完成  1是已完成
      formatStatus(type, id) {
        let finish = 0;
        //单选题、多选题、不定项选择题、判断题
        if ((type == 'single_choice' ||
          type == 'choice' ||
          type == 'uncertain_choice' ||
          type == 'determine') &&
          this.answer[id].length > 0
        ) {
          finish = 1;
          return finish;
        }
        //问答题
        if (type == 'essay' && this.answer[id][0] != '') {
          finish = 1;
          return finish;
        }
        //填空题，规则：只要填了一个就算完成
        if (type == 'fill') {
          finish = Number(this.answer[id].some(item => {
            return item != '';
          }));
          return finish;
        }
        return finish;
      },
      //答题卡定位
      slideToNumber(num) {
        let index = Number(num);
        this.cardSeq = index;
        //关闭弹出层
        this.cardShow = false;
      },
      //考试倒计时
      timer(timeStr) {
        let i = 0;
        let time = this.testpaperResult.limitedTime * 60 * 1000;
        if (time <= 0) {
          return;
        }
        //如果考试过程中中断，剩余时间=考试限制时间-中断时间
        if (this.lastTime) {
          let gotime = Math.ceil((new Date().getTime()) - (this.testpaperResult.beginTime * 1000));
          time = time - gotime;
        }

        this.timeMeter = setInterval(() => {
          let { hours, minutes, seconds } = getCountDown(time, i++);
          this.time = `${hours}:${minutes}:${seconds}`;
          if (hours == 0 && minutes == 0 && seconds < 60) {
            this.timeWarn = true;
          }
          if ((Number(hours) == 0 && Number(minutes) == 0 &&
              Number(seconds) == 0) || Number(seconds) < 0) {
            this.clearTime();
            //直接交卷
            this.submitExam();
          }
        }, 1000);
      },
      //清空定时器
      clearTime() {
        clearInterval(this.timeMeter);
        this.timeMeter = null;

        clearInterval(this.localtime);
        this.localtime = null;
      },
      //提交试卷
      submitPaper() {
        let index = 0;
        let message = '题目已经做完，确认交卷吗?';
        let answer = JSON.parse(JSON.stringify(this.answer));
        Object.keys(answer)
          .forEach(key => {
            //去除空数据
            answer[key] = answer[key].filter(t => t !== '');
            //统计未做个数
            if (answer[key].length === 0) {
              index++;
            }
          });

        if (index > 0) {
          message = `还有${index}题未做，确认交卷吗？`;
        }
        return new Promise((resolve, reject) => {
          Dialog.confirm({
            title: '交卷',
            cancelButtonText: '确认交卷',
            confirmButtonText: '检查一下',
            message: message
          })
            .then(() => {
              //显示答题卡
              this.cardShow = true;
              reject();
            })
            .catch(() => {
              this.clearTime();
              this.submitExam(answer)
                .then(res => {
                  resolve();
                })
                .catch((err) => {
                  reject();
                });
            });
        });
      },
      //dispatch给store，提交答卷
      submitExam(answer, endTime) {
        //如果已经遍历过answer就不要再次遍历，直接用
        if (!answer) {
          answer = JSON.parse(JSON.stringify(this.answer));
          Object.keys(answer)
            .forEach(key => {
              answer[key] = answer[key].filter(t => t !== '');
            });
        }
        //考试结束时间，没有传结束时间则为当前时间
        endTime = endTime ? endTime : new Date().getTime();

        let datas = {
          answer,
          resultId: this.testpaperResult.id,
          userId: this.user.id,
          endTime,
          beginTime: Number(this.testpaperResult.beginTime)
        };

        return new Promise((resolve, reject) => {
          this.handExamdo(datas)
            .then(res => {
              this.isHandExam = true;
              resolve();
              //跳转到结果页
              //  this.showResult();
            })
            .catch((err) => {
              reject();
              Toast.fail(err.message);
            });

        });
      },
      //实时存储答案
      saveAnswer(val) {
        localStorage.setItem(this.localanswerName, JSON.stringify(val));
      },
      //实时存储时间
      saveTime() {
        this.localuseTime = `${this.user.id}-${this.testpaperResult.id}-usedTime`;
        let time = localStorage.getItem(this.localuseTime) || 0;
        this.localtime = setInterval(() => {
          if (!this.testpaperResult.limitedTime) {
            localStorage.setItem(this.localuseTime, ++time);
          }
          localStorage.setItem(this.localtimeName, new Date().getTime());
        }, 1000);
      },
      //跳转到结果页
      showResult() {
        this.$router.replace({
          name: 'testpaperResult',
          query: {
            resultId: this.testpaperResult.id,
            testId: this.$route.query.testId,
            targetId: this.$route.query.targetId,
            backUrl: backUrl
          }
        });
      },
      //跳转到说明页
      toIntro() {
        this.$router.push({
          name: 'testpaperIntro',
          query: {
            testId: this.$route.query.testId,
            targetId: this.$route.query.targetId
          }
        });
      }
    }
  };
</script>
