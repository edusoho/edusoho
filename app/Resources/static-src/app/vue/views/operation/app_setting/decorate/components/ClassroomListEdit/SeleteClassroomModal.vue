<template>
  <a-modal
    :visible="visible"
    :width="900"
    :ok-text="'site.btn.save' | trans"
    @cancel="handleCancel"
    @ok="handleOk"
  >
    <template #title>
      {{ 'decorate.select_class' | trans }}
      <span class="modal-title-tips">{{ 'decorate.show_only_published_classes' | trans }}</span>
    </template>

    <div>
      {{ 'decorate.select_class' | trans }}：
      <a-select
        show-search
        :placeholder="'decorate.search_class' | trans"
        style="width: 300px"
        :default-active-first-option="false"
        :show-arrow="false"
        :filter-option="false"
        allow-clear
        :not-found-content="null"
        @search="onSearch"
        @change="handleChange"
      >
        <a-select-option v-for="course in data" :key="course.id">
          {{ course.title || course.courseSetTitle }}
        </a-select-option>
      </a-select>
    </div>

    <a-table
      class="mt16"
      :columns="columns"
      :row-key="record => record.id"
      :pagination="false"
      :data-source="selectList"
    >
      <span slot="action" slot-scope="text, record">
        <a class="ant-dropdown-link" @click="handleRemove(record.key)">{{ 'decorate.remove' | trans }}</a>
      </span>
    </a-table>
  </a-modal>
</template>
<script>
import _ from 'lodash';
import { Classroom } from 'common/vue/service/index.js';

const columns = [
  {
    title: Translator.trans('decorate.class_name'),
    dataIndex: 'title',
    width: '40%'
  },
  {
    title: Translator.trans('decorate.commodity_price'),
    dataIndex: 'price',
    width: '15%',
    customRender: function(text) {
      return `${text} ${Translator.trans('cny')}`;
    }
  },
  {
    title: Translator.trans('decorate.amount_of_goods'),
    dataIndex: 'courseNum',
    width: '15%'
  },
  {
    title: Translator.trans('decorate.creation_time'),
    dataIndex: 'createdTime',
    width: '20%',
    customRender: function(text) {
      return moment(text).format('YYYY-MM-DD HH:mm');
    }
  },
  {
    title: Translator.trans('decorate.operate'),
    width: '10%',
    scopedSlots: { customRender: 'action' }
  }
];

export default {
  name: 'SeleteClassroomModal',

  data() {
    return {
      visible: false,
      data: [],
      selectList: [],
      keyword: '',
      columns
    }
  },

  methods: {
    showModal() {
      this.visible = true;
      this.keyword = '';
    },

    handleCancel() {
      this.visible = false;
    },

    onSearch: _.debounce(function(value) {
      this.keyword = value;
      this.fetch();
    }, 200),

    async fetch() {
      const params = {
        sort: '-createdTime',
        title: this.keyword
      };

      const { data } = await Classroom.search(params);

      this.data = data;
    },

    handleChange(value) {
      _.forEach(this.data, item => {
        if (item.id === value) {
          this.selectList.push(item);
          return false;
        }
      });
    },

    handleRemove(value) {
      this.selectList.splice(value, 1);
    },

    handleOk() {
      this.$emit('update-items', this.selectList);
      this.visible = false;
    }
  },
};
</script>

<style lang="less" scoped>
.modal-title-tips {
  margin-left: 10px;
  font-size: 12px;
  color: #919191;
}
</style>
