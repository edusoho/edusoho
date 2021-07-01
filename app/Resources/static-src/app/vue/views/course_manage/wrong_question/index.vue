<template>
  <div>
    <a-form-model
      ref="form"
      :model="form"
      layout="inline"
    >
      <a-form-model-item>
        <a-select
          v-model="form.source"
          style="width: 120px;"
          placeholder="题目来源"
        >
          <a-select-option value="zuoye">
            作业任务
          </a-select-option>
          <a-select-option value="zuoye">
            作业任务
          </a-select-option>
          <a-select-option value="kaoshi">
            考试任务
          </a-select-option>
          <a-select-option value="lianxi">
            练习任务
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item>
        <a-select
          v-model="form.taskName"
          style="width: 120px;"
          placeholder="任务名称"
        >
          <a-select-option value="zuoye">
            作业任务
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item>
        <a-select
          v-model="form.sort"
          style="width: 120px;"
          placeholder="答错人次"
        >
          <a-select-option value="DES">
            由高至低
          </a-select-option>
          <a-select-option value="ASC">
            由低至高
          </a-select-option>
        </a-select>
      </a-form-model-item>

      <a-form-model-item>
        <a-button type="primary" @click="onSearch">搜索</a-button>
      </a-form-model-item>
    </a-form-model>

    <wrong-question-table
      class="mt24"
      :data="wrongQuestionList"
      :pagination="pagination"
      :loading="loading"
      @event-communication="eventCommunication"
    />

    <wrong-question-detail-modal
      :visible="visible"
      :wrong-question-id="wrongQuestionId"
      @event-communication="eventCommunication"
    />
  </div>
</template>

<script>
import { WrongBookStudentWrongQuestion } from 'common/vue/service/index.js';
import WrongQuestionTable from 'app/vue/views/components/WrongQuestion/Table.vue';
import WrongQuestionDetailModal from 'app/vue/views/components/WrongQuestion/ViewDetailModal.vue';

export default {
  name: 'CourseManageWrongQuestion',

  components: {
    WrongQuestionTable,
    WrongQuestionDetailModal
  },

  data() {
    return {
      form: {
        source: undefined,
        taskName: undefined,
        sort: undefined
      },
      wrongQuestionList: [],
      pagination: {
        hideOnSinglePage: true,
        current: 1
      },
      loading: false,
      visible: false,
      wrongQuestionId: '0'
    }
  },

  created() {
    this.fetchWrongQuestion();
  },

  methods: {
    onSearch() {
      this.$refs.form.validate(valid => {
        if (valid) {
          // do
        }
      })
    },

    handleTableChange(pagination) {
      this.pagination.current = pagination.current;
    },

    async fetchWrongQuestion() {
      this.loading = true;

      const apiParams = {
        query: {
          targetId: 72,
          targetType: 'course'
        },
        params: {
          offset: (this.pagination.current - 1) * 10,
          limit: 10
        }
      };

      const { data, paging } = await WrongBookStudentWrongQuestion.get(apiParams);

      this.loading = false;
      this.wrongQuestionList = data;
      this.pagination.total = paging.total;
    },

    handleClickViewDetails(data) {
      this.wrongQuestionId = data.itemId;
      this.visible = true;
    },

    eventCommunication(params) {
      const { type, data } = params;

      if (type === 'table-pagination') {
        this.handleTableChange(data);
        return;
      }

      if (type === 'table-click') {
        this.handleClickViewDetails(data);
        return;
      }

      if (type === 'modal-cancel') {
        this.visible = false;
      }
    }
  }
}
</script>
