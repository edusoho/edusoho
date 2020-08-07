<template>
  <div class="brush-exercise-directory-swipertab">
    <div class="brush-exercise-warp">
      <div class="brush-exercise-scroll">
        <button
          v-for="(item, index) in module"
          :key="index"
          :class="{ activeTag: index === activeIndex }"
          @click="checkedTab(item, index)"
          :disabled="disabled"
        >
          {{ item.title }}
        </button>
      </div>
    </div>

    <!-- <van-tabs
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
    </van-tabs> -->
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
      activeIndex: 0,
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
    checkedTab(item, index) {
      this.activeIndex = index;
      const data = {
        type: item.type,
        id: item.id,
      };
      this.$emit('changModule', data);
      this.change();
    },
    change() {
      this.disabled = true;
      if (this.timer) {
        clearTimeout(this.timer);
      }
      this.timer = setTimeout(() => {
        this.disabled = false;
      }, 300);
    },
  },
};
</script>
