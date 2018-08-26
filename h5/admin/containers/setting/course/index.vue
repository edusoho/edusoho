<template>
  <module-frame containerClass="setting-course" :isActive="isActive">
    <div slot="preview" class="find-page__part">
      <e-course-list :courseList="courseList" :maxNum="maxNum" :feedback="false"></e-course-list>
    </div>

    <div slot="setting" class="course-allocate">
      <header class="title">课程列表设置</header>
      <div class="course-item-setting clearfix">
        <!-- 列表名称 -->
        <div class="course-item-setting__section">
          <p class="pull-left section-left">列表名称：</p>
          <div class="section-right">
            <el-input size="mini" v-model="courseList.title" placeholder="请输入列表名称" clearable></el-input>
          </div>
        </div>
        <!-- 课程来源 -->
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">课程来源：</p>
          <div class="section-right">
            <el-radio v-model="radio" label="category">课程分类</el-radio>
            <el-radio v-model="radio" label="custom">自定义</el-radio>
          </div>
        </div>
        <!-- 课程分类 -->
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">课程分类：</p>
          <div class="section-right">
            <!-- <el-input size="mini" v-model="category" placeholder="请输入列表名称"> -->
            <el-cascader
              v-if="radio === 'category'"
              size="mini"
              placeholder="请输入列表名称"
              :options="categoryOptions"
              v-model="category"
              filterable
              change-on-select
            ></el-cascader>
            </el-input>
            <div v-show="radio === 'custom'">
              <el-button type="info" size="mini" @click="openModal">选择课程</el-button>
            </div>
          </div>
          <draggable v-model="courseSets" class="section__course-container">
            <div class="section__course-item" v-for="(courseItem, index) in courseSets" :key="index">
              <div class="section__course-item__title text-overflow">{{ courseItem.title }}</div>
              <i class="h5-icon h5-icon-cuowu1 section__course-item__icon-delete" @click="deleteCourse(index)"></i>
            </div>
          </draggable>
        </div>
        <!-- 排列顺序 -->
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">排列顺序：</p>
          <div class="section-right">
            <div class="section-right__item pull-left">
              <el-select v-model="sortSelected" placeholder="排列顺讯" size="mini">
                <el-option v-for="item in sortOptions" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </div>
            <div class="section-right__item pull-right">
              <el-select v-model="date" placeholder="时间区间" size="mini">
                <el-option v-for="item in dateOptions" :key="item.value" :label="item.label" :value="item.value">
                </el-option>
              </el-select>
            </div>
          </div>
        </div>
        <!-- 显示个数 -->
        <div class="course-item-setting__section mtl">
          <p class="pull-left section-left">显示个数：</p>
          <div class="section-right">
            <el-select v-model="maxNum" placeholder="请选择个数" size="mini">
              <el-option v-for="item in [1,2,3,4,5,6,7,8]" :key="item" :label="item" :value="item">
              </el-option>
            </el-select>
          </div>
        </div>
      </div>
    </div>

    <course-modal slot="modal" :visible="modalVisible"
                  :courseList="courseSets"
                  @visibleChange="modalVisibleHandler"
                  @sort="getSortedCourses"></course-modal>
  </module-frame>
</template>

<script>
import draggable from 'vuedraggable';
import courseList from '@/containers/components/e-course-list/e-course-list';
import courseModal from './modal/course-modal'
import moduleFrame from '../module-frame'

