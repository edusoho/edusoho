<template>
  <div class="subject">
    <div class="subject-stem">
      <span class="serial-number">{{ number }}、</span>
      <div  v-html="stem" class="rich-text"></div>
    </div>

    <div class="material-title" v-if="itemdata.parentTitle">
      <span class="serial-number">问题{{itemdata.materialIndex}}：</span>
      <div v-html="itemdata.stem" class="rich-text"></div>
    </div>

    <van-radio-group v-model="radio" class="answer-paper" @change="choose()">
      <van-radio class="subject-option"
        v-for="(item, index) in itemdata.metas.choices"
        :key="index"
        :name="index"
      >
        <div class="subject-option__content" v-html="item"></div>
        <span
          slot="icon"
          slot-scope="props"
          class="subject-option__order"
        >{{ index|filterOrder }}</span>
      </van-radio>  
    </van-radio-group>
  </div>
</template>

<script>
export default {
  name: 'single-choice',
  data() {
    return {
      radio:this.answer[0],
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
    },
  },
  filters: {
    filterOrder(index) {
      const arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
      return arr[index];
    },
  },
  methods: {
    //向父级提交数据
    choose(){
      this.$emit('singleChoose',this.radio,this.itemdata.id)
    }
  },
}
</script>