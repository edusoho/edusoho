<template>
  <edit-layout>
    <template #title>会员专区</template>
  </edit-layout>
</template>

<script>
import EditLayout from './EditLayout.vue';

export default {
  name: 'VipEdit',

  props: {
    moduleInfo: {
      type: Array,
      required: true
    }
  },

  components: {
    EditLayout
  },

  data() {
    return {
      moduleData: []
    }
  }
}
</script>