export default {
  components: {
    'e-course-list': courseList,
    draggable,
    courseModal,
    moduleFrame,
  },
  props: {
    active: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    isActive: {
      get() {
        return this.active;
      },
      set() {}
    }
  },
  data() {
    return {
      modalVisible: false,
      maxNumOptions: [1,2,3,4,5,6,7,8],
      maxNum: 1,
      courseSets: [
        {
          title: '如何干一个产品经理, 如何干一个产品经理, 如何干一个产品经理如何干一个产品经理如何干一个产品经理',
          price: '3333.00', // 价格显示原价
          createTime: '2018-06-02 15:00',
        }, {
          title: '如何干一个程序员',
          price: '3.30',
          createTime: '2018-06-02 15:00',
        }, {
          title: '如何干一个测试',
          price: '0.01',
          createTime: '2018-06-02 15:00',
        }
      ],
      sortSelected: '加入最多',
      sortOptions: [
        {
          value: '加入最多',
          label: '加入最多'
        }, {
          value: '最近创建',
          label: '最近创建'
        }, {
          value: '评价最高',
          label: '评价最高'
        }
      ],
      category: ["zhinan"],
      categoryOptions: [{
        value: 'zhinan',
        label: '指南',
        children: [{
          value: 'shejiyuanze',
          label: '设计原则',
          children: [{
            value: 'yizhi',
            label: '一致'
          }, {
            value: 'fankui',
            label: '反馈'
          }, {
            value: 'xiaolv',
            label: '效率'
          }, {
            value: 'kekong',
            label: '可控'
          }]
        }, {
          value: 'daohang',
          label: '导航',
          children: [{
            value: 'cexiangdaohang',
            label: '侧向导航'
          }, {
            value: 'dingbudaohang',
            label: '顶部导航'
          }]
        }]
      }, {
        value: 'zujian',
        label: '组件',
        children: [{
          value: 'basic',
          label: 'Basic',
          children: [{
            value: 'layout',
            label: 'Layout 布局'
          }, {
            value: 'color',
            label: 'Color 色彩'
          }, {
            value: 'typography',
            label: 'Typography 字体'
          }, {
            value: 'icon',
            label: 'Icon 图标'
          }, {
            value: 'button',
            label: 'Button 按钮'
          }]
        }, {
          value: 'form',
          label: 'Form',
          children: [{
            value: 'radio',
            label: 'Radio 单选框'
          }, {
            value: 'checkbox',
            label: 'Checkbox 多选框'
          }, {
            value: 'input',
            label: 'Input 输入框'
          }, {
            value: 'input-number',
            label: 'InputNumber 计数器'
          }, {
            value: 'select',
            label: 'Select 选择器'
          }, {
            value: 'cascader',
            label: 'Cascader 级联选择器'
          }, {
            value: 'switch',
            label: 'Switch 开关'
          }, {
            value: 'slider',
            label: 'Slider 滑块'
          }, {
            value: 'time-picker',
            label: 'TimePicker 时间选择器'
          }, {
            value: 'date-picker',
            label: 'DatePicker 日期选择器'
          }, {
            value: 'datetime-picker',
            label: 'DateTimePicker 日期时间选择器'
          }, {
            value: 'upload',
            label: 'Upload 上传'
          }, {
            value: 'rate',
            label: 'Rate 评分'
          }, {
            value: 'form',
            label: 'Form 表单'
          }]
        }, {
          value: 'data',
          label: 'Data',
          children: [{
            value: 'table',
            label: 'Table 表格'
          }, {
            value: 'tag',
            label: 'Tag 标签'
          }, {
            value: 'progress',
            label: 'Progress 进度条'
          }, {
            value: 'tree',
            label: 'Tree 树形控件'
          }, {
            value: 'pagination',
            label: 'Pagination 分页'
          }, {
            value: 'badge',
            label: 'Badge 标记'
          }]
        }, {
          value: 'notice',
          label: 'Notice',
          children: [{
            value: 'alert',
            label: 'Alert 警告'
          }, {
            value: 'loading',
            label: 'Loading 加载'
          }, {
            value: 'message',
            label: 'Message 消息提示'
          }, {
            value: 'message-box',
            label: 'MessageBox 弹框'
          }, {
            value: 'notification',
            label: 'Notification 通知'
          }]
        }, {
          value: 'navigation',
          label: 'Navigation',
          children: [{
            value: 'menu',
            label: 'NavMenu 导航菜单'
          }, {
            value: 'tabs',
            label: 'Tabs 标签页'
          }, {
            value: 'breadcrumb',
            label: 'Breadcrumb 面包屑'
          }, {
            value: 'dropdown',
            label: 'Dropdown 下拉菜单'
          }, {
            value: 'steps',
            label: 'Steps 步骤条'
          }]
        }, {
          value: 'others',
          label: 'Others',
          children: [{
            value: 'dialog',
            label: 'Dialog 对话框'
          }, {
            value: 'tooltip',
            label: 'Tooltip 文字提示'
          }, {
            value: 'popover',
            label: 'Popover 弹出框'
          }, {
            value: 'card',
            label: 'Card 卡片'
          }, {
            value: 'carousel',
            label: 'Carousel 走马灯'
          }, {
            value: 'collapse',
            label: 'Collapse 折叠面板'
          }]
        }]
      }, {
        value: 'ziyuan',
        label: '资源',
        children: [{
          value: 'axure',
          label: 'Axure Components'
        }, {
          value: 'sketch',
          label: 'Sketch Templates'
        }, {
          value: 'jiaohu',
          label: '组件交互文档'
        }]
      }],
      date: '最近7天',
      dateOptions: [
        {
          value: '7',
          label: '最近7天',
        }, {
          value: '30',
          label: '最近30天',
        },  {
          value: '90',
          label: '最近90天',
        },  {
          value: 'all',
          label: '历史所有',
        }
      ],
      radio: 'custom',
      courseList: {
        "title": "热门课程",
        "items": [{
          "id": "1",
          "title": "默认教学计划",
          "courseSetTitle": "试卷未去除已删除题目数量",
          "displayedTitle": "试卷未去除已删除题目数量-默认教学计划",
          "learnMode": "freeMode",
          "summary": "",
          "goals": [],
          "audiences": [],
          "isDefault": "1",
          "maxStudentNum": "0",
          "status": "published",
          "isFree": "1",
          "price": "0.00",
          "buyable": "1",
          "tryLookable": "0",
          "tryLookLength": "0",
          "watchLimit": "0",
          "taskNum": "6",
          "studentNum": "0",
          "parentId": "0",
          "originPrice": "0.00",
          "buyExpiryTime": "0",
          "enableFinish": "1",
          "compulsoryTaskNum": "6",
          "subtitle": "",
          "courseSet": {
            "id": "1",
            "type": "normal",
            "title": "试卷未去除已删除题目数量",
            "subtitle": "",
            "summary": "",
            "cover": {
              "large": "http://lvliujie.st.edusoho.cn/files/course/2018/08-08/09393022a5e0329364.png",
              "middle": "http://lvliujie.st.edusoho.cn/files/course/2018/08-08/09393022a5e0329364.png",
              "small": "http://lvliujie.st.edusoho.cn/files/course/2018/08-08/09393022a5e0329364.png"
            },
            "studentNum": "0",
            "discount": "10.00",
            "maxCoursePrice": "0.00",
            "minCoursePrice": "0.00",
            "minCoursePrice2": {
              "currency": "RMB",
              "amount": "0.00",
              "coinAmount": "0",
              "coinName": "虚拟币"
            },
            "maxCoursePrice2": {
              "currency": "RMB",
              "amount": "0.00",
              "coinAmount": "0",
              "coinName": "虚拟币"
            }
          },
          "learningExpiryDate": {
            "expiryMode": "forever",
            "expiryStartDate": "0",
            "expiryEndDate": "0",
            "expiryDays": "0",
            "expired": false
          },
          "price2": {
            "currency": "RMB",
            "amount": "0.00",
            "coinAmount": "0",
            "coinName": "虚拟币"
          },
          "originPrice2": {
            "currency": "RMB",
            "amount": "0.00",
            "coinAmount": "0",
            "coinName": "虚拟币"
          },
          "publishedTaskNum": "6"
        }, {
          "id": "2",
          "title": "默认教学计划",
          "courseSetTitle": "测试课程2",
          "displayedTitle": "试卷未去除已删除题目数量-默认教学计划",
          "learnMode": "freeMode",
          "summary": "",
          "goals": [],
          "audiences": [],
          "isDefault": "1",
          "maxStudentNum": "0",
          "status": "published",
          "isFree": "1",
          "price": "0.00",
          "buyable": "1",
          "tryLookable": "0",
          "tryLookLength": "0",
          "watchLimit": "0",
          "taskNum": "6",
          "studentNum": "0",
          "parentId": "0",
          "originPrice": "0.00",
          "buyExpiryTime": "0",
          "enableFinish": "1",
          "compulsoryTaskNum": "6",
          "subtitle": "",
          "courseSet": {
            "id": "1",
            "type": "normal",
            "title": "试卷未去除已删除题目数量",
            "subtitle": "",
            "summary": "",
            "cover": {
              "large": "http://lvliujie.st.edusoho.cn/files/course/2018/08-08/09393022a5e0329364.png",
              "middle": "http://lvliujie.st.edusoho.cn/files/course/2018/08-08/09393022a5e0329364.png",
              "small": "http://lvliujie.st.edusoho.cn/files/course/2018/08-08/09393022a5e0329364.png"
            },
            "studentNum": "0",
            "discount": "10.00",
            "maxCoursePrice": "0.00",
            "minCoursePrice": "0.00",
            "minCoursePrice2": {
              "currency": "RMB",
              "amount": "0.00",
              "coinAmount": "0",
              "coinName": "虚拟币"
            },
            "maxCoursePrice2": {
              "currency": "RMB",
              "amount": "0.00",
              "coinAmount": "0",
              "coinName": "虚拟币"
            }
          },
          "learningExpiryDate": {
            "expiryMode": "forever",
            "expiryStartDate": "0",
            "expiryEndDate": "0",
            "expiryDays": "0",
            "expired": false
          },
          "price2": {
            "currency": "RMB",
            "amount": "0.00",
            "coinAmount": "0",
            "coinName": "虚拟币"
          },
          "originPrice2": {
            "currency": "RMB",
            "amount": "0.00",
            "coinAmount": "0",
            "coinName": "虚拟币"
          },
          "publishedTaskNum": "6"
        }],
        "source": {
          "category": 0,
          "courseType": "all",
          "sort": "-hitNum"
        }
      }
    }
  },
  methods: {
    getSortedCourses(courses) {
      this.courseSets = courses;
    },
    modalVisibleHandler(visible) {
      this.modalVisible = visible;
    },
    openModal() {
      this.modalVisible = true;
    },
    // 删除自定义课程
    deleteCourse(index) {
      this.courseSets.splice(index, 1);
    }
  }
}

</script>
