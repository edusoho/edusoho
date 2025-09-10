<template>
  <div class="e-panel">
    <div v-show="!hiddeTitle" class="e-panel-heading">
      <span class="certificate-icon" v-if="type">证</span>{{ title }}
    </div>
    <div :class="needFlex ? 'flex' : ''" class="e-panel-body">
      <slot>
        {{ defaulValue || '暂无简介' }}
      </slot>
    </div>
  </div>
</template>
<script>
export default {
  name: 'EPanel',
  props: {
    'hidde-title': {
      type: Boolean,
      default: false,
    },
    title: {
      type: String,
      default: '',
    },
    defaulValue: {
      type: String,
      default: '',
    },
    needFlex: {
      type: Boolean,
      default: true,
    },
    type: {
      type: Boolean,
      default: false,
    },
  },
};
</script>
<style scoped>
.e-panel-heading {
  width: 343px;
}
</style>
