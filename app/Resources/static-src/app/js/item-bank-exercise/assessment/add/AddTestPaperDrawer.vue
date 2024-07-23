<script>
import {Testpaper} from 'common/vue/service';
import TestpaperTypeTag from '../../../question-bank/testpaper/TestPaperTypeTag';

const columns = [
  {
    title: Translator.trans('question.bank.paper.name'),
    dataIndex: 'name',
    ellipsis: true,
    width: 240,
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
    title: Translator.trans('question.bank.paper.status'),
    dataIndex: 'status',
    scopedSlots: {customRender: 'status'},
    width: 90,
  },
  {
    title: Translator.trans('question.bank.paper.numberOfItems/score'),
    scopedSlots: {customRender: 'numberOfItemsAndScore'},
    width: 100,
  },
  {
    title: Translator.trans('question.bank.paper.updatePerson/updatedAt'),
    scopedSlots: {customRender: 'creatorAndCreatedAt'},
    width: 160,
  },
];

export default {
  components: {TestpaperTypeTag},
  props: {
    itemBankId: null,
    exerciseId: null,
    moduleId: null,
  },
  name: 'AddTestPaper',
  data() {
    return {
      drawerVisible: false,
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
    };
  },
  methods: {
    closeDrawer() {
      document.getElementById('add-test-paper-drawer-visible').value = false;
    },
    confirm() {
      this.$confirm({
        title: Translator.trans('item_bank_exercise.abandonOperation'),
        content: Translator.trans('item_bank_exercise.unsaved'),
        okText: Translator.trans('site.confirm'),
        cancelText: Translator.trans('site.cancel'),
        onOk: this.closeDrawer,
      });
    },
    async fetchTestPaper(params) {
      this.loading = true;
      const {data, paging} = await Testpaper.search({
        limit: 20,
        itemBankId: this.itemBankId,
        status: 'open',
        ...params
      });

      const pagination = {...this.pagination};
      pagination.total = paging.total;
      pagination.pageSize = Number(paging.limit);

      this.loading = false;
      this.pageData = data;
      this.pagination = pagination;
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
    async addTestPaper() {
      await Testpaper.addToExercise({exerciseId: this.exerciseId, moduleId: this.moduleId, ids: this.selectedRowKeys});
      this.$message.success('添加成功');
      document.getElementById('add-test-paper-drawer-visible').value = false;
      this.$emit('addTestPaper');
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
  async created() {
    await this.fetchTestPaper(this.pagination);
  },
  mounted() {
    const hiddenInput = document.getElementById('add-test-paper-drawer-visible');
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.attributeName === 'value') {
          this.drawerVisible = hiddenInput.value === 'true';
        }
      });
    });

    observer.observe(hiddenInput, { attributes: true, attributeFilter: ['value'] });
  }
};
</script>

<template>
  <a-drawer
      class="exercise-container-drawer"
      :title="'item_bank_exercise.addTestpaper'|trans"
      :visible="drawerVisible"
      width="960"
      :body-style="{ paddingBottom: '80px' }"
      @close="confirm"
    >
      <div class="condition-bar">
        <a-select v-model="type" :placeholder="'question.bank.paper.type'|trans" style="width: 156px" allow-clear>
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
        <a-button type="primary" @click="fetchTestPaper({type})">{{ 'site.search_hint'|trans }}</a-button>
        <a-button @click="type = undefined; keywordType = 'name'; keyword = undefined;">
          {{ 'question.bank.reset.btn'|trans }}
        </a-button>
      </div>
      <a-table
        :columns="columns"
        :data-source="pageData"
        :row-key="record => record.id"
        :pagination="false"
        :loading="loading"
        :row-selection="rowSelection"
      >
        <template slot="type" slot-scope="type">
          <testpaper-type-tag :type="type"/>
        </template>
        <template slot="num" slot-scope="num">
          <span>{{ num ? num : '-' }}</span>
        </template>
        <template slot="status" slot-scope="status">
          <a-badge v-if="status === 'draft'" color="gray" :text="'question.bank.paper.draft'|trans"/>
          <a-badge v-if="status === 'open'" color="green" :text="'question.bank.paper.published'|trans"/>
          <a-badge v-if="status === 'closed'" color="red" :text="'question.bank.paper.closed'|trans"/>
          <a-badge v-if="status === 'generating'" color="orange" :text="'question.bank.paper.generating'|trans"/>
        </template>
        <template slot="numberOfItemsAndScore" slot-scope="record">
          {{ `${record.question_count} / ${record.total_score}` }}
        </template>
        <template slot="creatorAndCreatedAt" slot-scope="record">
          <div>
            <span>{{ record.created_user.nickname }}</span>
            <br/>
            <span class="created_time">{{ $dateFormat(record.created_time, 'YYYY-MM-DD HH:mm:ss') }}</span>
          </div>
        </template>
      </a-table>
      <div class="drawer-bottom">
        <div class="selector-operate">
          <a-checkbox :indeterminate="isIndeterminate && !isSelectAll" :checked="isSelectAll"
                      @change="handleSelectAllChange">
            <span class="checkbox-text">{{ 'question.bank.paper.selectAll'|trans }}</span>
          </a-checkbox>
          <span>{{ 'question.bank.paper.selectedItems'|trans({'select': this.selectedRowKeys.length}) }}</span>
        </div>
        <div>
          <a-button :style="{ marginRight: '8px' }" @click="confirm">
            {{ 'site.cancel'|trans }}
          </a-button>
          <a-button type="primary" @click="addTestPaper">
            {{ 'site.data.create'|trans }}
          </a-button>
        </div>
      </div>
    </a-drawer>
</template>
