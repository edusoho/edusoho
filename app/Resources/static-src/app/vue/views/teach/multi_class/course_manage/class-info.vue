<template>
  <div class="class-info">
    <div class="clearfix" style="margin-bottom: 24px;">
      <a-input-search class="pull-left" placeholder="请输入课时或老师关键字搜索" style="width: 260px" @search="onSearch" />
      <a-button class="pull-right" type="primary">
        重排课时/新增课时
      </a-button>
    </div>

    <a-table
      :columns="columns"
      :row-key="record => record.id"
      :data-source="data"
      :loading="loading"
      @change="handleTableChange"
    >
      <template slot="teacher" slot-scope="teacher">{{ teacher.nickname }}</template>
      <template slot="assistant" slot-scope="assistant">
        <template v-for="item in assistant">{{ item.nickname }}</template>
      </template>
      <template slot="actions" slot-scope="actions, record">
        <span :id="record.id">复制</span>
        <span>编辑</span>
        <a-icon type="ellipsis" />
      </template>
    </a-table>
  </div>
</template>

<script>
const data = {
    "id":"26",
    "courseId":"121",
    "type":"lesson",
    "number":1,
    "seq":"8",
    "title":"223",
    "createdTime":"1621236030",
    "updatedTime":"1621393523",
    "copyId":"0",
    "status":"published",
    "isOptional":"0",
    "published_number":"3",
    "syncId":"0",
    "itemType":"chapter",
    "tasks":{
        "id":"24",
        "courseId":"121",
        "multiClassId":"1",
        "seq":"9",
        "categoryId":"26",
        "activityId":"24",
        "title":"223",
        "isFree":"0",
        "isOptional":"0",
        "startTime":"0",
        "endTime":"0",
        "mode":"lesson",
        "isLesson":"1",
        "status":"published",
        "number":"3-1",
        "type":"text",
        "mediaSource":"",
        "maxOnlineNum":"0",
        "fromCourseSetId":"121",
        "length":"0",
        "copyId":"0",
        "createdUserId":"2",
        "createdTime":"1621236030",
        "updatedTime":"1621393523",
        "syncId":"0",
        "activity":{
            "id":"24",
            "title":"223",
            "remark":null,
            "mediaId":"1",
            "mediaType":"text",
            "length":"0",
            "fromCourseId":"121",
            "fromCourseSetId":"121",
            "fromUserId":"2",
            "copyId":"0",
            "startTime":"0",
            "endTime":"0",
            "createdTime":"1621236030",
            "updatedTime":"0",
            "finishType":"time",
            "finishData":"1",
            "syncId":"0",
            "ext":{
                "id":"1",
                "finishType":"time",
                "finishDetail":"0",
                "createdTime":"1621236030",
                "createdUserId":"2",
                "updatedTime":"1621236030",
                "syncId":"0"
            }
        },
        "courseUrl":"http://es.dev.cn/my/course/121"
    },
    "isExist":1,
    "chapterTitle":"1",
    "unitTitle":null,
    "teacher":{
        "userId":"2",
        "nickname":"super"
    },
    "assistant":[
        {
            "userId":"225",
            "nickname":"教师1"
        }
    ],
    "questions":0,
    "studyStudentNum":0,
    "totalStudentNum":0
}
const columns = [
  {
    title: '课时名称',
    dataIndex: 'title',
    width: '20%',
    ellipsis: true,
    customRender: (value, row, index) => {
      return {
        children: value + row.type
      };
    }
  },
  {
    title: '教学模式',
    dataIndex: 'type',
    filters: [
      { text: '文本', value: 'text' },
      { text: '视频', value: 'video' },
      { text: '直播', value: 'live' }
    ],
    width: '10%',
    customRender: (value, row, index) => {
      return {
        children: row.tasks.type
      };
    }
  },
  {
    title: '开课时间',
    dataIndex: 'startTime',
    sorter: true,
    width: '10%',
    customRender: (value, row, index) => {
      return {
        children: row.tasks.activity.startTime
      };
    }
  },
  {
    title: '时长',
    dataIndex: 'time',
    width: '10%',
    customRender: (value, row, index) => {
      return {
        children: '60min'
      };
    }
  },
  {
    title: '授课老师',
    dataIndex: 'teacher',
    width: '10%',
    scopedSlots: { customRender: 'teacher' }
  },
  {
    title: '助教老师',
    dataIndex: 'assistant',
    width: '10%',
    scopedSlots: { customRender: 'assistant' }
  },
  {
    title: '问题讨论',
    dataIndex: 'questions',
    width: '10%'
  },
  {
    title: '学习人数',
    dataIndex: 'studyStudentNum',
    width: '10%'
  },
  {
    title: '操作',
    dataIndex: 'actions',
    width: '10%',
    scopedSlots: { customRender: 'actions' },
  },
];

export default {
  data() {
    return {
      data: [data],
      loading: false,
      columns,
    }
  },

  methods: {
    onSearch() {

    },

    handleTableChange() {

    }
  }
}
</script>

<style lang="less">

</style>
