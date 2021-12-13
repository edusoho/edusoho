<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    @event-actions="handleClickAction"
  >
    graphic_navigation
  </layout>
</template>

<script>
import _ from 'lodash';
import moduleMixin from '../moduleMixin';

export default {
  name: 'GraphicNavigation',

  mixins: [moduleMixin]
}
</script>

<style lang="less" scoped>

</style>
