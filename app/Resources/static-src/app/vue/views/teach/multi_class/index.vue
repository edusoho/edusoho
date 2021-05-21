<template>
  <a-spin :spinning="getListLoading">
     <div class="clearfix mb6">
      <a-input-search placeholder="请输入课程或老师关键字搜索" style="width: 224px" @search="searchMultiClass" />
      <a-button class="pull-right" type="primary">新建班课</a-button>
     </div>

    <a-table :columns="columns" :data-source="data" :locale="locale">
      <a slot="name" slot-scope="text" >
        {{ text }}
      </a>
      <a slot="name2" slot-scope="text">
        {{ text }}
      </a>
      <a slot="lessons" slot-scope="text">
        {{ text }}
      </a>
      <a slot="num1" slot-scope="text">
        {{ text }}
      </a>
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
  </a-spin>
</template>


<script>
import { MultiClass } from 'common/vue/service/index.js';

const columns = [
  {
    title: '班课名称',
    dataIndex: 'name',
    scopedSlots: { customRender: 'name' },
  },
  {
    title: '课程名称',
    dataIndex: 'name2',
    scopedSlots: { customRender: 'name2' },
  },
  {
    title: '所属产品',
    dataIndex: 'productInfo',
    filters: [
      { text: 'Male', value: 'male' },
      { text: 'Female', value: 'female' },
    ],
  },
  {
    title: '价格',
    dataIndex: 'price',
    sorter: true,
  },
  {
    title: '已完成/课时',
    dataIndex: 'lessons',
    scopedSlots: { customRender: 'lessons' },
  },
  {
    title: '授课老师',
    dataIndex: 'teacher1',
  },
  {
    title: '助教老师',
    dataIndex: 'teacher2',
  },
  {
    title: '已报班人数',
    dataIndex: 'num1',
    sorter: true,
    scopedSlots: { customRender: 'num1' },
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
      data,
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
    async getMultiClassList (params = {}) {
      this.getListLoading = true;
      try {
        const { data, paging } = await MultiClass.search({
          keywords: params.keywords || this.keywords,
          offset: params.offset || this.paging.offset || 0,
          limit: params.limit || this.paging.limit || 10,
        })
        paging.page = (paging.offset / paging.limit) + 1;
        
        this.productList = data;
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
        title: '删除班课',
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
