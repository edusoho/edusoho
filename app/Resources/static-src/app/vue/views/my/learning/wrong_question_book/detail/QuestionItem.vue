<template>
  <div class="student-view-question">
    <!-- 单选 -->
    fsaf
    <single-choice>
      <template #situation>
        <situation />
      </template>
    </single-choice>
  </div>
</template>

<script>
import SingleChoice from '../question/SingleChoice.vue';
import Situation from '../question/components/Situation.vue';

export default {
  name: 'StudentView',

  components: {
    SingleChoice,
    Situation
  }
}
</script>
