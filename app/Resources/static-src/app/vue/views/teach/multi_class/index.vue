<template>
   <aside-layout :breadcrumbs="[{ name: '班课管理' }]" :headerTip="headerTip" :headerTitle="headerTitle">
    <a-spin :spinning="getListLoading">
      <div class="clearfix cd-mb16">
        <a-select
            v-model="search.productScreen"
            show-search
            placeholder="所属产品筛选"
            option-filter-prop="children"
            style="width: 150px"
            :filter-option="filterOption"
            @change="handleChange"
        >
          <a-select-option v-for="status in classStatusList" :key="status.status">
              {{ status.name }}
          </a-select-option>
        </a-select>

        <a-select 
            v-model="search.classStatus"
            placeholder="班课状态"
            style="width: 120px"
            @change="handleChange">
          <a-select-option v-for="status in classStatusList" :key="status.status">
              {{ status.name }}
          </a-select-option>
        </a-select>

        <a-select
            v-model="search.teacher"
            show-search
            placeholder="授课老师"
            option-filter-prop="children"
            style="width: 150px"
            :filter-option="filterOption"
            @change="handleChange"
        >
          <a-select-option v-for="status in classStatusList" :key="status.status">
              {{ status.name }}
          </a-select-option>
        </a-select>

        <a-select 
            v-model="search.classType"
            placeholder="班课类型"
            style="width: 120px"
            @change="handleChange">
          <a-select-option v-for="type in classTypeList" :key="type.status">
              {{ type.name }}
          </a-select-option>
        </a-select>
        
        <a-input 
          v-model="search.keywords" 
          placeholder="请输入班课名称" 
          style="width: 224px"
          :allowClear="true" />
          <a-button type="primary" @click="searchMultiClass">检索</a-button>
        <a-dropdown v-if="isPermission('multi_class_create')">
          <a-button class="pull-right" type="primary">新建班课</a-button>
          <a-menu slot="overlay">
            <a-menu-item>
              <a @click="goToCreateMultiClassPage" href="javascript:;">大班课</a>
            </a-menu-item>
            <a-menu-item>
              <a href="javascript:;">分组大班课</a>
            </a-menu-item>
          </a-menu>
        </a-dropdown>
      </div>

      <a-table :columns="columns"
        :pagination="paging"
        :data-source="multiClassList"
        @change="change"
        :rowKey="record => record.id">
        <a
          slot="class_title"
          slot-scope="text, record"
          href="javascript:;"
          :title="text"
          @click="goToMultiClassManage(record.id)"
        >
          {{ text }}
        </a>
        <a slot="taskNum" slot-scope="text, record"
          href="javascript:;"
          @click="goToMultiClassManage(record.id)">
          {{ record.endTaskNum }}/{{ record.taskNum }}
        </a>
        <a slot="course" slot-scope="course"
          :href="`/course/${course.id}`" target="_blank" :title="course.courseSetTitle">
          {{ course.courseSetTitle }}
        </a>
        <assistant slot="assistant" slot-scope="assistant" :assistant="assistant" />
        <a slot="studentNum" slot-scope="text, record"
          href="javascript:;"
          @click="$router.push({ name: 'MultiClassStudentManage', params: { id: record.id } })">
          {{ text }}
        </a>
        <template slot="createdTime" slot-scope="createdTime">
          {{ $dateFormat(createdTime, 'YYYY-MM-DD HH:mm') }}
        </template>
        <template slot="action" slot-scope="text, record">
          <a-button type="link"
            @click="goToMultiClassManage(record.id)">查看</a-button>
          <a-button v-if="isPermission('multi_class_edit')"
            type="link"
            @click="goToEditorMultiClass(record.id)">编辑</a-button>
          <a-dropdown v-if="isPermission('multi_class_copy') || isPermission('multi_class_delete')">
            <a class="ant-dropdown-link" style="margin-left: -6px;" @click="e => e.preventDefault()">
              <a-icon type="caret-down" />
            </a>
            <a-menu slot="overlay">
              <a-menu-item>
                <a v-if="isPermission('multi_class_copy')" href="javascript:;" @click="copyMultiClass(record)">复制班课</a>
              </a-menu-item>
              <a-menu-item>
                <a v-if="isPermission('multi_class_delete')" href="javascript:;" class="color-danger" @click="deleteMultiClass(record)">删除</a>
              </a-menu-item>
            </a-menu>
          </a-dropdown>
          <a-button v-if="isPermission('course_statistics_view')"
            type="link"
            @click="$router.push({ name: 'MultiClassDataPreview', params: { id: record.id}})">数据概览</a-button>
        </template>
      </a-table>
    </a-spin>

    <copy-multi-class-modal
      :id="copyMultiClassId"
      :visible="copyModalVisible"
      @event-communication="eventCommunication"
    />
   </aside-layout>
</template>

<script>
import AsideLayout from 'app/vue/views/layouts/aside.vue';
import { MultiClass, MultiClassProduct } from 'common/vue/service/index.js';
import Assistant from './course_manage/components/Assistant.vue';
import CopyMultiClassModal from './CopyMultiClassModal.vue';

