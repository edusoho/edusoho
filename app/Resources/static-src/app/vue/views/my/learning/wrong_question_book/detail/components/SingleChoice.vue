<template>
  <layout :question="question">
    <template #answer>
      SingleChoice
    </template>
  </layout>
</template>

<script>
import _ from 'lodash';
import Layout from './Layout.vue';

export default {
  name: 'SingleChoice',

  props: {
    question: {
      type: Object,
      required: true
    }
  },

  components: {
    Layout
  }
}
</script>
