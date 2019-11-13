<template>
  <div class="e-panel">
    <div class="e-panel-heading" v-show="!hiddeTitle">{{title}}</div>
    <div class="e-panel-body" :class="needFlex ? 'flex' : ''">
      <slot>
        {{ defaulValue || '暂无简介' }}
      </slot>
    </div>
  </div>
</template>
<script>
export default {
  name: 'e-panel',
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
