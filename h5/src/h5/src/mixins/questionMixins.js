export default {
  props: {
    itemData: {
      type: Object,
      default() {
        return {};
      }
    },
    commonData: {
      type: Object,
      default() {
        return {}
      }
    },
    attachements: {
      type: Array,
      default: () => []
    },
    currentItem: {
      type: Object,
      default() {
        return {}
      }
    },
    reviewedCount: {
      type: Number,
      default: 0
    },
    totalCount: {
      type: Number,
      default: 0
    },
    showShadow: {
      type: String,
      default: ''
    },
    exerciseInfo: {
      type: Array,
      default: () => []
    },
    disabledData: {
      type: Boolean,
      default: false
    },
    questionStatus: {
      type: String,
      default: ''
    },
    wrong: {
      type: Boolean,
      default: false
    },
    mode: {
      type: String,
      default: 'do'
    },
    isAnswerFinished: {
      type: Number,
      default: 0
    },
    reviewedQuestion: {
      type: Array,
      default: () => []
    },
    fillStatus: {
      type: Array,
      default: () => []
    },
    EssayRadio: {
      typeof: Array,
      default: () => []
    }
  },
  computed: {
    disabled() {
      return this.itemData.mode !== "do";
    }
  }
};
