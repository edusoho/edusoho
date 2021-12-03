import Layout from './Layout.vue';

export default {
  props: {
    moduleType: {
      type: String,
      required: true
    },

    currentModuleType: {
      type: String,
      required: true
    },

    moduleData: {
      type:  [Array, Object],
      required: true
    },

    isFirst: {
      type: Boolean,
      required: true
    },

    isLast: {
      type: Boolean,
      required: true
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