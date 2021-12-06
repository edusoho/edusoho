<template>
  <edit-layout>
    <template #title>会员专区</template>

    <div class="design-editor">
      <a-form-model :label-col="labelCol" :wrapper-col="wrapperCol">
        <a-form-model-item label="标题栏">
          <a-radio-group v-model="showTitle">
            <a-radio value="show">
              显示
            </a-radio>
            <a-radio value="unshow">
              不显示
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
        <a-form-model-item label="排列顺序：">
          <a-radio-group v-model="sort">
            <a-radio value="asc">
              从低到高
            </a-radio>
            <a-radio value="desc">
              从高到低
            </a-radio>
          </a-radio-group>
        </a-form-model-item>
        <div v-for="(item, index) in moduleInfo.items" :key="index">
          {{ item.name }}
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
    moduleInfo: {
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
      moduleData: [],
      showTitle: 'show',
      sort: 'asc'
    }
  },

  mounted() {
    const { titleShow, sort } = this.moduleInfo;
    _.assign(this, {
      showTitle: titleShow,
      sort
    });
  }
}
</script>
