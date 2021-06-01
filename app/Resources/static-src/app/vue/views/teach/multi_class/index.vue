<template>
  <a-spin :spinning="getListLoading">
    <div class="clearfix cd-mb24">
      <a-input-search placeholder="请输入课程或老师关键字搜索" style="width: 224px" @search="searchMultiClass" />
      <a-button class="pull-right" type="primary" @click="goToCreateMultiClassPage">新建班课</a-button>
    </div>

    <a-table :columns="columns"
      :pagination="paging"
      :data-source="multiClassList"
      :rowKey="record => record.id">
      <a slot="class_title" slot-scope="text, record"
        href="javascript:;"
        @click="goToMultiClassManage(record.id)">
        {{ text }}
      </a>
      <a slot="taskNum" slot-scope="text, record"
        href="javascript:;"
        @click="goToMultiClassManage(record.id)">
        {{ record.endTaskNum }}/{{ record.taskNum }}
      </a>
      <a slot="course" slot-scope="course"
        :href="`/course/${course.id}`">
        {{ course.title || course.courseSetTitle }}
      </a>
      <template slot="assistant" slot-scope="assistant">
        {{ assistant ? assistant.join('、') : '' }}
      </template>
      <a slot="studentNum" slot-scope="text, record"
        href="javascript:;"
        @click="$router.push({ name: 'MultiClassStudentManage', params: { id: record.id } })">
        {{ text }}
      </a>
      <template slot="createdTime" slot-scope="createdTime">
        {{ $dateFormat(createdTime, 'YYYY-MM-DD HH:mm') }}
      </template>
      <template slot="action" slot-scope="text, record">
        <a href="javascript:;" class="mr2"
          @click="goToMultiClassManage(record.id)">查看</a>
        <a href="javascript:;" class="mr2"
          @click="$router.push({ name: 'MultiClassDataPreview', params: { id: record.id}})">数据概览</a>
        <a-dropdown>
          <a href="javascript:;" @click="e => e.preventDefault()">
            更多 <a-icon type="down" />
          </a>
          <a-menu slot="overlay">
            <a-menu-item>
              <a href="javascript:;"
                @click="$router.push({ name: 'MultiClassCreate', query: { id: record.id } })">
                编辑
              </a>
            </a-menu-item>
            <a-menu-item>
              <a href="javascript:;">复制班课</a>
            </a-menu-item>
            <a-menu-item>
              <a href="javascript:;" class="color-danger" @click="deleteMultiClass(record)">删除</a>
            </a-menu-item>
          </a-menu>
        </a-dropdown>
      </template>
    </a-table>

    <div class="text-center cd-mt24">
      <a-pagination
        v-if="paging && multiClassList.length > 0"
        v-model="paging.page"
        :total="paging.total"
        show-less-items
      />
    </div>
  </a-spin>
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
    title: '所属产品',
    dataIndex: 'product',
    filters: [],
  },
  {
    title: '价格',
    dataIndex: 'price',
    sorter: true,
  },
  {
    title: '已完成/课时',
    dataIndex: 'taskNum',
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
    sorter: true,
    scopedSlots: { customRender: 'studentNum' },
  },
  {
    title: '创建时间',
    dataIndex: 'createdTime',
    sorter: true,
    scopedSlots: { customRender: 'createdTime' },
  },
  {
    title: '操作',
    dataIndex: 'action',
    scopedSlots: { customRender: 'action' },
  },
];

export default {
  name: 'MultiClassList',
  data () {
    return {
      columns,
      multiClassList: [],
      getListLoading: false,
      paging: {
        total: 0,
        offset: 0,
        limit: 10,
      },
    }
  },
  created() {
    this.getMultiClassList()
  },
  methods: {
    goToCreateMultiClassPage() {
      this.$router.push({
        name: 'MultiClassCreate'
      })
    },
    async getMultiClassList (params = {}) {
      this.getListLoading = true;
      try {
        const { data, paging } = await MultiClass.search({
          keywords: params.keywords || '',
          offset: params.offset || this.paging.offset || 0,
          limit: params.limit || this.paging.limit || 10,
        })
        paging.page = (paging.offset / paging.limit) + 1;

        this.multiClassList = data;
        this.paging = paging;
      } finally {
        this.getListLoading = false;
      }
    },
    searchMultiClass (keywords) {
      this.getMultiClassList({ keywords })
    },
    deleteMultiClass (multiClass) {
      this.$confirm({
        content: '确认要删除该班课？',
        okType: 'danger',
        maskClosable: true,
        onOk: async () => {
          const { success } = await MultiClass.delete({ id: multiClass.id })

          if (success) {
            this.$message.success('删除成功')
            this.getMultiClassList()
          }
        },
      });
    },

    goToMultiClassManage(id) {
      this.$router.push({
        name: 'MultiClassCourseManage',
        params: { id }
      })
    }
  }
}
</script>
