<template>
  <el-dialog
    :visible.sync="dialogVisible"
    width="90%"
    :before-close="handleClose">
    <div class="course-modal__header" slot="title">
      <span class="header__title">选择课程</span>
      <span class="header__subtitle">仅显示已发布课程</span>
    </div>
    <div class="course-modal__body">
      <div class="search__container"">
        <span class="search__label"">选择课程：</span>
        <!-- 接口字段 courseSetTitle -->
        <el-input class="search__input" clearable
          size="medium" v-model="keyWord"
          placeholder="搜索课程"></el-input>
      </div>
    </div>
    <course-table></course-table>
    <span slot="footer" class="dialog-footer">
      <el-button @click="dialogVisible = false">取 消</el-button>
      <el-button type="primary" @click="dialogVisible = false">保 存</el-button>
    </span>
  </el-dialog>
</template>

<script>
import courseTable from './course-table'

export default {
  name: 'course-modal',
  components: {
    courseTable,
  },
  props: {

  },
  data () {
    return {
      dialogVisible: true,
      keyWord: ''
    }
  },
  methods: {
    handleClose() {
      console.log('handleClose')
    },
  }
}
</script>
