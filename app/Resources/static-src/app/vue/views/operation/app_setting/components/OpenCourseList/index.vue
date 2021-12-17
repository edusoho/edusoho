<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    @event-actions="handleClickAction"
  >
    open-course-list
  </layout>
</template>

<script>
import _ from 'lodash';
import moduleMixin from '../moduleMixin';

export default {
  name: 'OpenCourseList',

  mixins: [moduleMixin]
}
</script>

