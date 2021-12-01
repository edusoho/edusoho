<template>
  <a-modal
    :visible="visible"
    :width="900"
    @cancel="handleCancel"
  >
    <template #title>
      选择班级
      <span class="modal-title-tips">仅显示已发布班级</span>
    </template>

    <template #footer>
      <a-button key="back" @click="handleCancel">
        取消
      </a-button>
    </template>

    <div>
      <a-input-search
        v-model="keyword"
        placeholder="搜索班级"
        style="width: 240px;"
        allow-clear
        @search="onSearch"
      />
    </div>

    <a-table
      class="mt16"
      :columns="columns"
      :row-key="record => record.id"
      :data-source="data"
      :pagination="pagination"
      :loading="loading"
      @change="handleTableChange"
    >
      <span slot="action" slot-scope="text, record">
        <a class="ant-dropdown-link" @click="handleSelect(record)">选择</a>
      </span>
    </a-table>
  </a-modal>
</template>
<script>
import _ from 'lodash';
import { Classroom } from 'common/vue/service/index.js';

const columns = [
  {
    title: '班级名称',
    dataIndex: 'title',
    width: '40%',
    customRender: function(text, record) {
      return text ? text : record.courseSetTitle;
    }
  },
  {
    title: '商品价格',
    dataIndex: 'price',
    width: '15%',
    customRender: function(text) {
      return `${text} 元`;
    }
  },
  {
    title: '商品数量',
    dataIndex: 'courseNum',
    width: '15%'
  },
  {
    title: '创建时间',
    dataIndex: 'createdTime',
    width: '20%',
    customRender: function(text) {
      return moment(text).format('YYYY-MM-DD HH:mm');
    }
  },
  {
    title: '操作',
    width: '10%',
    scopedSlots: { customRender: 'action' }
  }
];

export default {
  name: 'ClassroomLinkModal',

  data() {
    return {
      visible: false,
      data: [],
      keyword: '',
      loading: false,
      columns,
      pagination: {
        pageSize: 10,
        current: 1,
        hideOnSinglePage: true
      }
    }
  },

  methods: {
    showModal() {
      this.visible = true;
      this.keyword = '';
      this.pagination.current = 1;
      this.fetch();
    },

    handleCancel() {
      this.visible = false;
    },

    handleSelect(record) {
      const { displayedTitle, id, title  } = record;
      const params = {
        type: 'classroom',
        target: {
          displayedTitle,
          id,
          title
        },
        url: ''
      };
      this.$emit('update-link', params);
      this.handleCancel();
    },

    onSearch() {
      this.pagination.current = 1;
      this.fetch();
    },

    handleTableChange(pagination) {
      const { current } = pagination;
      _.assign(this.pagination, {
        current
      });
      this.fetch();
    },

    async fetch() {
      this.loading = true;

      const { pageSize, current } = this.pagination;
      const params = {
        limit: pageSize,
        offset: pageSize * (current - 1),
        sort: '-createdTime',
        title: this.keyword
      };

      const { data, paging: { total } } = await Classroom.search(params);
      const pagination = { ...this.pagination };
      pagination.total = total;

      _.assign(this, {
        loading: false,
        data,
        pagination
      });
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
