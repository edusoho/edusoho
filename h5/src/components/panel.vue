<template>
  <div class="e-panel">
    <div class="e-panel-heading">{{title}}</div>
    <div class="e-panel-body">
      <slot>
        暂无简介
      </slot>
    </div>
  </div>
</template>
<script>
export default {
  name: 'e-panel',
  props: ['title']
}
</script>
