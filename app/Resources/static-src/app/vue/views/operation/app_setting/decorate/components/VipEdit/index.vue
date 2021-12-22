<template>
  <edit-layout>
    <template #title>{{ 'decorate.members_only' | trans }}</template>

    <div class="design-editor">
      <a-form-model :label-col="labelCol" :wrapper-col="wrapperCol">
        <a-form-model-item :label="'decorate.title' | trans">
          <a-radio-group :default-value="moduleData.titleShow" @change="changeShowTitle">
            <a-radio value="show">
              {{ 'decorate.show' | trans }}
            </a-radio>
            <a-radio value="unshow">
              {{ 'decorate.do_not_show' | trans }}
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
        <a-form-model-item :label="'decorate.order2' | trans">
          <a-radio-group :default-value="moduleData.sort" @change="changeSort">
            <a-radio value="asc">
              {{ 'decorate.low_to_hign' | trans }}
            </a-radio>
            <a-radio value="desc">
              {{ 'decorate.hign_to_low' | trans }}
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
        <div class="vip-list">
          <div
            class="vip-list__item text-overflow"
            v-for="(item, index) in moduleData.items"
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
import EditLayout from '../EditLayout.vue';

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
      wrapperCol: { span: 18 }
    }
  },

  mounted() {
    const { titleShow, sort } = this.moduleData;

    _.assign(this, {
      titleShow: titleShow,
      sort
    });
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
