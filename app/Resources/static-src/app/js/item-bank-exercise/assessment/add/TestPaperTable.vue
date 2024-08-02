<script>
import {Testpaper} from 'common/vue/service';
import TestpaperTypeTag from '../../../question-bank/testpaper/TestPaperTypeTag';
import AddTestPaperDrawer from './AddTestPaperDrawer.vue';

const columns = [
  {
    title: Translator.trans('question.bank.paper.name'),
    dataIndex: 'name',
    scopedSlots: {customRender: 'name'},
    ellipsis: true,
    width: 340,
  },
  {
    title: Translator.trans('question.bank.paper.type'),
    dataIndex: 'type',
    scopedSlots: {customRender: 'type'},
    width: 120,
  },
  {
    title: Translator.trans('question.bank.paper.num'),
    dataIndex: 'num',
    scopedSlots: {customRender: 'num'},
    width: 90,
    align: 'right'
  },
  {
    title: Translator.trans('question.bank.paper.numberOfItems/score'),
    scopedSlots: {customRender: 'numberOfItemsAndScore'},
    width: 150,
    align: 'center'
  },
  {
    title: Translator.trans('question.bank.paper.operation'),
    scopedSlots: {customRender: 'operation'},
    width: 150,
  },
];

export default {
  components: {TestpaperTypeTag, AddTestPaperDrawer},
  props: {
    itemBankId: null,
    exerciseId: null,
    moduleId: null,
  },
  data() {
    return{
      columns,
      pageData: [],
      loading: false,
      pagination: {
        current: 1,
        total: 0,
        pageSize: 10,
      },
      selectedRowKeys: [],
      pageSizeOptions: ['10', '20', '30', '40', '50'],
    }
  },
  computed: {
    rowSelection() {
      const {selectedRowKeys} = this;
      return {
        selectedRowKeys,
        onChange: this.onSelectChange,
        hideDefaultSelections: true,
      };
    },
    isSelectAll() {
      const currentPageIds = this.pageData.map(data => data.id);
      for (const id of currentPageIds) {
        if (!this.selectedRowKeys.includes(id)) {
          return false;
        }
      }
      return true;
    },
    isIndeterminate() {
      const currentPageIds = this.pageData.map(data => data.id);
      for (const id of currentPageIds) {
        if (this.selectedRowKeys.includes(id)) {
          return true;
        }
      }
      return false;
    }
  },
  methods: {
    preview(record) {
      window.location.href = `${window.location.origin}/question_bank/${this.itemBankId}/testpaper/${record.assessmentId}/preview`
    },
    onSelectChange(selectedRowKeys) {
      this.selectedRowKeys = selectedRowKeys;
    },
    handleSelectAllChange(e) {
      const currentPageDataIds = this.pageData.map(data => data.id);
      if (e.target.checked) {
        this.selectedRowKeys = this.selectedRowKeys.filter(key => !currentPageDataIds.includes(key)).concat(currentPageDataIds);
      } else {
        this.selectedRowKeys = this.selectedRowKeys.filter(key => !currentPageDataIds.includes(key));
      }
    },
    async fetchTestPaper(params) {
      this.loading = true;
      const {data, paging} = await Testpaper.searchExercise({
        limit: 20,
        exerciseId: this.exerciseId,
        moduleId: this.moduleId,
        ...params
      });

      const pagination = {...this.pagination};
      pagination.total = paging.total;
      pagination.pageSize = Number(paging.limit);

      this.loading = false;
      this.pageData = data.map(a => Object.assign(a.assessment, {id: a.id, assessmentId: a.assessment.id}));
      this.pagination = pagination;
    },
    getTableTotal(total) {
      return Translator.trans('question.bank.paper.pageTotal', {total});
    },
    async handlePaginationChange(page, pageSize) {
      const pager = {...this.pagination};
      pager.current = page;
      pager.pageSize = pageSize;
      this.pagination = pager;
      await this.handleTableChange(this.pagination);
    },
    async batchDelete() {
      this.$confirm({
        title: `确定要删除${this.selectedRowKeys.length}份试卷吗？`,
        icon: "exclamation-circle",
        okText: "删除",
        cancelText: Translator.trans("site.cancel"),
        onOk: async () => {
          await Testpaper.deleteExercise({exerciseId: this.exerciseId, ids: this.selectedRowKeys});
          this.$message.success('删除成功');
          const params = {
            limit: this.pagination.pageSize,
            offset: (this.pagination.current - 1) * this.pagination.pageSize
          };
          await this.fetchTestPaper(params);
          this.selectedRowKeys = [];
        }
      });
    },
    handleDelete(paper) {
      const refresh = this.fetchTestPaper;
      this.$confirm({
        title: '确定要移除该试卷吗？',
        icon: 'exclamation-circle',
        okText: '删除',
        cancelText: '取消',
        centered: true,
        onOk: async () => {
          try {
            await Testpaper.deleteExercise({exerciseId: this.exerciseId, ids: [paper.id]});
            this.$message.success('移除成功');
            const params = {
              limit: this.pagination.pageSize,
              offset: (this.pagination.current - 1) * this.pagination.pageSize
            };
            this.selectedRowKeys = this.selectedRowKeys.filter(key => key !== paper.id);
            await refresh(params);
          } catch (err) {
            this.$message.success('移除失败', err);
          }
        },
      });
    },
    async handleAddTestPaper() {
      const pager = {...this.pagination};
      pager.current = 1;
      this.pagination = pager;
      await this.fetchTestPaper(this.pagination);
    }
  },
  async created() {
    await this.fetchTestPaper(this.pagination);
  },
}
</script>

