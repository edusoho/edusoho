<template>
  <a-modal
    :visible="visible"
    :width="900"
    ok-text="保存"
    @cancel="handleCancel"
    @ok="handleOk"
  >
    <template #title>
      选择公开课
      <span class="modal-title-tips">仅显示已发布公开课</span>
    </template>

    <div>
      选择公开课：
      <a-select
        show-search
        placeholder="搜索公开课"
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
          {{ course.title }}
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
        <a class="ant-dropdown-link" @click="handleRemove(record.key)">移除</a>
      </span>
    </a-table>
  </a-modal>
</template>
<script>
import _ from 'lodash';
import { OpenCourse } from 'common/vue/service/index.js';

const columns = [
  {
    title: '课程名称',
    dataIndex: 'title',
    width: '40%'
  },
  {
    title: '创建时间',
    dataIndex: 'createdTime',
    width: '40%',
    customRender: function(text) {
      return moment(text).format('YYYY-MM-DD HH:mm');
    }
  },
  {
    title: '操作',
    width: '20%',
    scopedSlots: { customRender: 'action' }
  }
];

export default {
  name: 'SeleteCourseModal',

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
        params: {
          title: this.keyword
        }
      };

      const { data } = await OpenCourse.search(params);

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
