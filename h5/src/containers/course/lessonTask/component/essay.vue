<template>
  <div class="essay">
    <div class="subject-stem" >
      <div class="serial-number">{{number}}、</div>
      <div v-html="stem"></div>
    </div>

    <div class="material-title" v-if="itemdata.parentTitle">
      <span class="serial-number">问题{{itemdata.materialIndex}}：</span>
      <div v-html="itemdata.stem"></div>
    </div>

    <div class="answer-paper">
      <van-field
        v-model="answer[0]"
        @input="change()"
        class="essay-input"
        label-width="0px"
        type="textarea"
        placeholder="请填写你的答案......"
        :autosize="{ maxHeight: 200,minHeight: 200 }"
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
  methods:{
    change(){
      //console.log(this.answer[0])
    }
  }
};
</script>

<style>
</style>