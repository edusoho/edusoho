<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    @event-actions="handleClickAction"
  >
    item_bank_exercise
  </layout>
</template>

<script>
import _ from 'lodash';
import moduleMixin from '../moduleMixin';

export default {
  name: 'ItemBankExercise',

  mixins: [moduleMixin]
}
</script>

