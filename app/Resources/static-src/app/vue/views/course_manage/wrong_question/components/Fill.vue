<template>
  <layout :question="question" :order="order" />
</template>

<script>
import answerMixin from './answerMixin.js';

export default {
  name: 'Fill',

  mixins: [answerMixin]
}
</script>
