<template>
  <a-spin :spinning="getListLoading">
    <a-table :columns="columns" :data-source="data" :locale="locale">
      <a slot="a0" slot-scope="text, record" >
        {{ text }}
      </a>
      <a slot="a1" slot-scope="text, record">
        {{ text }}
      </a>
      <a slot="a2" slot-scope="text, record">
        {{ text }}
      </a>
      <a slot="a3" slot-scope="text, record">
        {{ text }}
      </a>
      <template slot="action" slot-scope="text, record">
        <a-button type="link">答题分布</a-button>
        <a-button type="link">成绩分布</a-button>
      </template>
    </a-table>
  </a-spin>
</template>

<script>
  const columns = [
    {
      title: '课时',
      dataIndex: 'name',
      scopedSlots: { customRender: 'name' },
    },
    {
      title: '作业/考试',
      dataIndex: 'a0',
      scopedSlots: { customRender: 'a0' },
      filters: [
        { text: '作业', value: 'homework' },
        { text: '考试', value: 'test' },
      ],
    },
    {
      title: '题量',
      dataIndex: 'productInfo',
    },
    {
      title: '已批',
      dataIndex: 'a1',
      scopedSlots: { customRender: 'a1' },
    },
    {
      title: '未批',
      dataIndex: 'a2',
      scopedSlots: { customRender: 'a2' },
    },
    {
      title: '未交',
      dataIndex: 'a3',
      scopedSlots: { customRender: 'a3' },
    },
    {
      title: '开始时间',
      dataIndex: 'teacher2',
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
    name: 'HomeWorkReview',
    data () {
      return {
        columns,
        data,
        getListLoading: false,
        homeworksList: [
          {

          }
        ]
      }
    },
  }
</script>