const columns = [
  {
    title: '班课名称',
    dataIndex: 'title',
    width: '15%',
    ellipsis: true,
    scopedSlots: { customRender: 'class_title' },
  },
  {
    title: '课程名称',
    dataIndex: 'course',
    width: '15%',
    ellipsis: true,
    scopedSlots: { customRender: 'course' },
  },
  {
    title: '班课状态',
    dataIndex: 'status',
    width: '10%',
    ellipsis: true,
  },
  {
    title: '所属产品',
    dataIndex: 'product',
    key: 'productIds',
    width: '10%',
    ellipsis: true,
    filters: [],
  },
  {
    title: '价格',
    dataIndex: 'price',
    width: '100px',
    sorter: true,
  },
  {
    title: '已完成/课时',
    dataIndex: 'taskNum',
    width: '10%',
    scopedSlots: { customRender: 'taskNum' },
  },
  {
    title: '授课老师',
    dataIndex: 'teacher',
    width: '8%',
    ellipsis: true
  },
  {
    title: '助教老师',
    dataIndex: 'assistant',
    width: '160px',
    ellipsis: true,
    scopedSlots: { customRender: 'assistant' },
  },
  {
    title: '已报班人数',
    dataIndex: 'studentNum',
    width: '108px',
    ellipsis: true,
    sorter: true,
    scopedSlots: { customRender: 'studentNum' },
  },
  {
    title: '创建时间',
    dataIndex: 'createdTime',
    width: '160px',
    sorter: true,
    scopedSlots: { customRender: 'createdTime' },
  },
  {
    title: '操作',
    dataIndex: 'action',
    width: '200px',
    scopedSlots: { customRender: 'action' },
  },
];
const classStatusList = [
      { status: "", name: "课程状态" },
      { status: "0", name: "开课中" },
      { status: "1", name: "未开课" },
      { status: "2", name: "已结课" },
];
const classTypeList = [
      { status: "", name: "班课类型" },
      { status: "1", name: "大班课" },
      { status: "2", name: "分组大班课" },
];
export default {
  name: 'MultiClassList',

  components: {
    AsideLayout,
    Assistant,
    CopyMultiClassModal
  },

  data () {
    return {
      columns,
      search:{
        productScreen: "",
        classStatus: "",
        teacher: "",
        classType: "",
        keywords: ""
      },
      classStatusList,
      classTypeList,
      multiClassList: [],
      productList: [],
      getListLoading: false,
      paging: {
        total: 0,
        offset: 0,
        pageSize: 10,
      },
      copyModalVisible: false,
      copyMultiClassId: 0,
      headerTitle: '什么是班课？',
      headerTip: '班课是以班级形式按照特定的时间安排所进行的课程'
    }
  },
  created() {
    let paging = this.$route.params.paging;
    if (paging) {
      this.paging = paging;
    }
    this.getMultiClassList(this.paging)
    this.getMultiClassProductList()
  },
  methods: {
    goToCreateMultiClassPage() {
      this.$router.push({
        name: 'MultiClassCreate'
      })
    },
    async getMultiClassProductList() {
      const { data } = await MultiClassProduct.search({
        keywords: '',
        offset: 0,
        limit: 100000,
      })

      const index = _.findIndex(this.columns, item => item.dataIndex === 'product');
      const productItem = this.columns[index];

      productItem.filters = []
      _.forEach(data, item => {
        productItem.filters.push({
          text: item.title,
          value: item.id
        })
      })

      this.$set(this.columns, index, productItem)
    },
    async getMultiClassList (params = {}) {
      params.limit = params.pageSize || 10
      params.offset = params.offset || 0
      params.keywords = params.keywords || ''
     
      this.getListLoading = true;
      try {
        const { data, paging } = await MultiClass.search(params)

        paging.page = (paging.offset / paging.limit) + 1;
        paging.pageSize = Number(paging.limit);
        paging.current = params.current || 1;

        this.multiClassList = data;
        this.paging = paging;
      } finally {
        this.getListLoading = false;
      }
    },
    searchMultiClass () {
      this.getMultiClassList(this.search)
    },
    deleteMultiClass (multiClass) {
      this.$confirm({
        content: '确认要删除该班课？',
        okType: 'danger',
        maskClosable: true,
        icon:  'close-circle',
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
    },

    change(pagination, filters, sorter) {
      const params = {}

      if (pagination) {
        params.offset = pagination.pageSize * (pagination.current - 1)
        params.pageSize = pagination.pageSize,
        params.current = pagination.current
      }

      if (filters && Object.keys(filters).length > 0) {
        _.forEach(Object.keys(filters), key => {
          params[key] = filters[key]
        })
      }

      if (sorter && sorter.order) {
        params[`${sorter.field}Sort`] = sorter.order === 'ascend' ? 'ASC' : 'DESC'
      }

      if (Object.keys(params).length > 0) {
        this.getMultiClassList(params)
      }
    },

    copyMultiClass(multiClass) {
      this.copyModalVisible = true;
      this.copyMultiClassId = multiClass.id;
    },

    eventCommunication(params) {
      const { event } = params;

      if (event === 'cancel-modal') {
        this.copyModalVisible = false;
      }
    },

    goToEditorMultiClass(id) {
      this.$router.push({
        name: 'MultiClassCreate',
        query: {
          id
        },
        params: {
          paging: this.paging
        }
      });
    },
    handleChange() {
      console.log(this.search);
    },
    filterOption(input, option) {
      return (
        option.componentOptions.children[0].text.toLowerCase().indexOf(input.toLowerCase()) >= 0
      );
    },
  }
}
</script>

