<template>
  <div class="student-view-question">
    <!-- 单选 -->
    <single-choice>
      <template #situation>
        <situation />
      </template>
    </single-choice>
  </div>
</template>

<script>
import SingleChoice from './SingleChoice.vue';
import Situation from './Situation.vue';

export default {
  name: 'StudentView',

  components: {
    SingleChoice,
    Situation
  }
}
</script>
