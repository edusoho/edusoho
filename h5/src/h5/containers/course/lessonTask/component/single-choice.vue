<template>
  <div class="subject">
    <div class="subject-stem">
      <span class="serial-number">{{ itemdata.seq }}、</span>
      <div class="rich-text" v-html="stem"/>
    </div>

    <div v-if="itemdata.parentTitle" class="material-title">
      <span class="serial-number">问题{{ itemdata.materialIndex }}：</span>
      <div class="rich-text" v-html="itemdata.stem"/>
    </div>

    <van-radio-group v-model="radio" class="answer-paper" @change="choose()">
      <van-radio
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
          :class="['subject-option__order',!canDo ? checkAnswer(index,itemdata) :'']"
        >{{ index|filterOrder }}</span>
      </van-radio>
    </van-radio-group>
  </div>
</template>

<script>
import checkAnswer from '../../../../mixins/lessonTask/itemBank'
export default {
  name: 'SingleChoice',
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
    answer: {
      type: Array,
      default: () => []
    },
    number: {
      type: Number,
      default: 1
    },
    canDo: {
      type: Boolean,
      default: true
    }
  },
  data() {
    return {
      radio: this.answer[0]
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
    // 向父级提交数据
    choose() {
      this.$emit('singleChoose', this.radio, this.itemdata.id)
    }
  }
}
</script>
