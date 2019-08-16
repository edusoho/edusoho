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

      <div class="result-footer" v-show="!doTimes && isReadOver">
        <van-button class="result-footer__btn" type="primary" v-if="again">再考一次</van-button>
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
      isReadOver: false,
      resultId: '',
      title: '',
      testpaperInfo: {},
      doTimes: 0,
      again: 1,
      result: {},
      calHeight: '',
      subjectList: {},
      question_type_seq: [],
      obj: {
        "single_choice": '单选题',
        "choice": '多选题',
        "essay": '问答题',
        "uncertain_choice": '不定项选择题',
        "determine": '判断题',
        "fill": '填空题',
        "material": '材料题'
      },
      color: {
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
    remainTime: function() {
      const interval = this.testpaperInfo.redoInterval;
      const intervalTimestamp = parseInt(interval) * 60 * 1000;
      const nowTimestamp = new Date().getTime();
      const checkedTime = parseInt(this.result.checkedTime) * 1000;
      const sumTime = checkedTime + intervalTimestamp;
      const subTime =  Math.abs(sumTimenow - nowTimestamp);
      this.again = nowTimestamp >= sumTime ? true: false;
      remainTime = this.dealTimestamp(subTime);
      return remainTime;
    },
    usedTime: function() {
      const time = parseInt(this.result.usedTime);
      return Math.round(time/60);
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
        this.getSubjectList(res.items);
        this.calSubjectHeight();
      });
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
      const dataHeight = this.$refs.data.offsetHeight + this.$refs.tag.offsetHeight + 46;
      const allHeight = document.documentElement.clientHeight;
      const finalHeight = allHeight - dataHeight;
      this.calHeight = `${finalHeight}px`;
    },
    getArray(data, arr) {
      let obj = {};
      obj.seq = data.seq;
      obj.status = data.testResult.status;
      arr.push(obj);
    },
    dealTimestamp(timestamp) {
      let timeTip = '';
      const dayStamp = 1000 * 60 * 60 * 24;
      const hourStamp = 1000 * 60 * 60;
      if (timestamp <= hourStamp) {
        timeTip = Math.round((timestamp / (1000 * 60))) + '分';
      }
      else if (hourStamp * 1 < timestamp && timestamp <= dayStamp) {
        const hours = Math.floor(timestamp / (hourStamp));
        const minutes = Math.floor(timestamp % (hourStamp));
        timeTip = `${hours}小时${minutes}分`;
      }
      else if (timestamp > dayStamp) {
        const days = Math.floor(timestamp / (dayStamp));
        const remain = timestamp % (dayStamp);
        const hours = Math.floor(remain / (hourStamp));
        const minutes = Math.floor(remain % (hourStamp));
        timeTip = `${day}天${hours}小时${minutes}分`;
      }
      return timeTip;
    }
  },
  created() {
    this.resultId = this.$route.params.resultId;
    this.testpaperInfo = this.$route.params.testpaperInfo;
    this.title = this.$route.params.title;
    this.setNavbarTitle(this.title);
    this.doTimes = parseInt(this.testpaperInfo.doTimes);
    this.getTestpaperResult(this.resultId);
  },

  beforeUpdate (to, from, next) {
    document.body.className = 'bg-color';
  },
  beforeDestroy: () => {
    document.body.removeAttribute('class', 'bg-color');
  }
}
</script>
