<template>
  <div class="fill" v-if="filldata">
    <div>
      <div class="serial-number">1、</div>
      <div v-html="filldata.stem"></div>
    </div>
    <div v-for="(list,index) in filldata.answers" :key="index">
      <div class="fill-subject">填空题（{{index+1}}）</div>
      <van-field
        v-model="filldata.answers[index]"
        class="fill-input"
        label-width="0px"
        type="textarea"
        placeholder="请填写答案"
        rows="1"
        autosize
      />
    </div>
  </div>
</template>

<script>
export default {
  name: "fill-type",
  props: {
    filldata: {
      type: Object,
      default: () => {}
    }
  },
  data() {
    return {
      // filldata:{
      //   id: "6",
      //   type: "fill",
      //   stem:
      //     "<p>但是我奋斗的是[[我其实对方认为|v 奋斗色个人]]dwqfdwqer[[dewqad2w|dfwqe ]]</p>\r\n",
      //   score: "2.0",
      //   metas: [],
      //   categoryId: "0",
      //   difficulty: "normal",
      //   target: "course-1",
      //   courseId: "0",
      //   lessonId: "0",
      //   parentId: "0",
      //   subCount: "0",
      //   finishedTimes: "0",
      //   passedTimes: "0",
      //   createdUserId: "2",
      //   updatedUserId: "2",
      //   courseSetId: "1",
      //   seq: "6",
      //   missScore: "0.0",
      //   answers:[]
      // },
      index: 0
    };
  },
  created() {},
  mounted() {
    //this.replaceString();
  },
  methods: {
    replaceString() {
      const reg = /\[\[.+?\]\]/;
      if (!reg.test(this.filldata.stem)) {
        return false;
      } else {
        this.filldata.answers.push("");
        this.$set(this.filldata, "answers", this.filldata.answers);
        this.filldata.stem = this.filldata.stem.replace(reg, () => {
          return `<span class="fill-bank">（${++this.index}）</span>`;
        });
        this.replaceString(this.filldata.stem);
      }
    }
  }
};
</script>