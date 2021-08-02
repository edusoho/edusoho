<template>
  <div v-if="util.isExist == 1" class="util-directory text-overflow">
    {{ $t('goods.section', { number: util.number }) }}：{{ util.title }}
  </div>
</template>
<script>
export default {
  name: 'UtilDirectory',
  props: {
    util: {
      type: Object,
      default: () => {},
    },
  },
};
</script>
