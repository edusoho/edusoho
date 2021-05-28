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
      :locale="locale"
      @change="handleTableChange"
    >
      <class-name slot="name" slot-scope="name, record" :record="record" />

      <teach-mode slot="mode" slot-scope="mode, record" :record="record" />

      <template slot="startTime" slot-scope="startTime, record">
        <template v-if="record.tasks.type === 'live'">
          {{ $dateFormat(record.tasks.startTime, 'YYYY-MM-DD HH:mm') }}
        </template>
        <template v-else> -- </template>
      </template>
      <!-- TODO -->
      <template slot="time" slot-scope="time, record">
        <template v-if="record.tasks.type === 'video'">
          {{ record.tasks.length }}
        </template>
        <template v-else>--</template>
      </template>

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
            <a-menu-item key="copy" 
              v-clipboard="123"
              v-clipboard:success="() => $message.success('复制成功')">
              复制课程链接
            </a-menu-item>
          </a-menu>
        </a-dropdown>

        <a class="ant-dropdown-link" @click="e => e.preventDefault()">编辑</a>

        <a-dropdown :trigger="['hover']" placement="bottomRight">
          <a class="ant-dropdown-link" @click="e => e.preventDefault()">
            <a-icon type="caret-down" />
          </a>
          <a-menu slot="overlay" @click="({ key }) => handleMenuClick(key, record)">
            <a-menu-item v-if="record.status == 'published'" key="unpublish" >
              取消发布
            </a-menu-item>
            <template v-else>
              <a-menu-item key="publish">
                立即发布
              </a-menu-item>
              <a-menu-item key="delete">
                删除
              </a-menu-item>
            </template>
          </a-menu>
        </a-dropdown>
      </template>
    </a-table>
  </div>
</template>

<script>
import _ from '@codeages/utils';
import { MultiClass, Course } from 'common/vue/service';

import ClassName from './ClassName.vue';
import TeachMode from './TeachMode.vue';
import Assistant from './Assistant.vue';

const columns = [
  {
    title: '课时名称',
    dataIndex: 'name',
    scopedSlots: { customRender: 'name' }
  },
  {
    title: '教学模式',
    dataIndex: 'mode',
    filters: [
      { text: '图文', value: 'text' },
      { text: '视频', value: 'video' },
      { text: '直播', value: 'live' },
      { text: '考试', value: 'testpaper' },
      { text: '作业', value: 'homework' }
    ],
    scopedSlots: { customRender: 'mode' }
  },
  {
    title: '开课时间',
    dataIndex: 'startTime',
    sorter: true,
    scopedSlots: { customRender: 'startTime' }
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
    dataIndex: 'questionNum'
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
      data: [],
      pagination: {},
      loading: false,
      columns,
      locale: {
        filterConfirm: '确定',
        filterReset: '重置',
        emptyText: '暂无数据'
      },
      multiClassId: this.$route.params.id,
      keywords: ''
    }
  },

  mounted() {
    this.fetchLessons();
  },

  methods: {
    handleTableChange(pagination, filters, sorter) {
      const order = sorter.order;

      const pager = { ...this.pagination };
      pager.current = pagination.current;
      this.pagination = pager;

      const params = {
        limit: pagination.pageSize,
        offset: (pagination.current - 1) * pagination.pageSize
      };

      if (_.size(filters)) {
        params.types = filters.mode;
      }

      if (order) {
        params.sort = order == 'ascend' ? 'ASC' : 'DESC';
      }

      this.fetchLessons(params);
    },

    fetchLessons(params = {}) {
      this.loading = true;
      MultiClass.getLessons(this.multiClassId, { limit: 10, titleLike: this.keywords, ...params }).then(res => {
        const pagination = { ...this.pagination };
        pagination.total = res.paging.total;
        this.loading = false;
        this.data = res.data;
        this.pagination = pagination;
      });
    },

    onSearch(value) {
      this.keywords = value;
      this.pagination.current = 1;
      this.fetchLessons();
    },

    // actions: 复制, 发布, 取消发布, 删除
    handleMenuClick(key, value) {
      if (key === 'copy') {
        this.copy(value);
        return;
      }

      if (['publish', 'unpublish'].includes(key)) {
        this.updateTaskStatus(key, value);
        return;
      }

      if (key === 'delete') {
        this.deleteTask(value);
      }
    },

    copy(link) {
      console.log(link);
    },

    updateTaskStatus(type, value) {
      const { tasks: { courseId, id } } = value;
      const message = type == 'publish' ? `发布成功` : `取消发布成功`;
      Course.updateTaskStatus(courseId, id, { type }).then(() => {
        this.$message.success(message);
        this.fetchLessons()
      });
    },

    deleteTask(value) {
      const { tasks: { courseId, id } } = value;
      this.$confirm({
        title: '删除',
        content: '是否确定删除该课时吗?',
        okType: 'danger',
        onOk: () => {
          Course.deleteTask(courseId, id).then(res => {
            if (res.success) {
              this.$message.success('删除成功');
              this.fetchLessons()
            }
          });
        }
      });
    }
  }
}
</script>

