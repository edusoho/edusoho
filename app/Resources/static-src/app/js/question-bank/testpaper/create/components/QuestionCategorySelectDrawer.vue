<template>
  <div>
    <div class="question-category-choose-btn" @click="onDrawerDisplay">
      <span class="question-category-choose-btn-text">+ 选择分类</span>
    </div>
    <a-drawer
      title="选择分类"
      width="960"
      :visible="drawerVisible"
      :maskClosable="false"
      @close="onDrawerClose"
      class="question-category-choose-drawer">
      <div class="question-category-choose-container-body">
        <div class="question-category-choose-all">
          <div class="question-category-choose-all-header">
            <a-input placeholder="搜索分类" @pressEnter="onSearchCategories">
              <img
                slot="prefix"
                class="question-category-choose-all-header-search-icon"
                src="/static-dist/app/img/question-bank/question-category-search-icon.png"
                alt=""
              />
            </a-input>
            <a-checkbox
              :indeterminate="checkAllBoxIndeterminate"
              :checked="allCategoriesChecked"
              @change="onCheckAllChange">
              <span class="question-category-choose-all-header-check-all-text">选择全部分类</span>
            </a-checkbox>
          </div>
          <div class="question-category-choose-all-body">
            <div v-for="category in categories" class="question-category-choose-all-body-category"
                 @mouseover="checkChildrenOperationVisibleId = category.id"
                 @mouseleave="checkChildrenOperationVisibleId = undefined">
              <div :class="`question-category-choose-all-body-category-depth-${category.depth}`">
                <a-tooltip placement="topLeft" title="该分类下暂无题目" v-if="disabledCategories[category.id]">
                  <a-checkbox :disabled="true">
                    <span class="question-category-choose-all-body-category-name">{{ category.name }}</span>
                  </a-checkbox>
                </a-tooltip>
                <a-checkbox :value="category.id" :checked="checkedCategories[category.id]" @change="onCheckBoxChange" v-if="disabledCategories[category.id] === false">
                  <span class="question-category-choose-all-body-category-name">{{ category.name }}</span>
                </a-checkbox>
              </div>
              <div>
                <span class="question-category-choose-all-body-category-operation"
                      v-show="checkChildrenVisible(category.id)"
                      @click="checkedSelfAndChildren(category.id)">全选</span>
                <span class="question-category-choose-all-body-category-operation"
                      v-show="unCheckChildrenVisible(category.id)"
                      @click="unCheckedSelfAndChildren(category.id)">取消全选</span>
              </div>
            </div>
            <div v-if="categories.length === 0" class="question-category-choose-all-body-empty">
              <div class="question-category-choose-all-body-empty-content">
                <div class="question-category-choose-all-body-empty-content-img">
                  <img
                    src="/static-dist/app/img/question-bank/empty.png"
                    alt=""
                  />
                </div>
                <span class="question-category-choose-all-body-empty-content-text">暂无分类</span>
              </div>
              <button class="question-category-choose-all-body-add-category-btn" @click="modalVisible = true">添加题目分类</button>
            </div>
          </div>
        </div>
        <div class="question-category-choose-selected">
          <div class="question-category-choose-selected-header">
            <span class="question-category-choose-selected-header-selected">已选</span>
            <a-popconfirm
              title="确定要清空全部吗？"
              placement="bottomRight"
              ok-text="确定"
              cancel-text="取消"
              @confirm="unCheckedAllCategories"
            >
              <span class="question-category-choose-selected-header-clear">清空</span>
            </a-popconfirm>
          </div>
          <div class="question-category-choose-selected-body">
            <div v-for="category in categories" v-show="checkedCategories[category.id]"
                 class="question-category-choose-selected-body-item">
              <div class="question-category-choose-selected-body-item-text">
                <div class="question-category-choose-selected-body-item-text-name">{{ category.name }}</div>
                <span class="question-category-choose-selected-body-item-text-level">{{ category.level }}</span>
              </div>
              <img
                class="question-category-choose-selected-body-item-remove"
                src="/static-dist/app/img/question-bank/remove-selected-question-category-icon.png"
                alt=""
                @click="unCheckedCategory(category.id)"
              />
            </div>
          </div>
        </div>
      </div>
      <div class="question-category-choose-container-footer">
        <div class="question-category-choose-container-footer-btn-group">
          <button class="question-category-choose-container-footer-btn-cancel" @click="onDrawerClose">
            <span class="question-category-choose-container-footer-btn-text">取消</span>
          </button>
          <button class="question-category-choose-container-footer-btn-save" @click="saveCheckedCategories">
            <span class="question-category-choose-container-footer-btn-text">保存</span>
          </button>
        </div>
      </div>
    </a-drawer>
    <question-category-create-modal
      :visible="modalVisible"
      :bankId="bankId"
      @ok="onCategoriesCreated"
      @cancel="modalVisible = false"
    />
  </div>
</template>

<script>
import {apiClient} from 'common/vue/service/api-client';
import QuestionCategoryCreateModal from './QuestionCategoryCreateModal';

