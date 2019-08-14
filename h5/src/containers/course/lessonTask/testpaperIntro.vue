<template>
  <div>
    <div class="intro-body">
      <van-panel class="panel intro-panel" title="考试名称">
        <div class="intro-panel__content intro-panel__content--title">{{ testpaperTitle }}</div>
      </van-panel>
      <van-panel class="panel intro-panel" v-if=startTime title="开考时间">
        <div class="intro-panel__content intro-tip">{{ startTime|filterDate }}</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="考试时长">
        <div class="intro-panel__content intro-tip" v-if=limitTime>{{ limitTime }}分钟</div>
        <div class="intro-panel__content" v-else>不限制</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="试卷满分">
        <div class="intro-panel__content">满分{{ score }}分</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="题目数量">
        <div class="intro-panel__content">
          <van-cell class="intro-cell intro-cell--total" :border=false title="共计" :value="sum|filterStr" />
          <van-cell class="intro-cell" :border=false v-for="(item, index) in question_type_seq" :key="item" :title="obj[item]" :value="counts[item]|filterStr" />
        </div>
      </van-panel>
    </div>
    <div class="intro-footer">
      <van-button class="intro-footer__btn" :disabled="disabled" type="primary"><span v-if="result">查看成绩</span><span v-else>开始考试</span></van-button>
    </div>
  </div>
</template>

<script>
import Api from '@/api';
export default {
  data() {
    return {
      testpaperTitle: '',
      info: {},
      doTime: '',
      startTime: '',
      limitTime: '',
      score: '',
      total: 0,
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
      counts: {},
      result: {}
    }
  },
  computed: {
    sum: function () {
      let sum = 0;
      for (let i in this.counts) {
        sum = sum + parseInt(this.counts[i]);
      }
      return sum;
    },
    disabled: function() {
      const nowTime = new Date().getTime();
      return this.startTime > nowTime ? true: false;
    }
  },
  created() {
    this.getInfo(17, 668);
  },
  filters: {
    filterStr(index) {
      const str = `${index}题`;
      return str;
    },
    filterDate(timestamp) {
      const dateObj = new Date(+timestamp)
      const year = dateObj.getFullYear();
      const month = dateObj.getMonth() + 1;
      const date = dateObj.getDate();
      const hours = dateObj.getHours();
      const minutes = dateObj.getMinutes();
      const dateNumFun = (num) => +num < 10 ? `0${num}` : num;
      const [Y, M, D, h, m, s] = [ year, dateNumFun(month), dateNumFun(date), dateNumFun(hours), dateNumFun(minutes)];
      return `${Y}年${M}月${D}日${h}:${m}`;
     }
  },
  methods: {
    getInfo(testId, taskId) {
      Api.testpaperIntro({
        params: {
          targetId: taskId,
          targetType: 'task'
        },
        query: {
          testId: testId
        }
      }).then(res => {
        this.counts = res.items;
        this.testpaperTitle = res.testpaper.name;
        this.score = res.testpaper.score;
        this.info = res.task.activity.testpaperInfo;
        this.startTime = parseInt(this.info.startTime) * 1000;
        this.doTime = this.info.doTimes;
        this.limitTime = parseInt(this.info.limitTime);
        this.result = res.testpaperResult;
        this.question_type_seq = res.testpaper.metas.question_type_seq;
      });
    }
  }
}
</script>

