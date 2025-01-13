<template>
  <div class="brush-exercise-exercise">
    <van-loading v-if="isLoading" vertical size="24" color="#1989fa"
      >加载中...</van-loading
    >
    <template v-if="exercise.length">
      <div v-for="(item, index) in exercise" :key="item.id" :ref="item.id">
        <exercise-section
          v-bind="$attrs"
          :class="getClass(item.depth)"
          :section="item"
        ></exercise-section>
      </div>
    </template>

    <empty v-if="noData" text="暂无练习" class="empty__exam" />
  </div>
</template>

<script>
import empty from '&/components/e-empty/e-empty.vue';
import exerciseSection from './exercise-section.vue';
export default {
  components: {
    exerciseSection,
    empty,
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
  computed: {
    noData: function() {
      return !this.isLoading && !this.exercise.length;
    },
  },
  created() {},
  watch: {
    exercise(newVal) {
      if (newVal.length > 0) {
        const categoryId = this.$route.query.categoryId;
        if (categoryId && this.exercise.length > 0 && this.exercise[0].id !== categoryId) {
          this.$nextTick(() => {
            this.scrollToCategory(categoryId);
          });
        }
      }
    }
  },
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
    scrollToCategory() {
      const targetElement = this.$refs[this.$route.query.categoryId];
      if (targetElement) {
        const offsetTop = targetElement[0].offsetTop || targetElement.offsetTop;
        window.scrollTo({
          top: offsetTop + 245.375,
          behavior: 'smooth',
        });
      }
    }
  },
};
</script>
