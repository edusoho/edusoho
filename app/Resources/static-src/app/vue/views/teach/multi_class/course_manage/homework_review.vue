<template>
  <div>
    <a-spin :spinning="getListLoading">
      <a-table
        :columns="columns"
        :data-source="homeworksList"
        :pagination="false"
      >
        <a slot="a0" slot-scope="text, record">
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
          <a
            :href="`/course/${record.courseId}/manage/exam/activity/${record.activityId}/analysis`"
            >答题分布</a
          >
          <a>成绩分布</a>
          <a
            href="#modal"
            data-toggle="modal"
            :data-url="`/course/${record.courseId}/activity/${record.activityId}/testpaper/graph`"
          >
            成绩分布</a
          >
        </template>
      </a-table>
    </a-spin>

    <a-modal
      :visible="modalVisible"
      :footer="null"
      title="测试，哈哈"
      :width="920"
      @cancel="modalVisible = false"
    >
      <a-tabs v-model="currentTab">
        <a-tab-pane key="1" tab="全部"></a-tab-pane>
        <a-tab-pane key="2" tab="未批阅"></a-tab-pane>
        <a-tab-pane key="3" tab="进行中"></a-tab-pane>
        <a-tab-pane key="4" tab="已批阅"></a-tab-pane>
      </a-tabs>
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>姓名</th>
            <th>成绩</th>
            <th>提交时间</th>
            <th>状态</th>
            <th>批阅人</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>yelihua</td>
            <td>-</td>
            <td>2021-05-21 09:46:45</td>
            <td>
              <span class="color-primary">已批阅</span>
            </td>
            <td>系统批阅</td>
            <td>
              <a
                class="link-primary"
                href="/homework/result/2753/show?action=check"
                id="show_testpaper_result"
                target="_blank"
                >查看结果</a
              >
            </td>
          </tr>
        </tbody>
      </table>
    </a-modal>
  </div>
</template>

<script>
const columns = [
  {
    title: "课时",
    dataIndex: "name",
    scopedSlots: { customRender: "name" },
  },
  {
    title: "作业/考试",
    dataIndex: "a0",
    scopedSlots: { customRender: "a0" },
    filters: [
      { text: "作业", value: "homework" },
      { text: "考试", value: "test" },
    ],
  },
  {
    title: "题量",
    dataIndex: "productInfo",
  },
  {
    title: "已批",
    dataIndex: "a1",
    scopedSlots: { customRender: "a1" },
  },
  {
    title: "未批",
    dataIndex: "a2",
    scopedSlots: { customRender: "a2" },
  },
  {
    title: "未交",
    dataIndex: "a3",
    scopedSlots: { customRender: "a3" },
  },
  {
    title: "开始时间",
    dataIndex: "teacher2",
  },
  {
    title: "操作",
    dataIndex: "action",
    scopedSlots: { customRender: "action" },
  },
];

export default {
  name: "HomeWorkReview",
  data() {
    return {
      columns,
      getListLoading: false,
      homeworksList: [],
      paging: {
        offset: 0,
        limit: 10,
      },
      modalVisible: true,
      currentTab: '1',
    };
  },
};
</script>