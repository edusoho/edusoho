<template>
  <layout :question="question" />
</template>

<script>
import answerMixin from './answerMixin.js';

export default {
  name: 'Fill',

  mixins: [answerMixin]
}
</script>
