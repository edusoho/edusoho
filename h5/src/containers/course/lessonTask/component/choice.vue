<template>
  <div class="subject">
    <div class="subject-stem">
      <span class="serial-number">{{ number }}、</span>
      <div class="subject-stem__content rich-text" v-html="stem"></div>
    </div>

    <div class="material-title" v-if="itemdata.parentTitle">
      <span class="serial-number">问题{{itemdata.materialIndex}}：</span>
      <div v-html="itemdata.stem" class="rich-text"></div>
    </div>

    <van-checkbox-group v-model="result" class="answer-paper" @change="choose()">
      <van-checkbox
        class="subject-option"
        v-for="(item, index) in itemdata.metas.choices"
        :key="index"
        :name="index"
        :disabled="!canDo"
      >
        <div class="subject-option__content" v-html="item"></div>
        <span
          slot="icon"
          slot-scope="props"
          :class="['subject-option__order','subject-option__order--square',!canDo ? checkAnswer(index,itemdata) :'']"
        >{{ index|filterOrder }}</span>
      </van-checkbox>
    </van-checkbox-group>
  </div>
</template>

<script>
import  checkAnswer from '../../../../mixins/lessonTask/itemBank'
export default {
  name: "choice-type",
  mixins:[checkAnswer],
  data() {
    return {
      result: this.answer
    };
  },
  props: {
    itemdata: {
      type: Object,
      default: () => {}
    },
    number: {
      type: Number,
      default: 1
    },
    answer:{
      type: Array,
      default: () => []
    },
    canDo:{
      type:Boolean,
      default:true
    }
  },
  computed: {
    stem: {
      get() {
        if (this.itemdata.parentTitle) {
          return this.itemdata.parentTitle.stem;
        } else {
          return this.itemdata.stem;
        }
      }
    }
  },
  filters: {
    filterOrder(index) {
      const arr = ["A", "B", "C", "D", "E", "F", "G", "H", "I", "J"];
      return arr[index];
    }
  },
  methods: {
    choose(name){
        this.$emit('choiceChoose',this.result,this.itemdata.id)
    }
  },
};
</script>