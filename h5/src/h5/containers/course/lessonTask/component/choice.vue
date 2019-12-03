<template>
  <div class="subject">
    <div class="subject-stem">
      <span class="serial-number">{{ itemdata.seq }}、</span>
      <div class="subject-stem__content rich-text" v-html="stem"/>
    </div>

    <div v-if="itemdata.parentTitle" class="material-title">
      <span class="serial-number">问题{{ itemdata.materialIndex }}：</span>
      <div class="rich-text" v-html="itemdata.stem"/>
    </div>

    <van-checkbox-group v-model="result" class="answer-paper" @change="choose()">
      <van-checkbox
        v-for="(item, index) in itemdata.metas.choices"
        :key="index"
        :name="index"
        :disabled="!canDo"
        class="subject-option"
      >
        <div class="subject-option__content" v-html="item"/>
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
import checkAnswer from '../../../../mixins/lessonTask/itemBank'
export default {
  name: 'ChoiceType',
  filters: {
    filterOrder(index) {
      const arr = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J']
      return arr[index]
    }
  },
  mixins: [checkAnswer],
  props: {
    itemdata: {
      type: Object,
      default: () => {}
    },
    number: {
      type: Number,
      default: 1
    },
    answer: {
      type: Array,
      default: () => []
    },
    canDo: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      result: this.answer
    }
  },
  computed: {
    stem: {
      get() {
        if (this.itemdata.parentTitle) {
          return this.itemdata.parentTitle.stem
        } else {
          return this.itemdata.stem
        }
      }
    }
  },
  methods: {
    choose(name) {
      this.$emit('choiceChoose', this.result, this.itemdata.id)
    }
  }
}
</script>
