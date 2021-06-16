<template>
  <div>
    <single-choice />
    <choice />
  </div>
</template>

<script>
import SingleChoice from './components/SingleChoice.vue';
import Choice from './components/Choice.vue';

export default {
  components: {
    SingleChoice,
    Choice
  }
}
</script>
