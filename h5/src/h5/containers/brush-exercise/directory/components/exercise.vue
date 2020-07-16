<template>
  <div class="brush-exercise-exercise">
    <div v-for="(charp, index) in charps" :key="'charp' + index">
      <exercise-section class="exercise-charp"></exercise-section>
      <div
        v-for="(section, sectionIndex) in charp.childern"
        :key="'section' + sectionIndex"
      >
        <exercise-section class="exercise-section"></exercise-section>
        <div
          v-for="(task, taskIndex) in section.childern"
          :key="'task' + taskIndex"
        >
          <exercise-section class="exercise-task"></exercise-section>
        </div>
      </div>
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
    return {
      charps: [
        {
          title: '第一章 绪论',
          childern: [
            {
              title: '第一节 从头开始说这个故事的开始',
              childern: [
                {
                  title: '这是A的故事',
                },
              ],
            },
          ],
        },
      ],
    };
  },
  computed: {},
  watch: {},
  created() {},
  methods: {},
};
</script>
