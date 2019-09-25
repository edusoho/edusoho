<template>
  <div class="fill" >
    <div class="subject-stem" >
      <div class="serial-number">{{itemdata.seq}}、</div>
      <div v-html="stem" class="rich-text"></div>
    </div>

    <div class="material-title" v-if="itemdata.parentTitle">
      <span class="serial-number">问题{{itemdata.materialIndex}}：</span>
      <div v-html="itemdata.stem" class="rich-text"></div>
    </div>

    <div class="answer-paper">
      <div v-for="(i,index) in itemdata.fillnum" :key="index" >
        <div class="fill-subject">填空题（{{index+1}}）</div>
        <van-field
          v-model="answer[index]"
          class="fill-input"
          label-width="0px"
          type="textarea"
          :placeholder="placeholder"
          rows="1"
          autosize
          :disabled="!canDo"
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
    },
    canDo:{
      type:Boolean,
      default:true
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
    },
    placeholder:{
       get(){
        if(this.canDo){
          return '请填写答案'
        }else{
          return '未作答'
        }
      }
    }
  },
  data() {
    return {
      index: 0
    };
  },
  methods: {
  }
};
</script>