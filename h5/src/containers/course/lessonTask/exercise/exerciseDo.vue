<template>
  
</template>

<script>
export default {
   name: "exercise-do",
   data() {
    return {
      info: [], //作业信息
      answer: {}, //答案
      lastAnswer: null,
      cardSeq: 0, //点击题卡要滑动的指定位置的索引
      homework: null,
      cardShow: false, //答题卡显示标记
      localanswerName:null,
      localtimeName:null,
      lastUsedTime:null,
      lastAnswer:null,
      usedTime:null,//使用时间，本地实时计时
      isHandHomework:false,//是否已经交完作业
      slideIndex:0,//题库组件当前所在的划片位置
    };
  },
}
</script>

<style>

</style>