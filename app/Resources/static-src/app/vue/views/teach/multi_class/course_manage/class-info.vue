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

      <assistant slot="assistant" slot-scope="assistant" :assistant="assistant" />

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

const columns = [
  {
    title: '课时名称',
    dataIndex: 'name',
    width: '20%',
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
    width: '10%',
    scopedSlots: { customRender: 'mode' }
  },
  {
    title: '开课时间',
    dataIndex: 'startTime',
    sorter: true,
    width: '10%',
    customRender: (value, row, index) => {
      return {
        // children: row.tasks.activity.startTime
        children: '2002/10/03 10:39'
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
    customRender: (value, row, index) => {
      return {
        children: value.nickname || '- -'
      };
    }
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
    width: '10%',
    customRender: (value, row, index) => {
      const { studyStudentNum, totalStudentNum } = row;
      return {
        children: `${studyStudentNum}/${totalStudentNum}`
      };
    }
  },
  {
    title: '操作',
    dataIndex: 'actions',
    width: '10%',
    scopedSlots: { customRender: 'actions' },
  },
];

export default {
  components: {
    ClassName,
    TeachMode,
    Assistant
  },

  data() {
    return {
      data: [],
      pagination: {},
      loading: false,
      columns
    }
  },

  methods: {
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

