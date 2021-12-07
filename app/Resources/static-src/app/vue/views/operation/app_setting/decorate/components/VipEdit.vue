<template>
  <edit-layout>
    <template #title>会员专区</template>

    <div class="design-editor">
      <a-form-model :label-col="labelCol" :wrapper-col="wrapperCol">
        <a-form-model-item label="标题栏">
          <a-radio-group v-model="titleShow" @change="changeShowTitle">
            <a-radio value="show">
              显示
            </a-radio>
            <a-radio value="unshow">
              不显示
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
        <a-form-model-item label="排列顺序：">
          <a-radio-group v-model="sort" @change="changeSort">
            <a-radio value="asc">
              从低到高
            </a-radio>
            <a-radio value="desc">
              从高到低
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
        <div class="vip-list">
          <div
            class="vip-list__item text-overflow"
            v-for="(item, index) in vipItems"
            :key="index"
          >
            {{ item.name }}
          </div>
        </div>
      </a-form-model>
    </div>
  </edit-layout>
</template>

<script>
import _ from 'lodash';
import EditLayout from './EditLayout.vue';

export default {
  name: 'VipEdit',

  props: {
    moduleData: {
      type: Object,
      required: true
    }
  },

  components: {
    EditLayout
  },

  data() {
    return {
      labelCol: { span: 5 },
      wrapperCol: { span: 18 },
      titleShow: 'show',
      sort: 'asc',
      vipItems: []
    }
  },

  mounted() {
    const { titleShow, sort } = this.moduleData;

    _.assign(this, {
      titleShow: titleShow,
      sort
    });

    this.getVipitems();
  },

  methods: {
    changeShowTitle(e) {
      this.$emit('update-edit', {
        type: 'vip',
        key: 'titleShow',
        value: e.target.value
      });
    },

    changeSort(e) {
      const value = e.target.value;
      this.$emit('update-edit', {
        type: 'vip',
        key: 'sort',
        value
      });
    },

    getVipitems() {
      const { sort, items } = this.moduleData;

      if (sort === 'ast') {
        this.vipItems = items;
        return;
      }

      const tempItems = _.cloneDeep(items);
      this.vipItems = tempItems.reverse();
    }
  }
}
</script>

<style lang="less" scoped>
.vip-list {
  padding-right: 8px;
  padding-left: 8px;
  background: rgba(237, 237, 237, 0.53);

  &__item {
    padding: 8px 0;
    border-bottom: 1px solid #eee;

    &:last-child {
      border-bottom: none;
    }
  }
}
</style>
