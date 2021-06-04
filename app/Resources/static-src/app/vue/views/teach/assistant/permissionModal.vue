<template>
  <a-modal
    title="助教权限管理"
    :visible="visible"
    okText="保存"
    cancelText="取消"
    @ok="handleOk"
    @cancel="handleCancel"
    destroyOnClose
  >
    <a-tree
      checkable
      :tree-data="treeData"
      :default-checked-keys="permissions"
      :replace-fields="replaceFields"
      @check="onCheck"
    />
  </a-modal>
</template>

<script>
import { AssistantPermission } from 'common/vue/service';
import _ from '@codeages/utils';

export default {
  name: 'PermissionModal',

  props: {
    visible: {
      type: Boolean,
      required: true
    },

    treeData: {
      type: Array,
      required: true
    },

    permissions: {
      type: Array,
      required: true
    }
  },

  data() {
    return {
      replaceFields: {
        title: 'title',
        key: 'code'
      },
      checkedKeys: []
    }
  },

  methods: {
    handleOk() {
      AssistantPermission.add({
        permissions: this.checkedKeys
      }).then(res => {
        this.$message.success('更新成功');
        this.handleCancel();
      });
    },

    handleCancel() {
      this.$emit('cancel-permission-modal');
    },

    onCheck(checkedKeys) {
      this.checkedKeys = checkedKeys;
    },
  }
}
</script>
