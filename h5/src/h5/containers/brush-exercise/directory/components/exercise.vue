<template>
  <div class="brush-exercise-exercise">
    <van-loading v-if="isLoading" vertical size="24" color="#1989fa"
      >加载中...</van-loading
    >
    <template v-if="exercise.length">
      <div v-for="(item, index) in newExercise" :key="item.id" :ref="'exercise_' + item.id">
        <exercise-section
          :exercise-id="exerciseId"
          :module-id="moduleId"
          :is-last="index + 1 === newExercise.length"
          :level="0"
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
    return {
      newExercise: [],
    };
  },
  props: {
    exercise: {
      type: Array,
      default: [],
    },
    isLoading: {
      type: Boolean,
      default: true,
    },
    moduleId: {
      type: String,
      default: '',
    },
    exerciseId: {
      type: Number,
      default: -1,
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
      this.newExercise = this.nestItems(newVal);
      if (newVal.length > 0) {
        const categoryId = this.$route.query.categoryId;
        if (categoryId && this.newExercise.length > 0) {
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
    nestItems(data) {
      const idMap = {};
      data.forEach(item => {
        idMap[item.id] = { ...item, children: [] };
      });
      const result = [];
      data.forEach(item => {
        const parentId = item.parent_id;
        if (parentId === "0") {
          result.push(idMap[item.id]);
        } else {
          const parent = idMap[parentId];
          if (parent) {
            parent.children.push(idMap[item.id]);
          }
        }
      });
      return result;
    },
    scrollToCategory() {
      const targetElement = this.$refs['exercise_' + this.$route.query.categoryId];
      if (targetElement) {
        const offsetTop = targetElement[0].offsetTop || targetElement.offsetTop;
        window.scrollTo({
          top: offsetTop + 222,
          behavior: 'smooth',
        });
      }
    }
  },
};
</script>
