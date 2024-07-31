<script>
import ListHeader from './components/header.vue';
import TestPaperTypeTag from '../TestPaperTypeTag.vue';
import {Testpaper} from 'common/vue/service';

const columns = [
  {
    title: Translator.trans('question.bank.paper.name'),
    dataIndex: 'name',
    ellipsis: true,
    scopedSlots: {customRender: 'name'},
    width: 270,
  },
  {
    title: Translator.trans('question.bank.paper.paper.type'),
    dataIndex: 'type',
    scopedSlots: {customRender: 'type'},
    width: 120,
  },
  {
    title: Translator.trans('question.bank.paper.num'),
    dataIndex: 'num',
    scopedSlots: {customRender: 'num'},
    width: 90,
    align: 'right',
  },
  {
    title: Translator.trans('question.bank.paper.status'),
    dataIndex: 'status',
    scopedSlots: {customRender: 'status'},
    width: 90,
  },
  {
    title: Translator.trans('question.bank.paper.numberOfItems/score'),
    scopedSlots: {customRender: 'numberOfItemsAndScore'},
    align: 'right',
    width: 130,
  },
  {
    title: Translator.trans('question.bank.paper.updatePerson/updatedAt'),
    scopedSlots: {customRender: 'creatorAndCreatedAt'},
    width: 160,
  },
  {
    title: Translator.trans('question.bank.paper.operation'),
    scopedSlots: {customRender: 'operation'},
  },
];

