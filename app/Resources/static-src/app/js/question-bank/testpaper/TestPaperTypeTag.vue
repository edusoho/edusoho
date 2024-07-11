<script>

export default {
  props: {
    type: null
  },
};
</script>

<template>
  <div v-if="type === 'random'" class="paper-type paper-type-random">{{ 'question.bank.paper.random'|trans }}</div>
  <div v-else-if="type === 'regular'" class="paper-type paper-type-regular">
    {{ 'question.bank.paper.regular'|trans }}
  </div>
  <div v-else-if="type === 'ai_personality'" class="paper-type paper-type-ai">
    {{ 'question.bank.paper.ai_personality'|trans }}
  </div>
</template>
