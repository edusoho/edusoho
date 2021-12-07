<template>
  <edit-layout>
    <template #title>优惠卷</template>
    <template #subtitle>（仅显示未过期的优惠券）</template>

    <div class="design-editor">
      design-editor
    </div>
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';

export default {
  name: 'CouponEdit',

  props: {
    moduleData: {
      type: Object,
      required: true
    }
  },

  components: {
    EditLayout
  }
}
</script>
