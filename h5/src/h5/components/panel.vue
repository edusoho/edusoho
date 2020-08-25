<template>
  <div class="e-panel">
    <div v-show="!hiddeTitle" class="e-panel-heading">{{ title }}</div>
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
    'hidde-title': '',
    title: '',
    defaulValue: '',
    needFlex: {
      type: Boolean,
      default: true
    }
  }
}
</script>
