<template>
  <div class="class-info">
    <div class="clearfix" style="margin-bottom: 16px;">
      <a-input-search class="pull-left" placeholder="请输入课时关键字搜索" style="width: 260px" @search="onSearch" />
      <a-button class="pull-right" type="primary" @click="goToEditorLesson">
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

      <template slot="time" slot-scope="time, record">
        <template v-if="'video' === record.tasks.type">
          {{ (record.tasks.length / 60).toFixed(2) }}min
        </template>
        <template v-else-if="'live' === record.tasks.type">{{ record.tasks.length }}min</template>
        <template v-else>--</template>
      </template>

      <template slot="teacher" slot-scope="teacher">{{ teacher.nickname }}</template>

      <assistant slot="assistant" slot-scope="assistant" :assistant="assistant" />

      <a slot="questionNum"
        slot-scope="questionNum, record"
        :href="`/my/course/${record.tasks.courseId}/question?type=question`"
        target="_blank">{{ questionNum }}</a>

      <template slot="studyStudentNum" slot-scope="studyStudentNum, record">
        {{ studyStudentNum }}/{{ record.totalStudentNum }}
      </template>

      <template slot="actions" slot-scope="actions, record">
        <a-dropdown :trigger="['hover']" placement="bottomRight" style="margin-right: 12px;">
          <a-button type="link" @click="e => e.preventDefault()">
            <a-icon type="copy" />
          </a-button>
          <a-menu slot="overlay" @click="({ key }) => handleMenuClick(key, record)">
            <a-menu-item key="copy" >
              复制课程链接
            </a-menu-item>
          </a-menu>
        </a-dropdown>

        <a-button
          type="link"
          data-toggle="modal"
          data-target="#modal"
          :data-url="`/course/${record.courseId}/task/${record.tasks.id}/update`">编辑</a-button>

        <a-dropdown :trigger="['hover']" placement="bottomRight">
          <a class="ant-dropdown-link" style="margin-left: -6px;" @click="e => e.preventDefault()">
            <a-icon type="caret-down" />
          </a>
          <a-menu slot="overlay" @click="({ key }) => handleMenuClick(key, record)">
            <a-menu-item v-if="record.tasks.status == 'published'" key="unpublish" >
              取消发布
            </a-menu-item>
            <template v-else>
              <a-menu-item key="publish">
                立即发布
              </a-menu-item>
              <a-menu-item key="delete">
                <span style="color: #fe4040; cursor: pointer;">删除</span>
              </a-menu-item>
            </template>
          </a-menu>
        </a-dropdown>
      </template>
    </a-table>
  </div>
</template>

<script>
import _ from 'lodash';
import { MultiClass, Course } from 'common/vue/service';

import ClassName from '../components/ClassName.vue';
import TeachMode from '../components/TeachMode.vue';
import Assistant from '../components/Assistant.vue';

const columns = [
  {
    title: '课时名称',
    dataIndex: 'name',
    width: '15%',
    scopedSlots: { customRender: 'name' }
  },
  {
    title: '任务类型',
    dataIndex: 'mode',
    filters: [
      { text: '视频', value: 'video' },
      { text: '音频', value: 'audio' },
      { text: '直播', value: 'live' },
      { text: '讨论', value: 'discuss' },
      { text: '文档', value: 'doc' },
      { text: 'PPT', value: 'ppt' },
      { text: '考试', value: 'testpaper' },
      { text: '作业', value: 'homework' },
      { text: '练习', value: 'exercise' },
      { text: '下载资料', value: 'download' },
      { text: '图文', value: 'text' }
    ],
    width: '12%',
    scopedSlots: { customRender: 'mode' }
  },
  {
    title: '开课时间',
    dataIndex: 'startTime',
    width: '13%',
    scopedSlots: { customRender: 'startTime' }
  },
  {
    title: '时长',
    width: '10%',
    dataIndex: 'time',
    scopedSlots: { customRender: 'time' }
  },
  {
    title: '授课老师',
    width: '10%',
    ellipsis: true,
    dataIndex: 'teacher',
    scopedSlots: { customRender: 'teacher' }
  },
  {
    title: '助教老师',
    width: '10%',
    ellipsis: true,
    dataIndex: 'assistant',
    scopedSlots: { customRender: 'assistant' }
  },
  {
    title: '问题',
    width: '10%',
    dataIndex: 'questionNum',
    scopedSlots: { customRender: 'questionNum' }
  },
  {
    title: '学习人数',
    width: '10%',
    dataIndex: 'studyStudentNum',
    scopedSlots: { customRender: 'studyStudentNum' }
  },
  {
    title: '操作',
    width: '128px',
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
        filterReset: '重置'
      },
      multiClassId: this.$route.params.id,
      keywords: '',
      courseId: 0,
      courseSetId: 0,
    }
  },

  mounted() {
    this.fetchLessons();
    this.fetchMultiClass();

    $('#modal').on('hide.bs.modal', () => {
      const params = {
        limit: 10,
        offset: (this.pagination.current - 1) * 10
      };
      this.fetchLessons(params);
    })
  },

  destroyed() {
    $('#modal').off('hide.bs.modal');
  },

  filters: {
    timeTransfer(totalSecond) {
      const minute = _.floor(totalSecond / 60)
      const second = totalSecond % 60
      let time = `${minute}min `

      if (second) {
        time += `${second}s`
      }

      return time
    }
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

    fetchMultiClass() {
      MultiClass.get(this.multiClassId).then(res => {
        const { course: { id, courseSetId } } = res;
        this.courseId = id;
        this.courseSetId = courseSetId;
      });
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
    handleMenuClick(key, record) {
      if (key === 'copy') {
        this.copy(record);
        return;
      }

      if (['publish', 'unpublish'].includes(key)) {
        this.updateTaskStatus(key, record);
        return;
      }

      if (key === 'delete') {
        this.deleteTask(record);
      }
    },

    copy(record) {
      this.$clipboard(this.copyTaskUrl(record));
      this.$message.success('复制成功');
    },

    updateTaskStatus(type, value) {
      const { tasks: { courseId, id } } = value;
      const message = type == 'publish' ? `发布成功` : `取消发布成功`;
      Course.updateTaskStatus(courseId, id, { type }).then(() => {
        this.$message.success(message);
        _.forEach(this.data, item => {
          if (item.tasks.id == id) {
            item.tasks.status = type === 'publish' ? 'published' : 'create';
          }
        });
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
              _.forEach(this.data, (item, index) => {
                if (item.tasks.id == id) {
                  this.data.splice(index, 1);
                }
              });
            }
          });
        }
      });
    },

    copyTaskUrl(record) {
      let url = `${window.location.origin}/course/${record.courseId}`

      return url;
    },

    goToEditorLesson() {
      this.$router.push({
        name: 'MultiClassEditorLesson',
        params: {
          id: this.courseId
        },
        query: {
          courseSetId: this.courseSetId,
          multiClassId: this.multiClassId
        }
      });
    }
  }
}
</script>

<style lang="less">
.es-transition(@property:all,@time:.3s) {
  -webkit-transition: @property @time ease;
     -moz-transition: @property @time ease;
       -o-transition: @property @time ease;
          transition: @property @time ease;
}

.es-transition {
  .es-transition()
}

.border-radius(@radius) {
  border-radius: @radius;
}

@import "~app/less/admin-v2/variables.less";
@import "~app/less/page/course-manage/task/create.less";
@import "~app/less/component/es-step.less";

</style>
