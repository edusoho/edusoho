<template>
  <div class="essay">
    <div class="subject-stem" >
      <div class="serial-number">{{ itemdata.seq }}、</div>
      <div class="rich-text" v-html="stem"/>
    </div>

    <div v-if="itemdata.parentTitle" class="material-title">
      <span class="serial-number">问题{{ itemdata.materialIndex }}：</span>
      <div class="rich-text" v-html="itemdata.stem"/>
    </div>

    <div class="answer-paper">
      <van-field
        v-model="answer[0]"
        :placeholder="placeholder"
        :autosize="{ maxHeight: 200,minHeight: 200 }"
        :disabled="!canDo"
        class="essay-input"
        label-width="0px"
        type="textarea"
        @input="change()"
      />
    </div>
  </div>
</template>

<script>
export default {
  name: 'EssayType',
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
  computed: {
    stem: {
      get() {
        if (this.itemdata.parentTitle) {
          return this.itemdata.parentTitle.stem
        } else {
          return this.itemdata.stem
        }
      }
    },
    placeholder: {
      get() {
        if (this.canDo) {
          return '请填写你的答案......'
        } else {
          return '未作答'
        }
      }
    }
  },
  methods: {
    change() {
      // console.log(this.answer[0])
    }
  }
}
</script>

<style>
</style>
