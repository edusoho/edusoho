<template>
  <a-spin :spinning="getListLoading">
     <div class="clearfix mb6">
      <a-input-search placeholder="请输入课程或老师关键字搜索" style="width: 224px" @search="searchMultiClass" />
      <a-button class="pull-right" type="primary" @click="goToCreateMultiClassPage">新建班课</a-button>
     </div>

    <a-table :columns="columns" 
      title=""
      :pagination="false"
      :data-source="multiClassList" 
      :locale="locale">
      <a slot="title" slot-scope="text" >
        {{ text }}
      </a>
      <a slot="course" slot-scope="text">
        {{ text }}
      </a>
      <a slot="product" slot-scope="text">
        {{ text }}
      </a>
      <template slot="taskNum" slot-scope="text, record">
        {{ record.endTaskNum }}/{{ record.taskNum }}
      </template>
      <template slot="assistant" slot-scope="assistant">
        {{ assistant.join('、') }}
      </template>
      <template slot="action" slot-scope="text, record">
        <a-button type="link">查看</a-button>
        <a-dropdown>
          <a @click="e => e.preventDefault()">
            编辑 <a-icon type="down" />
          </a>
          <a-menu slot="overlay">
            <a-menu-item>
              <a href="javascript:;">复制班课</a>
            </a-menu-item>
            <a-menu-item>
              <a href="javascript:;">删除</a>
            </a-menu-item>
          </a-menu>
        </a-dropdown>
        <a-button type="link">数据概览</a-button>
      </template>
    </a-table>

    <div class="text-center">
      <a-pagination class="mt6"
        v-if="paging" 
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
    scopedSlots: { customRender: 'title' },
  },
  {
    title: '课程名称',
    dataIndex: 'course',
    scopedSlots: { customRender: 'course' },
  },
  {
    title: '所属产品',
    dataIndex: 'product',
    filters: [
    ],
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
  },
  {
    title: '创建时间',
    dataIndex: 'createdTime',
    sorter: true,
  },
  {
    title: '操作',
    dataIndex: 'action',
    scopedSlots: { customRender: 'action' },
  },
];
  
const data = [
  {
    name: '11',
    name2: '222',
    price: 100,
    lessons: 100,
    teacher1: '123',
    teacher2: '123',
    num1: '123',
    createdTime: 123214213213
  },
];

export default {
  name: 'MultiClassList',
  data () {
    return {
      columns,
      multiClassList: data,
      getListLoading: false,
      keywords: '',
      paging: {
        offset: 0,
        limit: 10,
      },
      locale: {
        filterConfirm: '确定',
        filterReset: '重置',
        emptyText: '暂无数据',
      }
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
          keywords: params.keywords || this.keywords,
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
        async onOk() {
          const { success } = await MultiClass.delete({ id: multiClass.id })

          if (success) {
            this.getMultiClassList()
          }
        },
      });
    }
  }
}
</script>