export default {
  props: {
    itemBankId: null
  },
  components: {
    ListHeader,
    TestPaperTypeTag
  },
  data() {
    return {
      status: undefined,
      type: undefined,
      keywordType: 'name',
      keyword: undefined,
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
      currentTab: 'all',
    };
  },
  methods: {
    async handleTableChange(pagination) {
      const pager = {...this.pagination};
      pager.current = pagination.current;
      this.pagination = pager;

      const params = {
        limit: pagination.pageSize,
        offset: (pagination.current - 1) * pagination.pageSize
      };

      await this.fetchTestPaper(params);
    },

    async fetchTestPaper(params) {
      this.loading = true;
      const searchQuery = Object.assign({
        itemBankId: this.itemBankId,
        status: this.status,
        type: this.type,
        ...params
      }, this.keywordType === 'creator' ? {createdUser: this.keyword} : {nameLike: this.keyword})
      const {data, paging} = await Testpaper.search(searchQuery);

      const pagination = {...this.pagination};
      pagination.total = paging.total;
      pagination.pageSize = Number(paging.limit);

      this.loading = false;
      this.pageData = data;
      this.pagination = pagination;
    },

    async onSearch() {
      this.pagination.current = 1;
      const params = {
        limit: this.pagination.pageSize,
        offset: (this.pagination.current - 1) * this.pagination.pageSize
      };
      await this.fetchTestPaper(params);
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
    async publish(record) {
      if (record.type === 'random') {
        this.$confirm({
          title: '确定要发布该试卷吗？',
          content: '试卷发布后无论是否关闭都将无法修改',
          icon: 'exclamation-circle',
          okText: '发布',
          cancelText: '取消',
          centered: true,
          onOk: async () => {
            await Testpaper.changeStatus(record.id, 'open');
            record.status = 'open';
            this.$message.success(Translator.trans('question.bank.paper.publish.success'));
          },
        });
      } else {
        await Testpaper.changeStatus(record.id, 'open');
        record.status = 'open';
        this.$message.success(Translator.trans('question.bank.paper.publish.success'));
      }
    },
    async close(record) {
      this.$confirm({
        title: '操作确认',
        content: `真的要关闭试卷${record.name}吗？`,
        icon: 'exclamation-circle',
        okText: '关闭',
        cancelText: '取消',
        centered: true,
        onOk: async () => {
          try {
            await Testpaper.changeStatus(record.id, 'closed');
            record.status = 'closed';
            this.$message.success(Translator.trans('question.bank.paper.close.success'));
          } catch (err) {
            this.$message.success('关闭失败', err);
          }
        },
      });
    },
    async preview(record) {
      if (record.type === 'aiPersonality') {
        await this.$router.push({name: 'preview', params: {id: record.id}});
      } else {
        window.location.href = `${window.location.origin}/question_bank/${this.itemBankId}/testpaper/${record.id}/preview`
      }
    },
    async handleChangeTab(tab) {
      this.currentTab = tab;
      this.pagination.current = 1;
      if (tab === 'all') {
        const params = {
          limit: this.pagination.pageSize,
          offset: (this.pagination.current - 1) * this.pagination.pageSize
        };
        this.type = undefined;
        await this.fetchTestPaper(params);
      } else if (tab === 'aiPersonality') {

        const params = {
          limit: this.pagination.pageSize,
          offset: (this.pagination.current - 1) * this.pagination.pageSize
        };
        this.type = 'aiPersonality';

        await this.fetchTestPaper(params);
      }
    },
    handleDelete(paper) {
      const refresh = this.fetchTestPaper;
      this.$confirm({
        title: '确定要删除该试卷吗？',
        icon: 'exclamation-circle',
        okText: '删除',
        cancelText: '取消',
        centered: true,
        onOk: async () => {
          try {
            await Testpaper.delete({ids: [paper.id]});
            this.$message.success('删除成功');
            const params = {
              limit: this.pagination.pageSize,
              offset: (this.pagination.current - 1) * this.pagination.pageSize
            };
            this.selectedRowKeys = this.selectedRowKeys.filter(key => key !== paper.id);
            await refresh(params);
          } catch (err) {
            this.$message.success('删除失败', err);
          }
        },
      });
    },
    async handleRegenerate(paper) {
      await Testpaper.regenerate(paper.id);
      const params = {
        limit: this.pagination.pageSize,
        offset: (this.pagination.current - 1) * this.pagination.pageSize
      };
      await this.fetchTestPaper(params);
    },
    exportPaper(paper) {
      window.open(`/question_bank/${this.itemBankId}/testpaper/${paper.id}/export`, '_blank')
    },
    async handleEdit(paper) {
      if (paper.status === 'draft') {
        if (paper.type === 'regular') {
          window.location.href = `/question_bank/${this.itemBankId}/testpaper/${paper.id}/edit`
        } else {
          await this.$router.push({
            name: 'update', query: {type: paper.type}, params: {id: paper.id}
          });
        }
      } else  {
        this.$confirm({
          title: '确定要进行编辑吗？',
          content: '当前试卷可能已被应用在考试中，修改试卷内容，可能会对学员答题记录、答题成绩等产生影响',
          icon: "exclamation-circle",
          okText: Translator.trans("question.bank.paper.edit"),
          cancelText: Translator.trans("site.cancel"),
          centered: true,
          onOk: async () => {
            if (paper.type === 'regular') {
              window.location.href = `/question_bank/${this.itemBankId}/testpaper/${paper.id}/edit`
            } else {
              await this.$router.push({
                name: 'update', query: {type: paper.type}, params: {id: paper.id}
              });
            }
          }
        });
      }
    },
    async batchDelete() {
      this.$confirm({
        title: `确定要删除${this.selectedRowKeys.length}份试卷吗？`,
        icon: "exclamation-circle",
        okText: "删除",
        cancelText: Translator.trans("site.cancel"),
        onOk: async () => {
          await Testpaper.delete({ids: this.selectedRowKeys});
          this.$message.success('删除成功');
          const params = {
            limit: this.pagination.pageSize,
            offset: (this.pagination.current - 1) * this.pagination.pageSize
          };
          this.selectedRowKeys = [];
          await this.fetchTestPaper(params);
        }
      });
    }
  },
  watch: {
    async status() {
      await this.onSearch();
    },
    async type() {
      await this.onSearch();
    },
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
      if (!this.pageData || this.pageData.length === 0) {
        return false;
      }
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
  async created() {
    const params = {
      limit: this.pagination.pageSize,
      offset: (this.pagination.current - 1) * this.pagination.pageSize
    };
    await this.fetchTestPaper(params);
  }
};
</script>

<template>
  <div class="test-paper-list-container">
    <list-header @changeTab="handleChangeTab"/>
    <div class="condition-bar">
      <a-select v-model="status" :placeholder="'question.bank.paper.status'|trans" style="width: 156px" allow-clear>
        <a-select-option value="draft">
          {{ 'question.bank.paper.draft'|trans }}
        </a-select-option>
        <a-select-option value="open">
          {{ 'question.bank.paper.published'|trans }}
        </a-select-option>
        <a-select-option value="closed">
          {{ 'question.bank.paper.closed'|trans }}
        </a-select-option>
        <a-select-option value="generating">
          {{ 'question.bank.paper.generating'|trans }}
        </a-select-option>
        <a-select-option value="failure">
          {{ 'question.bank.paper.fail'|trans }}
        </a-select-option>
      </a-select>
      <a-select v-if="currentTab === 'all'" v-model="type" :placeholder="'question.bank.paper.type'|trans" style="width: 156px" allow-clear>
        <a-select-option value="regular">
          {{ 'question.bank.paper.regular'|trans }}
        </a-select-option>
        <a-select-option value="random">
          {{ 'question.bank.paper.random'|trans }}
        </a-select-option>
        <a-select-option value="aiPersonality">
          {{ 'question.bank.paper.aiPersonality'|trans }}
        </a-select-option>
      </a-select>
      <a-select v-model="keywordType" style="width: 100px">
        <a-select-option value="name">
          {{ 'question.bank.paper.name'|trans }}
        </a-select-option>
        <a-select-option value="creator">
          {{ 'question.bank.paper.creator'|trans }}
        </a-select-option>
      </a-select>
      <a-input v-model="keyword" :placeholder="'question.bank.paper.typeKeyword'|trans" style="flex: 1 0 0"></a-input>
      <a-button type="primary" @click="onSearch" ghost>{{ 'site.search_hint'|trans }}</a-button>
      <a-button>{{ 'question.bank.reset.btn'|trans }}</a-button>
    </div>
    <a-table
      :columns="columns"
      :data-source="pageData"
      :row-key="record => record.id"
      :pagination="false"
      :loading="loading"
      :row-selection="rowSelection"
      @change="handleTableChange"
    >
      <template slot="name" slot-scope="name">
        <a-tooltip :title="name">
          <span>{{ name }}</span>
        </a-tooltip>
      </template>
      <template slot="type" slot-scope="type">
        <test-paper-type-tag :type="type"/>
      </template>
      <template slot="num" slot-scope="num">
        <span>{{ num ? num : '-' }}</span>
      </template>
      <template slot="status" slot-scope="status">
        <a-badge v-if="status === 'draft'" color="gray" :text="'question.bank.paper.draft'|trans"/>
        <a-badge v-if="status === 'open'" color="green" :text="'question.bank.paper.published'|trans"/>
        <a-badge v-if="status === 'closed'" color="red" :text="'question.bank.paper.closed'|trans"/>
        <a-badge v-if="status === 'failure'" color="red" :text="'question.bank.paper.fail'|trans"/>
        <a-badge v-if="status === 'generating'" color="orange" :text="'question.bank.paper.generating'|trans"/>
      </template>
      <template slot="numberOfItemsAndScore" slot-scope="record">
        {{ `${record.question_count} / ${record.total_score}` }}
      </template>
      <template slot="creatorAndCreatedAt" slot-scope="record">
        <div>
          <span>{{ record.updated_user.nickname }}</span>
          <br/>
          <span class="created_time">{{ $dateFormat(record.updated_time, 'YYYY-MM-DD HH:mm:ss') }}</span>
        </div>
      </template>
      <template slot="operation" slot-scope="record">
        <div class="operation-group">
          <a-button v-if="['draft', 'open'].includes(record.status)"
                    type="link"
                    class="operation-group-button-active"
                    @click="preview(record)"
          >
            {{ 'question.bank.paper.preview'|trans }}
          </a-button>
          <a-button v-if="['generating', 'closed'].includes(record.status)"
                    type="link"
                    :disabled="true"
          >
            {{ 'question.bank.paper.preview'|trans }}
          </a-button>
          <a-button v-if="['open'].includes(record.status)"
                    type="link"
                    class="operation-group-button-active"
                    @click="close(record)"
          >
            {{ 'question.bank.paper.close'|trans }}
          </a-button>
          <a-button v-if="['draft', 'closed'].includes(record.status)"
                    type="link"
                    class="operation-group-button-active"
                    @click="publish(record)">{{ 'question.bank.paper.publish'|trans }}
          </a-button>
          <a-button v-if="['generating'].includes(record.status)"
                    type="link"
                    :disabled="true">
            {{ 'question.bank.paper.publish'|trans }}
          </a-button>
          <a-tooltip v-if="['generating'].includes(record.status) || ['closed', 'open'].includes(record.status) && record.type === 'random'">
            <template slot="title">
              随机试卷仅草稿状态可编辑
            </template>
            <div>
              <a-button type="link"
                        :disabled="true">
                {{ 'question.bank.paper.edit'|trans }}
              </a-button>
            </div>
          </a-tooltip>
          <a-button v-else-if="record.status !== 'failure'"
                    type="link"
                    class="operation-group-button-active"
                    @click="handleEdit(record)"
          >
            {{ 'question.bank.paper.edit'|trans }}
          </a-button>
          <a-button v-if="record.status === 'failure'"
                    type="link"
                    class="operation-group-button-active"
                    @click="handleRegenerate(record)"
          >
            重新生成
          </a-button>
          <a-button v-if="record.status === 'failure'"
                    type="link"
                    class="operation-group-button-active"
                    @click="handleDelete(record)"
          >
            删除
          </a-button>
          <a-dropdown v-if="['closed', 'draft'].includes(record.status) || record.type === 'regular'"
                      :trigger="['click']"
                      placement="bottomRight"
          >
            <a-button type="link"
                      class="operation-group-button-active">...
            </a-button>
            <a-menu slot="overlay">
              <a-menu-item v-if="['closed', 'draft'].includes(record.status)" key="delete" @click="handleDelete(record)">删除</a-menu-item>
              <a-menu-item v-if="record.type === 'regular'" key="export" @click="exportPaper(record)">导出</a-menu-item>
            </a-menu>
          </a-dropdown>
        </div>
      </template>
    </a-table>
    <div class="list-bottom">
      <div class="selector-operate">
        <a-checkbox :indeterminate="isIndeterminate && !isSelectAll" :checked="isSelectAll"
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
  </div>
</template>