<template>
  <div>
    <a-table
      :columns="columns"
      :data-source="pageData"
      :row-key="record => record.id"
      :pagination="false"
      :loading="loading"
      :row-selection="rowSelection"
    >
      <template slot="name" slot-scope="name">
        <a-tooltip :title="name">
          <span>{{ name }}</span>
        </a-tooltip>
      </template>
      <template slot="type" slot-scope="type">
        <testpaper-type-tag :type="type"/>
      </template>
      <template slot="num" slot-scope="num">
        <span>{{ num ? num : '-' }}</span>
      </template>
      <template slot="numberOfItemsAndScore" slot-scope="record">
        {{ `${record.question_count} / ${record.total_score}` }}
      </template>
      <template slot="operation" slot-scope="record">
        <div class="operation-group">
          <a-button v-if="record.type !== 'aiPersonality' && ['draft', 'open'].includes(record.status)"
                    type="link"
                    class="operation-group-button-active"
                    @click="preview(record)"
          >
            {{ 'question.bank.paper.preview'|trans }}
          </a-button>
          <a-button v-if="record.type !== 'aiPersonality' && ['generating', 'fail'].includes(record.status)"
                    type="link"
                    :disabled="true"
          >
            {{ 'question.bank.paper.preview'|trans }}
          </a-button>
          <a-button type="link" @click="handleDelete(record)">
            {{ 'decorate.remove'|trans }}
          </a-button>
        </div>
      </template>
    </a-table>
    <div class="list-bottom">
      <div class="selector-operate">
        <a-checkbox :indeterminate="isIndeterminate && !isSelectAll" :checked="selectedRowKeys && selectedRowKeys.length > 0 && isSelectAll"
                    @change="handleSelectAllChange">
          <span class="checkbox-text">{{ 'question.bank.paper.selectAll'|trans }}</span>
        </a-checkbox>
        <span>{{ 'question.bank.paper.selectedItems'|trans({'select': this.selectedRowKeys.length}) }}</span>
        <a-button type="danger" ghost size="small" :disabled="this.selectedRowKeys.length === 0" @click="batchDelete">{{ 'question.bank.paper.batch.delete'|trans }}</a-button>
      </div>
      <a-pagination
        show-quick-jumper
        show-size-changer
        :page-size-options="pageSizeOptions"
        :show-total="total => getTableTotal(total)"
        v-model="pagination.current"
        :total="pagination.total"
        @showSizeChange="handlePaginationChange"
        @change="handlePaginationChange"
      />
    </div>
    <add-test-paper-drawer :item-bank-id="itemBankId" :exercise-id="exerciseId" :module-id="moduleId" @addTestPaper="handleAddTestPaper"/>
  </div>
</template>
