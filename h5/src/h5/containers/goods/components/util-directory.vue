<template>
  <div v-if="util.isExist == 1" class="util-directory text-overflow">
    第{{ util.number }}{{ courseSettings.part_name ? courseSettings.part_name : $t('courseLearning.section2') }}：{{ util.title }}
  </div>
</template>
<script>
import { mapState } from 'vuex';

export default {
  name: 'UtilDirectory',
  props: {
    util: {
      type: Object,
      default: () => {},
    },
  },
  computed: {
    ...mapState(['courseSettings']),
  }
};
</script>
