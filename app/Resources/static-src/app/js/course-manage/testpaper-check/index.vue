<template>
  <div class="testpaper-check-header">
    <a-form-model layout="inline">
      <a-form-model-item>
        <a-tree-select
          v-model="currentValue"
          style="width: 200px"
          :dropdown-style="{ maxHeight: '400px', maxWidth: '200px'}"
          dropdownClassName="testpaper-dropdown"
          :tree-data="treeData"
          :placeholder="'placeholder.class.name' | trans"
          @change="changeDrop"
        >
        </a-tree-select>
      </a-form-model-item>
      <a-form-model-item>	
        <a-input
          v-model="title"
          :placeholder="'placeholder.task.name' | trans"
          style="width: 306px;"
        >
        </a-input>
      </a-form-model-item>
      <a-form-model-item>
        <a-button type="primary" @click="search">
          {{ "site.search_hint" | trans }}
        </a-button>
      </a-form-model-item>
      <a-form-model-item>
        <a-button type="default" @click="handleReset">
          {{ "question.bank.reset.btn" | trans }}
        </a-button>
      </a-form-model-item>
    </a-form-model>
  </div>
</template>

<script>
export default {
  data() {
    return {
      treeData: [],
      title: '',
      currentValue: undefined,
      targetType: $('.js-testpaper-check-header').val(),
      targetId: $('.js-testpaper-check-targetid').val(),
      type: $('.js-testpaper-check-type').val(),
      currentSelectItem: ''
    };
  },
  created() {
    this.getTreeData()
  },

  methods: {
    handleReset() {
      this.title = ''
      this.currentValue = undefined
    },
    search() {
      const type = {
        course: 'courseId',
        chapter: 'chapterId',
        unit: 'unitId',
        lesson: 'lessonId',
      }
      
      $.ajax({
        type: 'GET',
        url: `/testpaper/check/${this.targetType}/${this.targetId}/${this.type}`,
        data:this.currentSelectItem == undefined ? JSON.parse({title: this.title}) : {[type[this.currentSelectItem]]: this.currentValue, title: this.title}
      }).done((resp) =>{
          $('.js-task-list').html(resp)
      }).fail(function(){
        
      });
    },
    getTreeData() {
      $.ajax({
        type: 'GET',
        url: `/chapter/manage/lessonTree/${this.targetType}/${this.targetId}/${this.type}`,
      }).done((resp) =>{
          this.treeData = resp || []
          this.treeData.forEach(element => {
            this.setData(element)
          });
      }).fail(function(){
        
      });
    },
    setData(item) {
      if (item.children && item.children.length > 0) {
        item.children.forEach(subItem => {
          this.setData(subItem)
        })
      }
      item.key = item.id
      item.value = item.id
    },

    changeDrop(value, label, extra) {
      if (value == undefined) return
      this.currentSelectItem =  extra.triggerNode._props.dataRef.type
    }
  }
};
</script>

<style scoped></style>
