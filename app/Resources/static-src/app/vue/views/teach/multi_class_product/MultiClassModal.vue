<template>
  <a-modal
      :title="title + '-班课列表'"
      :width="1240"
      :footer="null"
      :visible="visible"
      @cancel="handleCancel"
    >
    <a-spin :spinning="!multiClassList">
      <a-table :columns="columns" :data-source="multiClassList">
        <a slot="title" slot-scope="text" >
          {{ text }}
        </a>
        <a slot="course" slot-scope="text">
          {{ text }}
        </a>
        <a slot="taskNum" slot-scope="text, record">
          {{ record.taskNum - record.notStartLiveTaskNum }}/{{ record.taskNum }}
        </a>
        <a slot="num1" slot-scope="text">
          {{ text }}
        </a>
        <template :size="8" slot="action" slot-scope="text, record">
          <a-button type="link">查看</a-button>
          <a-button type="link">编辑</a-button>
          <a-button type="link">数据概览</a-button>
        </template>
      </a-table>
    </a-spin>
  </a-modal>
</template>

<script>
  import { MultiClass } from 'common/vue/service/index.js';

  const columns = [
    {
      title: '班课名称',
      dataIndex: 'title',
      scopedSlots: { customRender: 'name' },
    },
    {
      title: '课程名称',
      dataIndex: 'course',
       scopedSlots: { customRender: 'name2' },
    },
    {
      title: '价格',
      dataIndex: 'price',
    },
    {
      title: '已完成/课时',
      dataIndex: 'taskNum', // taskNum、notStartLiveTaskNum
      scopedSlots: { customRender: 'taskNum' },
    },
    {
      title: '授课老师',
      dataIndex: 'teacher',
    },
    {
      title: '助教老师',
      dataIndex: 'assistant',
      scopedSlots: { customRender: 'assistant' },
    },
    {
      title: '已报班人数',
      dataIndex: 'num1',
      scopedSlots: { customRender: 'num1' },
    },
    {
      title: '创建时间',
      dataIndex: 'createdTime',
    },
    {
      title: '操作',
      dataIndex: 'action',
      scopedSlots: { customRender: 'action' },
    },
  ];
  
  export default {
    props: {
      product: {
        type: Object,
        required: true,
      },
      visible: {
        type: Boolean,
        required: true,
        default: false
      },
    },
    data() {
      return {
        multiClassList,
        columns,
      };
    },
    watch: {
      product: {
        immediate: true,
        handler: 'searchMultiClassList',
      }
    },
    methods: {
      handleCancel() {
        this.$emit('close', false)
      },
      async searchMultiClassList() {
        if (!this.product) return

        try {
          const res = await MultiClass.search({ id: this.id })

          console.log(res)
        } finally {

        }
      }
    }
  };
  </script>