export default {
  props: {
    bankId: undefined,
    selectedCategories: undefined,
  },
  components: {
    QuestionCategoryCreateModal
  },
  data() {
    return {
      drawerVisible: false,
      modalVisible: false,
      checkChildrenOperationVisibleId: undefined,
      categories: [],
      categoriesTree: {},
      checkedCategories: {},
      disabledCategories: {},
    };
  },
  computed: {
    checkChildrenVisible() {
      return id => {
        if (id !== this.checkChildrenOperationVisibleId || this.categoriesTree[id].children.length === 0 || this.disabledCategories[id]) {
          return false;
        }
        return this.hasChildUnchecked(id);
      };
    },
    unCheckChildrenVisible() {
      return id => {
        if (id !== this.checkChildrenOperationVisibleId || this.categoriesTree[id].children.length === 0 || this.disabledCategories[id]) {
          return false;
        }
        return !this.hasChildUnchecked(id);
      };
    },
    checkAllBoxIndeterminate() {
      let checked = 0;
      for (const category of this.categories) {
        if (this.checkedCategories[category.id]) {
          checked++;
        }
      }
      return checked > 0 && checked < this.categories.length;
    },
    allCategoriesChecked() {
      for (const category of this.categories) {
        if (!this.disabledCategories[category.id] && !this.checkedCategories[category.id]) {
          return false;
        }
      }
      return this.categories.length > 0;
    },
  },
  methods: {
    onDrawerDisplay() {
      this.fetchQuestionCategories();
      this.drawerVisible = true;
    },
    async fetchQuestionCategories(name = '') {
      const questionCounts = await apiClient.get('/api/item/categoryId/count', {params: {bank_id: this.bankId}});
      this.disabledCategories = {};
      questionCounts.forEach(item => {
        this.disabledCategories[item.category_id] = false;
      });
      const categories = await apiClient.get(`/api/item_bank/${this.bankId}/item_category_transform/treeList`);
      this.categories = [];
      this.categoriesTree = {};
      categories.forEach(category => {
        if (!category.name.includes(name)) {
          return;
        }
        this.categories.push({
          id: category.id,
          name: category.name,
          depth: category.depth,
          level: this.getCategoryLevelText(category.depth)
        });
        this.categoriesTree[category.id] = {
          children: []
        };
        if (this.categoriesTree[category.parent_id]) {
          this.categoriesTree[category.parent_id].children.push(category.id);
        }
        if (this.disabledCategories[category.id] === undefined) {
          this.disabledCategories[category.id] = true;
        }
      });
      this.checkedCategories = {};
      this.selectedCategories.forEach(category => {
        if (!this.disabledCategories[category.id]) {
          this.checkedCategory(category.id);
        }
      });
    },
    getCategoryLevelText(depth) {
      return {
        1: '一级分类',
        2: '二级分类',
        3: '三级分类',
      }[depth];
    },
    onCheckAllChange(event) {
      this.categories.forEach(category => {
        if (!this.disabledCategories[category.id]) {
          this.$set(this.checkedCategories, category.id, event.target.checked);
        }
      });
    },
    onCheckBoxChange(event) {
      this.$set(this.checkedCategories, event.target.value, event.target.checked);
    },
    hasChildUnchecked(id) {
      if (!this.disabledCategories[id] && !this.checkedCategories[id]) {
        return true;
      }
      if (this.categoriesTree[id].children) {
        for (const childId of this.categoriesTree[id].children) {
          if (this.hasChildUnchecked(childId)) {
            return true;
          }
        }
      }
      return false;
    },
    checkedSelfAndChildren(id) {
      if (!this.disabledCategories[id]) {
        this.checkedCategory(id);
      }
      if (this.categoriesTree[id].children) {
        this.categoriesTree[id].children.forEach(childId => {
          this.checkedSelfAndChildren(childId);
        });
      }
    },
    unCheckedSelfAndChildren(id) {
      this.unCheckedCategory(id);
      if (this.categoriesTree[id].children) {
        this.categoriesTree[id].children.forEach(childId => {
          this.unCheckedSelfAndChildren(childId);
        });
      }
    },
    unCheckedAllCategories() {
      this.categories.forEach(category => {
        this.unCheckedCategory(category.id);
      });
    },
    checkedCategory(id) {
      this.$set(this.checkedCategories, id, true);
    },
    unCheckedCategory(id) {
      this.$set(this.checkedCategories, id, false);
    },
    saveCheckedCategories() {
      if (this.categories.length === 0) {
        this.$message.error('该题库下暂无分类，请添加题目分类');
        return;
      }
      let selectedCategories = [];
      this.categories.forEach(category => {
        if (this.checkedCategories[category.id]) {
          selectedCategories.push(category);
        }
      });
      if (selectedCategories.length === 0) {
        this.$message.error('请选择分类');
        return;
      }
      this.$emit('save-selected-categories', selectedCategories);
      this.drawerVisible = false;
      this.$message.success('保存成功');
    },
    onSearchCategories(event) {
      this.fetchQuestionCategories(event.target.value);
    },
    onDrawerClose() {
      this.$confirm({
        title: '确定放弃此次操作吗？',
        content: '当前操作尚未保存',
        icon: 'exclamation-circle',
        okText: '确定',
        cancelText: '取消',
        centered: true,
        onOk: () => {
          this.drawerVisible = false;
        },
      });
    },
    onCategoriesCreated() {
      this.fetchQuestionCategories();
      this.modalVisible = false;
    },
  }
}
</script>
