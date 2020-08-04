<template>
  <div class="brush-exercise-exercise">
    <van-loading v-if="isLoading" vertical size="24" color="#1989fa"
      >加载中...</van-loading
    >
    <div v-else v-for="(item, index) in exercise" :key="'exercise' + index">
      <exercise-section
        v-bind="$attrs"
        :class="getClass(item.depth)"
        :section="item"
      ></exercise-section>
    </div>
  </div>
</template>

<script>
import exerciseSection from './exercise-section.vue';
export default {
  components: {
    exerciseSection,
  },
  data() {
    return {};
  },
  props: {
    exercise: {
      type: Array,
      default() {
        return [];
      },
    },
    isLoading: {
      type: Boolean,
      default: true,
    },
  },
  computed: {},
  watch: {},
  created() {},
  methods: {
    getClass(depth) {
      switch (depth) {
        case 1:
          return 'exercise-charp';
        case 2:
          return 'exercise-section';
        case 3:
          return 'exercise-task';
      }
    },
  },
};
</script>
