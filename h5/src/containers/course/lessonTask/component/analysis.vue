<template>
  <div class="analysis">
    <div class="mt10 analysis-result">
      <div class="analysis-title">做题结果</div>
      <div class="analysis-content">
        <div class="analysis-content__item  mt10">
          <div class="analysis-item__title">做题结果</div>
          <div :class="[statusColor]">{{status(testResult)}}</div>
        </div>

        <div v-if="subject=='fill'">
          <div class="analysis-content__item  mt10" v-for="(item,index) in answer" :key="`right${index}`">
            <div class="analysis-item__title">正确答案</div>
            <div class="analysis-item_right">（{{index+1}}）{{filterOrder(item)}}</div>
          </div>
          <!-- 因为这里的testResult在部分情况下是没有的，所以这里的遍历使用正确答案来遍历 -->
          <div class="analysis-content__item  mt10" v-for="(item,index) in answer" :key="index">
            <div class="analysis-item__title">你的答案</div>
            <div class="analysis-item_noAnswer" v-if="!testResult">未回答</div>
            <div v-else :class="[statusColor]">（{{index+1}}）{{ filterOrder(testResult.answer[index])}}</div>
          </div>
        </div>

        <div v-if="subject!=='fill'">
          <div class="analysis-content__item  mt10">
            <div class="analysis-item__title">正确答案</div>
            <div class="analysis-item_right">{{filterOrder(answer,'standard')}}</div>
          </div>
          <div class="analysis-content__item  mt10">
            <div class="analysis-item__title">你的答案</div>
            <div class="analysis-item_noAnswer" v-if="!testResult">未回答</div>
            <div v-else :class="[statusColor]">{{ filterOrder(testResult.answer)}}</div>
          </div>
        </div>

      </div>
    </div>
    <div class="mt10 analysis-result">
      <div class="analysis-title">做题解析</div>
      <div class="analysis-content mt10" v-if="analysis">{{analysis}}</div>
      <div class="analysis-content mt10" v-else>无解析</div>
    </div>
  </div>
</template>

<script>
  export default {
    name: 'analysis',
    props: {
      testResult: {
        type: Object,
        default: () => {
        }
      },
      answer: {
        type: Array,
        default: () => []
      },
      analysis: {
        type: String,
        default: ''
      },
      subject: {
        type: String,
        default: ''
      },
      isExercise: {
        type: Boolean,
        default: false
      }
    },
    computed: {
      statusColor() {
        if (!this.testResult) {
          return 'analysis-item_noAnswer';
        }
        let status = this.testResult.status;
        switch (status) {
          case 'right':
            return 'analysis-item_right';
          case 'none':
            return this.isExercise ? 'analysis-item_subject' : 'analysis-item_none';
          case 'wrong':
          case 'partRight':
            return 'analysis-item_worng';
          case 'noAnswer':
            return 'analysis-item_noAnswer';
        }
      }
    },
    methods: {
      status: function (testResult) {
        if (!testResult) {
          return '未回答';
        }
        let status = testResult.status;
        switch (status) {
          case 'right':
            return '回答正确';
          case 'none':
            return this.isExercise ? '主观题' : '待批阅';
          case 'wrong':
          case 'partRight':
            return '回答错误';
          case 'noAnswer':
            return '未回答';
        }
      },
      filterOrder: function (answer = [], mode = 'do') {
        if (this.subject == 'fill' || this.subject == 'essay') {
          if (mode == 'standard') {
            return answer.length > 0 ? answer.toString() : '无';
          } else {
            return answer.length > 0 ? answer.toString() : '未回答';
          }
        } else {
          let arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
          if (this.subject == 'determine') {
            arr = ['错', '对'];
          }
          let formateAnswer = null;
          formateAnswer = answer.map((element) => {
            return arr[element];
          });
          if (mode == 'standard') {
            return formateAnswer.length > 0 ? formateAnswer.join(' ') : '无';
          }
          return formateAnswer.length > 0 ? formateAnswer.join(' ') : '未回答';
        }
      }
    }
  };
</script>

<style>
</style>
