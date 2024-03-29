<template>
  <a-modal
    :title="product.title + '-班课列表'"
    :width="1240"
    :footer="null"
    :visible="visible"
    @cancel="handleCancel"
  >
    <a-spin :spinning="ajaxLoading">
      <a-table
        :columns="columns"
        :data-source="multiClassList"
        :pagination="paging"
        @change="handleTableChange"
      >
        <a slot="class_title" slot-scope="text, record"
          href="javascript:;"
          @click="goToMultiClassManage(record.id)">
          {{ text }}
        </a>
        <a slot="course" slot-scope="text, record"
          :href="`/course/${record.courseId}`"
          target="_blank">
          {{ record.course ? record.course.courseSetTitle : '' }}
        </a>
        <a slot="taskNum" slot-scope="text, record"
          href="javascript:;"
          @click="goToMultiClassManage(record.id)">
          {{ record.endTaskNum }}/{{ record.taskNum }}
        </a>
        <template slot="assistant" slot-scope="assistant">
          {{ assistant ? assistant.join('、') : '' }}
        </template>
        <template slot="createdTime" slot-scope="createdTime">
          {{ $dateFormat(createdTime, 'YYYY-MM-DD HH:mm') }}
        </template>
        <a slot="studentNum" slot-scope="text, record"
          :href="`/admin/v2/multi_class/index#/manage/${record.id}/student_manage`">
          {{ text }}
        </a>
        <template :size="8" slot="action" slot-scope="text, record">
          <a-button type="link" @click="goToMultiClassManage(record.id)">管理</a-button>
          <a-button type="link" @click="goToEditMultiClass(record.id)">编辑</a-button>
          <a-button type="link" @click="goToMultiClassDataPreview(record.id)">数据概览</a-button>
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
      scopedSlots: { customRender: 'class_title' },
    },
    {
      title: '课程名称',
      dataIndex: 'course',
       scopedSlots: { customRender: 'course' },
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
      dataIndex: 'studentNum',
      scopedSlots: { customRender: 'studentNum' },
    },
    {
      title: '创建时间',
      dataIndex: 'createdTime',
      scopedSlots: { customRender: 'createdTime' },
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
        multiClassList: [],
        paging: [],
        ajaxLoading: false,
        columns,
      };
    },
    watch: {
      product: {
        immediate: true,
        handler: 'searchMultiClassList',
      }
    },
    created() {
    },
    methods: {
      handleCancel() {
        this.$emit('close', false)
      },
      async searchMultiClassList (data = {}) {
        if (!this.product) return
        const params = {};
        params.limit = data.pageSize || 10
        params.offset = data.offset || 0
        params.productId = this.product.id;

        this.ajaxLoading = true;
        try {
          const { data, paging } = await MultiClass.search(params)

          paging.page = (paging.offset / paging.limit) + 1;
          paging.pageSize = Number(paging.limit);

          this.multiClassList = data;
          this.paging = paging;
        } finally {
          this.ajaxLoading = false;
        }
      },

      goToMultiClassManage(id) {
        window.location.href = `/admin/v2/multi_class/index#/manage/${id}`
      },
      goToEditMultiClass(id) {
        window.location.href = `/admin/v2/multi_class/index#/create?id=${id}`
      },
      goToMultiClassDataPreview(id) {
        window.location.href = `/admin/v2/multi_class/index#/manage/${id}/data_preview`
      },

      handleTableChange(pagination) {
        const params = {}

        if (pagination) {
          params.offset = pagination.pageSize * (pagination.current - 1)
          params.pageSize = pagination.pageSize,
          params.current = pagination.current
        }

        this.searchMultiClassList(params)
      }
    }
  };
  </script>
