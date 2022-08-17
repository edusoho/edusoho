<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    :preview="preview"
    :validator-result="validatorResult"
    @event-actions="handleClickAction"
  >
    <div class="information">
      <div class="information__item clearfix">
        <div class="number" style="background-color: #F53F3F;">1</div>
        <div class="content text-overflow">资讯内容一</div>
      </div>
      <div class="information__item clearfix">
        <div class="number" style="background-color: #FF900E;">2</div>
        <div class="content text-overflow">资讯内容二</div>
      </div>
      <div class="information__item clearfix">
        <div class="number" style="background-color: #F9CC45;">3</div>
        <div class="content text-overflow">资讯内容三</div>
      </div>
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import moduleMixin from '../moduleMixin';

export default {
  name: 'Information',

  mixins: [moduleMixin],
}
</script>

<style lang="less" scoped>
  .information {
    margin: 0 16px;
    padding: 0 12px;
    background-color: #fff;
    border-radius: 6px;

    &__item:not(:last-child) {
      border-bottom: 1px solid #F2F3F5;
    }

    &__item {
      padding: 12px 0;

      .number {
        float: left;
        width: 18px;
        height: 18px;
        margin-top: 2px;
        margin-right: 8px;
        text-align: center;
        line-height: 18px;
        color: #fff;
        font-weight: 500;
        font-size: 14px;
        border-radius: 4px;
      }

      .content {
        float: left;
        width: 266px;
        line-height: 22px;
        color: #1D2129;
        font-size: 14px;
      }
    }
  }
</style>
