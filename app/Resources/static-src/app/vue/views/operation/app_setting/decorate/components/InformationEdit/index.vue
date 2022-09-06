<template>
  <edit-layout>
    <template #title>{{ 'decorate.information_settings' | trans }}</template>
    
    <div class="color-gray text-14" style="padding: 0 24px;font-weight: 400;">{{ 'decorate.information_tips' | trans }}</div>
  </edit-layout>
</template>

<script>
import EditLayout from '../EditLayout.vue';

export default {
  name: 'InformationEdit',

  props: {
    moduleData: {
      type: Object,
      required: true
    }
  },

  components: {
    EditLayout,
  },
}
</script>
