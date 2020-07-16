<template>
  <div class="brush-exercise-directory" style="background:#F5F5F5">
    <swiperTab />
    <exam />
    <exercise />
  </div>
</template>

<script>
import swiperTab from './components/swiper-tab.vue';
import exam from './components/exam.vue';
import exercise from './components/exercise.vue';
export default {
  components: {
    swiperTab,
    exam,
    exercise,
  },
  data() {
    return {};
  },
  computed: {},
  watch: {},
  created() {},
  methods: {},
};
</script>
