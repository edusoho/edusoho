<template>
  <edit-layout>
    <template #title>题库列表设置</template>

    <div class="design-editor">
      ItemBankExercise
    </div>
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from '../EditLayout.vue';

export default {
  name: 'ItemBankExerciseEdit',

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
