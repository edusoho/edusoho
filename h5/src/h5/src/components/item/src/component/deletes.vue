<template>
  <div class="ibs-subject">
    <div class="ibs-subject-stem" v-if="items.isDelete === 1">
      <div class="ibs-serial-number">{{ question.seq }}、</div>
      <div class="ibs-rich-text">
        此题已经被删除
      </div>
    </div>
    <template v-else>
      <div class="ibs-subject-stem">
        <div class="ibs-serial-number">{{ question.seq }}、</div>
        <div class="ibs-rich-text" v-html="items.material" />
      </div>

      <div v-if="items.type === 'material'" class="ibs-material-title">
        <span class="ibs-serial-number">问题{{ questionKey }}：</span>
        <div class="ibs-rich-text">
          此题已经被删除
        </div>
      </div>
    </template>
  </div>
</template>
<script>
export default {
  name: "",
  props: {
    items: {
      type: Object,
      default: () => {}
    },
    question: {
      type: Object,
      default: () => {}
    },
    questionIndex: {
      type: Number,
      default: 1
    },
    keys: {
      type: Array,
      default() {
        return [];
      }
    }
  },
  computed: {
    questionKey() {
      return this.keys[2] + 1;
    }
  }
};
</script>
