<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    :validator-result="validatorResult"
    @event-actions="handleClickAction"
  >
    <div class="gn-list clearfix">
      <item
        v-for="(item, index) in moduleData"
        :key="index"
        class="pull-left"
        :style="{ width: itemWidth }"
        :item="item"
      />
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import moduleMixin from '../moduleMixin';
import Item from './Item.vue';

export default {
  name: 'GraphicNavigation',

  mixins: [moduleMixin],

  components: {
    Item
  },

  computed: {
    itemWidth() {
      const length =  _.clamp(_.size(this.moduleData), 1, 4);
      return `${100 / length}%`;
    }
  }
}
</script>

<style lang="less" scoped>
.gn-list {
  margin-right: 16px;
  margin-left: 16px;
  background: linear-gradient(
    to left,
    rgba(255, 255, 255, 0.1) 0%,
    rgba(255, 255, 255, 0.8) 25%,
    rgba(255, 255, 255, 1) 50%,
    rgba(255, 255, 255, 0.8) 75%,
    rgba(255, 255, 255, 0.1) 100%
  );
}
</style>
