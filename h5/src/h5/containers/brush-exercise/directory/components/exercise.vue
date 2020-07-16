<template>
  <div class="brush-exercise-exercise">
    <div v-for="(charp, index) in charps" :key="'charp' + index">
      <div class="exercise-charp">{{ charp.title }}</div>
      <div
        v-for="(section, sectionIndex) in charp.childern"
        :key="'section' + sectionIndex"
      >
        <div class="exercise-section">{{ section.title }}</div>
        <div
          v-for="(task, taskIndex) in section.childern"
          :key="'task' + taskIndex"
        >
          <div class="exercise-task">{{ task.title }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  components: {},
  data() {
    return {
      charps: [
        {
          title: '章',
          childern: [
            {
              title: '节',
              childern: [
                {
                  title: '课时',
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
