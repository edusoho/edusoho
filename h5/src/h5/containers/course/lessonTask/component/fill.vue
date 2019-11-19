<template>
  <div class="fill" >
    <div class="subject-stem" >
      <div class="serial-number">{{ itemdata.seq }}、</div>
      <div class="rich-text" v-html="stem"/>
    </div>

    <div v-if="itemdata.parentTitle" class="material-title">
      <span class="serial-number">问题{{ itemdata.materialIndex }}：</span>
      <div class="rich-text" v-html="itemdata.stem"/>
    </div>

    <div class="answer-paper">
      <div v-for="(i,index) in itemdata.fillnum" :key="index" >
        <div class="fill-subject">填空题（{{ index+1 }}）</div>
        <van-field
          v-model="answer[index]"
          :placeholder="placeholder"
          :disabled="!canDo"
          class="fill-input"
          label-width="0px"
          type="textarea"
          rows="1"
          autosize
        />
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FillType',
  props: {
    filldata: {
      type: Object,
      default: () => {}
    }
  },
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
      index: 0
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
          return '请填写答案'
        } else {
          return '未作答'
        }
      }
    }
  },
  methods: {
  }
}
</script>
