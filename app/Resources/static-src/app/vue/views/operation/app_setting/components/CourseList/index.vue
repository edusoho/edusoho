<template>
  <layout
    :active="moduleType === currentModuleType"
    :is-first="isFirst"
    :is-last="isLast"
    @event-actions="handleClickAction"
  >
    <div class="course-list">
      <div class="clearfix">
        <div class="course-list__title pull-left text-overflow">推荐课程</div>
        <div class="course-list__more pull-right">查看更多<a-icon type="right" /></div>
      </div>
      <div class="">
        <!-- <column-item />
        <column-item />
        <column-item />
        <column-item /> -->
        <row-item />
        <row-item />
        <row-item />
        <row-item />
      </div>
    </div>
  </layout>
</template>

<script>
import _ from 'lodash';
import moduleMixin from '../moduleMixin';
import ColumnItem from './ColumnItem.vue';
import RowItem from './RowItem.vue';

export default {
  name: 'CourseList',

  mixins: [moduleMixin],

  components: {
    ColumnItem,
    RowItem
  }
}
</script>

<style lang="less" scoped>
.course-list {
  padding-right: 16px;
  padding-left: 16px;

  .course-list__title {
    position: relative;
    padding-left: 10px;
    max-width: 60%;
    font-size: 16px;
    font-weight: 500;
    color: #333;
    line-height: 24px;

    &::before {
      content: "";
      position: absolute;
      top: 6px;
      left: 0;
      width: 4px;
      height: 12px;
      background: #03c777;
      border-radius: 1px;
    }
  }

  &__more {
    margin-top: 4px;
    font-size: 12px;
    color: #999;
    line-height: 16px;
  }
}
</style>

