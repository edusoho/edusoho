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

    <van-radio-group v-model="radio" class="answer-paper" @change="choose()">
      <van-radio
        :name="1"
        :disabled="!canDo"
        class="subject-option subject-option--determine">
        <div class="subject-option__content">对</div>
        <i
          slot="icon"
          slot-scope="props"
          :class="['iconfont','icon-yes','subject-option__order', !canDo ? checkAnswer(1,itemdata) :'' ]"
        />
      </van-radio>
      <van-radio
        :name="0"
        :disabled="!canDo"
        class="subject-option subject-option--determine">
        <div class="subject-option__content">错</div>
        <i
          slot="icon"
          slot-scope="props"
          :class="['iconfont','icon-no','subject-option__order', !canDo ? checkAnswer(0,itemdata) :'' ]"
        />
      </van-radio>
    </van-radio-group>
  </div>
</template>

<script>
import checkAnswer from '../../../../mixins/lessonTask/itemBank'
export default {
  name: 'DetermineType',
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
      this.$emit('determineChoose', this.radio, this.itemdata.id)
    }
  }
}
</script>
