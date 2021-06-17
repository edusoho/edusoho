<template>
  <layout>
    <template #answer>
      <a-radio-group :default-value="value">
        <a-radio :style="radioStyle" :value="1">
          Option A
        </a-radio>
        <a-radio :style="radioStyle" :value="2">
          Option B
        </a-radio>
        <a-radio :style="radioStyle" :value="3">
          Option C
        </a-radio>
      </a-radio-group>
    </template>
  </layout>
</template>

<script>
import Layout from './Layout.vue';

export default {
  components: {
    Layout
  },

  data() {
    return {
      value: 1,
      radioStyle: {
        display: 'block',
        height: '30px',
        lineHeight: '30px',
        fontWeight: '400'
      }
    }
  }
}
</script>
