<template>
  <div class="fill" >
    <div class="subject-stem" >
      <div class="serial-number">{{number}}、</div>
      <div v-html="stem"></div>
    </div>
     <!-- <div class="material-title">问题1：教师职业最大的特点在区委区是多于职业角色（）。</div> -->

    <div class="material-title" v-if="itemdata.parentTitle">
      <span class="serial-number">问题{{itemdata.materialIndex}}：</span>
      <div v-html="itemdata.stem"></div>
    </div>

    <div class="answer-paper">
      <div v-for="(i,index) in itemdata.fillnum" :key="index" >
        <div class="fill-subject">填空题（{{index+1}}）</div>
        <van-field
          v-model="answer[index]"
          class="fill-input"
          label-width="0px"
          type="textarea"
          placeholder="请填写答案"
          rows="1"
          autosize
        />
      </div>
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
  props:{
    itemdata:{
      type: Object,
      default: () => {}
    },
    answer:{
      type: Array,
      default: () => []
    },
    number:{
      type: Number,
      default: 1
    }
  },
  computed:{
    stem:{
      get(){
        if(this.itemdata.parentTitle){
          return this.itemdata.parentTitle.stem
        }else{
          return this.itemdata.stem
        }
      }
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
  methods: {
  }
};
</script>