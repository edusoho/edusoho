<template>
  <div class="brush-exercise-directory-swipertab">
    <van-tabs
      :border="false"
      @click="checkedTab"
      :ellipsis="false"
      @change="change"
    >
      <van-tab
        v-for="(item, index) in module"
        :title="item.title"
        :key="index"
        :disabled="disabled"
      ></van-tab>
    </van-tabs>
  </div>
</template>

<script>
import { createNamespacedHelpers } from 'vuex';
// import { throttle } from '@/utils/utils.js';
const { mapState } = createNamespacedHelpers('ItemBank');
export default {
  components: {},
  data() {
    return {
      disabled: false,
      timer: null,
    };
  },
  computed: {
    ...mapState({
      module: state => state.ItemBankModules,
    }),
  },
  watch: {},
  created() {},
  methods: {
    checkedTab(e) {
      const data = {
        type: this.module[e].type,
        id: this.module[e].id,
      };
      this.$emit('changModule', data);
    },
    change() {
      this.disabled = true;
      if (this.timer) {
        clearTimeout(this.timer);
      }
      this.timer = setTimeout(() => {
        this.disabled = false;
      }, 500);
    },
  },
};
</script>
