<template>
  <layout :question="question" />
</template>

<script>
import _ from 'lodash';
import Layout from './Layout.vue';

export default {
  name: 'Fill',

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
