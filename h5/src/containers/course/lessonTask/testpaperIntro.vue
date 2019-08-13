<template>
  <div>
    <div class="intro-body">
      <van-panel class="panel intro-panel" title="考试名称">
        <div class="intro-panel__content intro-panel__content--title">这里是考试名称，可以比较长，再长就换行。怎么还有文字，那就换行吧，只要就这样换行好了也不需要特殊样式，就这样吧。但是需要限制一下字数个数。</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="开考时间">
        <div class="intro-panel__content intro-tip">2019年01月11日 04:13</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="考试时长">
        <div class="intro-panel__content intro-tip">180分钟</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="试卷满分">
        <div class="intro-panel__content">满分100分</div>
      </van-panel>
      <van-panel class="panel intro-panel" title="题目数量">
        <div class="intro-panel__content">
          <van-cell class="intro-cell intro-cell--total" :border=false title="共计" value="45题" />
          <van-cell class="intro-cell" :border=false v-for="(item, index) in question_type_seq" :key="item" :title="obj[item]" :value="counts[item]|filterStr" />
        </div>
      </van-panel>
    </div>
    <div class="intro-footer">
      <van-button class="intro-footer__btn" disabled type="primary">开始考试</van-button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      question_type_seq: [
        "single_choice",
        "choice",
        "essay",
        "uncertain_choice",
        "determine",
        "fill",
        "material"
      ],
      obj: {
        "single_choice": '单选题',
        "choice": '多选题',
        "essay": '问答题',
        "uncertain_choice": '不定项选择题',
        "determine": '判断题',
        "fill": '填空题',
        "material": '材料题'        
      },
      counts: {
        "single_choice": 1,
        "choice": 1,
        "essay": 1,
        "uncertain_choice": 1,
        "determine": 1,
        "fill": 1,
        "material": 1
      },
    }
  },
  filters: {
    filterStr(index) {
      const str = `${index}题`;
      return str;
    }
  },
}
</script>