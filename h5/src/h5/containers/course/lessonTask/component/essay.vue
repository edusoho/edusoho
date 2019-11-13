<template>
  <div class="essay">
    <div class="subject-stem" >
      <div class="serial-number">{{itemdata.seq}}、</div>
      <div v-html="stem" class="rich-text"></div>
    </div>

    <div class="material-title" v-if="itemdata.parentTitle">
      <span class="serial-number">问题{{itemdata.materialIndex}}：</span>
      <div v-html="itemdata.stem" class="rich-text"></div>
    </div>

    <div class="answer-paper">
      <van-field
        v-model="answer[0]"
        @input="change()"
        class="essay-input"
        label-width="0px"
        type="textarea"
        :placeholder="placeholder"
        :autosize="{ maxHeight: 200,minHeight: 200 }"
        :disabled="!canDo"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: "essay-type",
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
          return '请填写你的答案......'
        }else{
          return '未作答'
        }
      }
    }
  },
  methods:{
    change(){
      //console.log(this.answer[0])
    }
  }
};
</script>

<style>
</style>