<template>
  <a-modal
    :visible="visible"
    :width="900"
    @cancel="handleCancel"
  >
    <template #title>
      选择课程
      <span class="modal-title-tips">仅显示已发布课程</span>
    </template>

    <template #footer>
      <a-button key="back" @click="handleCancel">
        取消
      </a-button>
    </template>

    <div>
      <a-input placeholder="搜索课程" style="width: 240px;" allow-clear>
        <a-icon slot="suffix" type="search" />
      </a-input>
    </div>

    <a-table class="mt16" :columns="columns" :data-source="data">
      <a slot="name" slot-scope="text">{{ text }}</a>
      <span slot="customTitle"><a-icon type="smile-o" /> Name</span>
      <span slot="tags" slot-scope="tags">
        <a-tag
          v-for="tag in tags"
          :key="tag"
          :color="tag === 'loser' ? 'volcano' : tag.length > 5 ? 'geekblue' : 'green'"
        >
          {{ tag.toUpperCase() }}
        </a-tag>
      </span>
      <span slot="action" slot-scope="text, record">
        <a class="ant-dropdown-link" @click="handleSelect(record)">选择</a>
      </span>
    </a-table>
  </a-modal>
</template>
<script>
const columns = [
  {
    dataIndex: 'name',
    key: 'name',
    slots: { title: 'customTitle' },
    scopedSlots: { customRender: 'name' },
  },
  {
    title: 'Age',
    dataIndex: 'age',
    key: 'age',
  },
  {
    title: 'Address',
    dataIndex: 'address',
    key: 'address',
  },
  {
    title: 'Tags',
    key: 'tags',
    dataIndex: 'tags',
    scopedSlots: { customRender: 'tags' },
  },
  {
    title: 'Action',
    key: 'action',
    scopedSlots: { customRender: 'action' },
  },
];

const data = [
  {
    key: '1',
    name: 'John Brown',
    age: 32,
    address: 'New York No. 1 Lake Park',
    tags: ['nice', 'developer'],
  },
  {
    key: '2',
    name: 'Jim Green',
    age: 42,
    address: 'London No. 1 Lake Park',
    tags: ['loser'],
  },
  {
    key: '3',
    name: 'Joe Black',
    age: 32,
    address: 'Sidney No. 1 Lake Park',
    tags: ['cool', 'teacher'],
  },
];

export default {
  name: 'CourseLinkModal',

  data() {
    return {
      visible: false,
      data,
      columns,
      pagination: {
        hideOnSinglePage: true
      }
    }
  },

  methods: {
    showModal() {
      this.visible = true;
    },

    handleCancel() {
      this.visible = false;
    },

    handleSelect(record) {

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
