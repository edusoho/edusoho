import { createI18n } from 'vue-i18n';
import { merge } from 'lodash-es'

const i18n = createI18n(merge({
  legacy: false,
  locale: window.app.lang,
  globalInjection: true,
  messages: {
    zh_CN: {
      contract: '电子合同',
      createContract: '创建电子合同',
      contractManagement: '电子合同管理',
      signatureRecord: '签署记录',
      btn: {
        search: '搜索',
        reset: '重置',
        view: '查看',
        editor: '编辑',
        delete: '删除',
      },
      label: {
        signatureTime: '签署时间',
      },
      select: {
        name: '名称',
        regenerator: '更新人',
        curriculum: '课程',
        questionBank: '题库',
        class: '班级',
        username: '用户名',
        phoneNumber: '手机号',
        tradeName: '商品名称',
      },
      placeholder: {
        searchType: '搜索类型',
        enterName: '请输入名称',
        commodityType: '商品类型',
      },
      list: {
        title: {
          name: '名称',
          relatedGoods: '关联商品',
          regenerator: '更新人',
          updateTime: '更新时间',
          controls: '操作',
          contractNumber: '合同编号',
          username: '用户名',
          phoneNumber: '手机号',
          commodityType: '商品类型',
          tradeName: '商品名称',
          contractName: '电子合同名称',
        },
        content: {
          curriculum: '课程',
          questionBank: '题库',
          class: '班级',
          signatureTime: '签署时间',
          orderNumber: '订单号',
        }
      },
      pagination: {
        total: '共',
        item: '项'
      },
      tip: {
        title: '管理员手动加入课程/班级/题库的学员，如果没有生成订单，这里不展示订单号',
      }
    },
    en: {
      contract: 'Contract',
      createContract: 'Create an electronic contract',
      contractManagement: 'Electronic contract management',
      signatureRecord: 'Signature record',
      btn: {
        search: 'Search',
        reset: 'Reset',
        view: 'View',
        editor: 'Editor',
        delete: 'Delete',
      },
      label: {
        signatureTime: 'Signature time',
      },
      select: {
        name: 'Name',
        regenerator: 'Regenerator',
        curriculum: 'curriculum',
        questionBank: 'Question bank',
        class: 'class',
        username: 'Username',
        phoneNumber: 'Mobile phone number',
        tradeName: 'Trade name',
      },
      placeholder: {
        searchType: 'Search type',
        enterName: 'Please enter name',
        commodityType: 'Commodity type',
      },
      list: {
        title: {
          name: 'Name',
          relatedGoods: 'Related goods',
          regenerator: 'Regenerator',
          updateTime: 'Update time',
          controls: 'Controls',
          contractNumber: 'Contract number',
          username: 'Username',
          phoneNumber: 'Mobile phone number',
          commodityType: 'Commodity type',
          tradeName: 'Trade name',
          contractName: 'Electronic contract name',
        },
        content: {
          curriculum: 'curriculum',
          questionBank: 'Question bank',
          class: 'class',
          signatureTime: 'Signature time',
          orderNumber: 'Order number',
        }
      },
      pagination: {
        total: 'In total',
        item: 'item'
      },
      tip: {
        title: 'If the administrator manually joins the course/class/question bank, the order number is not displayed here if no order is generated',
      }
    }
  },
}, {}));

export const t = i18n.global.t