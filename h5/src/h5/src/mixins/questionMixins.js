export default {
  props: {
    itemData: {
      type: Object,
      default() {
        return {};
      }
    }
  },
  computed: {
    disabled() {
      return this.itemData.mode !== "do";
    }
  }
};
