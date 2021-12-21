<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    :preview="preview"
    :validator-result="validatorResult"
    @event-actions="handleClickAction"
  >
    <div class="poster">
      <img v-if="moduleData.image.uri" :src="moduleData.image.uri" alt="">
      <div v-else class="poster-empty">广告图片</div>
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import moduleMixin from '../moduleMixin';

export default {
  name: 'Poster',

  mixins: [moduleMixin]
}
</script>

<style lang="less" scoped>
.poster {
  padding: 0 16px;

  img {
    width: 100%;
  }

  &-empty {
    width: 100%;
    height: 194px;
    background-color: #e1e1e1;
    font-size: 26px;
    color: #919191;
    font-weight: bold;
    text-align: center;
    line-height: 194px;
  }
}
</style>
