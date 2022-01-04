import Layout from './Layout.vue';

export default {
  props: {
    moduleType: {
      type: String,
      required: true
    },

    currentModuleType: {
      type: String,
      default: ''
    },

    moduleData: {
      type:  [Array, Object],
      required: true
    },

    isFirst: {
      type: Boolean,
      default: false
    },

    isLast: {
      type: Boolean,
      default: false
    },

    validatorResult: {
      type: Boolean,
      default: true
    },

    preview: {
      type: Boolean,
      default: true
    }
  },

  components: {
    Layout
  },

  methods: {
    handleClickAction(type) {
      this.$emit('event-actions', type);
    }
  }
};