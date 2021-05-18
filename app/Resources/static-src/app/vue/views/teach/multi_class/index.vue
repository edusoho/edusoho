<template>
  <div>
     <div class="clearfix cd-mb24">
      <a-input-search placeholder="请输入课程或老师关键字搜索" style="width: 224px" @search="onSearch" />
      <a-button class="pull-right" type="primary">新建班课</a-button>
     </div>

    <a-table :columns="columns" :data-source="data">
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
      <template :size="8" slot="action" slot-scope="text, record">
        <a-button type="link">查看</a-button>
        <a-button type="link">编辑</a-button>
        <a-button type="link">数据概览</a-button>
      </template>
    </a-table>
  </div>
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
    title: '价格',
    dataIndex: 'price',
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
    }
  },
  created() {
    console.log(MultiClass)
  },
  methods: {
    onSearch () {

    }
  }
}
</script>
