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
      :pagination="pagination"
      @change="handleTableChange"
    >
      <class-name slot="name" slot-scope="name, record" :record="record" />

      <teach-mode slot="mode" slot-scope="mode, record" :record="record" />

      <template slot="createdTime" slot-scope="createdTime">{{ createdTime }}</template>

      <template slot="time" slot-scope="time">60min</template>

      <template slot="teacher" slot-scope="teacher">{{ teacher.nickname }}</template>

      <assistant slot="assistant" slot-scope="assistant" :assistant="assistant" />

      <template slot="studyStudentNum" slot-scope="studyStudentNum, record">
        {{ studyStudentNum }}/{{ record.totalStudentNum }}
      </template>

      <template slot="actions" slot-scope="actions, record">
        <a-dropdown :trigger="['hover']" placement="bottomRight" style="margin-right: 12px;">
          <a class="ant-dropdown-link" @click="e => e.preventDefault()">
            <a-icon type="copy" />
          </a>
          <a-menu slot="overlay" @click="({ key }) => handleMenuClick(key, record.id)">
            <a-menu-item key="copy">
              复制课程链接
            </a-menu-item>
          </a-menu>
        </a-dropdown>

        <a class="ant-dropdown-link" @click="e => e.preventDefault()">编辑</a>

        <a-dropdown :trigger="['click']" placement="bottomRight">
          <a class="ant-dropdown-link" @click="e => e.preventDefault()">
            <a-icon type="caret-down" />
          </a>
          <a-menu slot="overlay" @click="({ key }) => handleMenuClick(key, record.id)">
            <a-menu-item key="publish">
              立即发布
            </a-menu-item>
            <a-menu-item key="unpublish">
              取消发布
            </a-menu-item>
            <a-menu-item key="delete">
              删除
            </a-menu-item>
          </a-menu>
        </a-dropdown>
      </template>
    </a-table>
  </div>
</template>

<script>
import ClassName from './ClassName.vue';
import TeachMode from './TeachMode.vue';
import Assistant from './Assistant.vue';

const data = {
  "id": "26",
  "courseId": "121",
  "type": "lesson",
  "number": 1,
  "seq": "8",
  "title": "223",
  "createdTime": "1621236030",
  "updatedTime": "1621393523",
  "copyId": "0",
  "status": "published",
  "isOptional": "0",
  "published_number": "3",
  "syncId": "0",
  "itemType": "chapter",
  "tasks": {
      "id": "24",
      "courseId": "121",
      "multiClassId": "1",
      "seq": "9",
      "categoryId": "26",
      "activityId": "24",
      "title": "223",
      "isFree": "0",
      "isOptional": "0",
      "startTime": "0",
      "endTime": "0",
      "mode": "lesson",
      "isLesson": "1",
      "status": "published",
      "number": "3-1",
      "type": "text",
      "mediaSource": "",
      "maxOnlineNum": "0",
      "fromCourseSetId": "121",
      "length": "0",
      "copyId": "0",
      "createdUserId": "2",
      "createdTime": "1621236030",
      "updatedTime": "1621393523",
      "syncId": "0",
      "activity": {
          "id": "24",
          "title": "223",
          "remark": null,
          "mediaId": "1",
          "mediaType": "text",
          "length": "0",
          "fromCourseId": "121",
          "fromCourseSetId": "121",
          "fromUserId": "2",
          "copyId": "0",
          "startTime": "0",
          "endTime": "0",
          "createdTime": "1621236030",
          "updatedTime": "0",
          "finishType": "time",
          "finishData": "1",
          "syncId": "0",
          "ext": {
              "id": "1",
              "finishType": "time",
              "finishDetail": "0",
              "createdTime": "1621236030",
              "createdUserId": "2",
              "updatedTime": "1621236030",
              "syncId": "0"
          }
      },
      "courseUrl": "http://es.dev.cn/my/course/121"
  },
  "isExist": 1,
  "chapterTitle": "1",
  "unitTitle": null,
  "teacher": {
      "userId": "2",
      "nickname": "super"
  },
  "assistant": [
      {
          "userId": "225",
          "nickname": "教师1"
      }
  ],
  "questions": 0,
  "studyStudentNum": 0,
  "totalStudentNum": 0
}

const columns = [
  {
    title: '课时名称',
    dataIndex: 'name',
    ellipsis: true,
    scopedSlots: { customRender: 'name' }
  },
  {
    title: '教学模式',
    dataIndex: 'mode',
    filters: [
      { text: '文本', value: 'text' },
      { text: '视频', value: 'video' },
      { text: '直播', value: 'live' }
    ],
    scopedSlots: { customRender: 'mode' }
  },
  {
    title: '开课时间',
    dataIndex: 'createdTime',
    sorter: true,
    scopedSlots: { customRender: 'createdTime' }
  },
  {
    title: '时长',
    dataIndex: 'time',
    scopedSlots: { customRender: 'time' }
  },
  {
    title: '授课老师',
    dataIndex: 'teacher',
    scopedSlots: { customRender: 'teacher' }
  },
  {
    title: '助教老师',
    dataIndex: 'assistant',
    scopedSlots: { customRender: 'assistant' }
  },
  {
    title: '问题讨论',
    dataIndex: 'questions'
  },
  {
    title: '学习人数',
    dataIndex: 'studyStudentNum',
    scopedSlots: { customRender: 'studyStudentNum' }
  },
  {
    title: '操作',
    dataIndex: 'actions',
    scopedSlots: { customRender: 'actions' }
  }
];

export default {
  components: {
    ClassName,
    TeachMode,
    Assistant
  },

  data() {
    return {
      data: [data],
      pagination: {},
      loading: false,
      columns
    }
  },

  methods: {
    fetchLessons() {

    },

    onSearch(value) {
      console.log(value);
    },

    handleTableChange(pagination, filters, sorter) {
      const pager = { ...this.pagination };
      console.log(pager);
      pager.current = pagination.current;
      this.pagination = pager;
      this.fetch({
        results: pagination.pageSize,
        page: pagination.current,
        sortField: sorter.field,
        sortOrder: sorter.order,
        ...filters,
      });
    },

    fetch(params = {}) {
      this.loading = true;
    },

    // actions: 复制, 发布, 取消发布, 删除
    handleMenuClick(key, value) {
      this[key](value);
    },

    copy(link) {
      console.log(link);
    },

    publish(id) {
      console.log(id)
    },

    unpublish(id) {
      console.log(id)
    },

    delete(id) {
      console.log(id)
    }
  }
}
</script>

