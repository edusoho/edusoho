<template>
  <div>
    <e-loading v-if="isLoading"></e-loading>
    <div class="result-data" ref="data">
      <div class="result-data__item">
        本次得分
        <div class="result-data__bottom data-number-orange data-medium" v-if="isReadOver"><span class=" data-number">{{ result.score }}</span>分</div>
        <div class="result-data__bottom data-text-blue" v-else>待批阅</div>
      </div>
      <div class="result-data__item">
        正确率
        <div class="result-data__bottom data-number-green data-medium" v-if="isReadOver"><span class=" data-number">{{ result.rightRate }}</span>%</div>
        <div class="result-data__bottom data-text-blue" v-else>待批阅</div>
      </div>
      <div class="result-data__item">
        做题用时
        <div class="result-data__bottom data-number-gray data-medium"><span class=" data-number">{{ usedTime }}</span>分钟</div>
      </div>
    </div>
    <div class="result-tag" ref="tag">
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-green"></div>正确
      </div>
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-orange"></div>错误
      </div>
      <div class="result-tag-item clearfix">
        <div class="result-tag-item__circle circle-gray"></div>未作答
      </div>
      <div class="result-tag-item clearfix" v-show="!isReadOver">
        <div class="result-tag-item__circle circle-brown"></div>待批阅
      </div>
    </div>

    <div class="result-subject" :style="{height: calHeight}">
      <van-panel class="result-panel" v-for="(keyItem, index) in question_type_seq" :key="index"  :title="obj[keyItem]">
        <ul class="result-list">
          <li :class="[ 'result-list__item testpaper-number', `circle-${color[item.status]}`]" v-for="(item, index) in subjectList[keyItem]" :key=index>{{ item.seq }}</li>
        </ul>
      </van-panel>

      <div class="result-footer" ref="footer" v-show="!doTimes && isReadOver">
        <van-button class="result-footer__btn" type="primary" v-if="again" @click="startTestpaper()">再考一次</van-button>
        <van-button class="result-footer__btn" type="primary" v-else disabled>在{{remainTime}}后可以再考一次</van-button>
      </div>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
import { mapState, mapMutations } from 'vuex';
import * as types from '@/store/mutation-types';
export default {
  name: "testpaperResult",
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
      doTimes: 0, // 考试允许次数
      redoInterval: null, // 重考间隔
      remainTime: null,   // 再次重考剩余时间
      obj: {              // 题型判断
        "single_choice": '单选题',
        "choice": '多选题',
        "essay": '问答题',
        "uncertain_choice": '不定项选择题',
        "determine": '判断题',
        "fill": '填空题',
        "material": '材料题'
      },
      color: {              // 题号标签状态判断
        'right': 'green',
        'none': 'brown',
        'wrong': 'orange',
        'partRight': 'orange',
        'noAnswer': 'gray',
      }
    }
  },
  computed: {
    ...mapState({
      isLoading: state => state.isLoading,
    }),
    usedTime: function() {
      const timeInterval = parseInt(this.result.usedTime) - parseInt(this.result.beginTime);
      const time = Math.abs(timeInterval);
      return Math.round(time/60/1000);
    }
  },
  methods: {
    ...mapMutations({
      setNavbarTitle: types.SET_NAVBAR_TITLE
    }),
    getTestpaperResult(resultId) {
      Api.testpaperResult({
        query: {
          resultId: resultId
        }
      }).then(res => {
        this.result = res.testpaperResult;
        this.question_type_seq = res.testpaper.metas.question_type_seq;
        this.isReadOver = this.result.status === 'finished' ? true : false;
        this.setNavbarTitle(res.testpaper.name);
        this.getSubjectList(res.items);
        this.calSubjectHeight();
        this.judgeTime();
      });
    },

    judgeTime() {
      const interval = this.redoInterval;
      const intervalTimestamp = parseInt(interval) * 60 * 1000;
      const nowTimestamp = new Date().getTime();
      const checkedTime = parseInt(this.result.checkedTime) * 1000;
      const sumTime = checkedTime + intervalTimestamp;
      this.again = nowTimestamp >= sumTime ? true: false;
      if (!this.again) {
        const subTime =  Math.abs(sumTime - nowTimestamp);
        this.remainTime = this.dealTimestamp(subTime);
      }
    },

    getSubjectList(resData) {
      const self = this;
      for (let i in resData) {
        let final = [];
        resData[i].map(function(one) {
          if (i === 'material') {
            one.subs.map(function(item) {
              self.getArray(item, final);
            })
          } else {
            self.getArray(one, final);
          }
        })
        self.subjectList[i] = final;
      }
    },

    calSubjectHeight() {
      this.$nextTick(()=>{
        const dataHeight = this.$refs.data.offsetHeight + this.$refs.tag.offsetHeight + 46;
        const allHeight = document.documentElement.clientHeight;
        const footerHeight = (!this.doTimes && this.isReadOver) ? this.$refs.footer.offsetHeight: 0;
        const finalHeight = allHeight - dataHeight - footerHeight;
        this.calHeight = `${finalHeight}px`;
      })
    },
    getArray(data, arr) {
      let obj = {};
      obj.seq = data.seq;
      obj.status = data.testResult.status;
      arr.push(obj);
    },
    dealTimestamp(timestamp) {
      let timeTip = '';
      const minuteStamp = 1000 * 60;
      const hourStamp = 1000 * 60 * 60;
      const dayStamp = 1000 * 60 * 60 * 24;
      if (timestamp <= hourStamp) {
        timeTip = Math.round(timestamp / minuteStamp) + '分';
      }
      else if (hourStamp * 1 < timestamp && timestamp <= dayStamp) {
        const hours = Math.floor(timestamp / hourStamp);
        const remainder = timestamp % hourStamp;
        const minutes = Math.floor(remainder / minuteStamp);
        timeTip = `${hours}小时${minutes}分`;
      }
      else if (timestamp > dayStamp) {
        const days = Math.floor(timestamp / dayStamp);
        const remain = timestamp % dayStamp;
        const hours = Math.floor(remain / hourStamp);
        const remainder = remain % hourStamp;
        const minutes = Math.floor(remainder / minuteStamp);
        timeTip = `${days}天${hours}小时${minutes}分`;
      }
      return timeTip;
    },
    startTestpaper(uselocal) {
      let uselocalData=uselocal || null
      this.$router.push({
        name: 'testpaperDo',
        query: {
          testId: this.result.testId,
          targetId: this.targetId,
          uselocalData
        }
      })
    },
    getRouteData() {
      this.resultId = this.$route.query.resultId;
      this.targetId = this.$route.query.targetId;
      this.doTimes = parseInt(this.$route.query.doTimes);
      this.redoInterval = this.$route.query.redoInterval;
    }
  },
  created() {
    this.getRouteData();
    this.getTestpaperResult(this.resultId);
  },

  beforeRouteEnter(to, from, next) {
    document.getElementById("app").style.background="#f6f6f6"
    next()
  },
  beforeRouteLeave(to, from, next)  {
    document.getElementById("app").style.background=""
    next()
  }
}
</script>
