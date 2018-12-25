<template>
  <div class="more">
    <van-list style="padding-bottom: 40px;" v-model="loading" :finished="finished" @load="onLoad">
      <e-class v-for="item in classroomList"
        :key="item.id"
        :course="item | courseListData(listObj)"
        :discount="typeList === 'course_list' ? item.courseSet.discount : ''"
        :courseType="typeList === 'course_list' ? item.courseSet.type : ''"
        :typeList="typeList"
        :tagShow="tagShow"
        :type="type"
        :feedback="feedback">
      </e-class>
    </van-list>
  </div>
</template>

<script>
  import Api from '@/api';
  import eClass from '@/containers/components/e-class/e-class';
  import courseListData from '../../../utils/filter-course.js';

  export default {
    components: {
      eClass,
    },
    props: {
      feedback: {
        type: Boolean,
        default: true,
      },
      tagShow: {
        type: Boolean,
        default: true
      }
    },
    data() {
      return {
        classroomList: {},
        loading: false,
        finished: false,
        type: 'price',
        offset: 0,
        levelId: 1,
        typeList: 'classroom_list'
      };
    },
    computed: {
      listObj() {
        return {
          type: 'price',
          typeList: this.typeList,
          showStudent: false,
        }
      }
    },
    beforeRouteEnter(to, from, next) {
      const navTitle = to.query.vipName || '会员';
      to.meta.title = `${navTitle}班级`;
      next()
    },
    filters: {
      courseListData,
    },
    methods: {
      onLoad() {
        const params = { levelId: this.levelId, offset: this.offset }
        Api.getVipClasses({params}).then(({data, paging}) => {
          this.classroomList = [...this.classroomList, ...data];
          this.offset = this.classroomList.length

          if (this.classroomList.length == paging.total) {
            this.finished = true
          }
          this.loading = false
        }).catch(err => {
          Toast.fail(err.message)
          this.loading = false
        });
      }
    }
  }
</script>
