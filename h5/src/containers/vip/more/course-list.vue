<template>
  <div class="more">
    <van-list style="padding-bottom: 40px;" v-model="loading" :finished="finished" @load="onLoad">
      <e-class v-for="item in courseList"
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
  import { mapState } from 'vuex';

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
        courseList: {},
        loading: false,
        finished: false,
        type: 'price',
        offset: 0,
        levelId: 1,
        typeList: 'course_list'
      };
    },
    computed: {
      ...mapState(['courseSettings']),
      listObj() {
        return {
          type: 'price',
          typeList: this.typeList,
          showStudent: this.courseSettings ?
                      Number(this.courseSettings.show_student_num_enabled) : true,
        }
      }
    },
    filters: {
      courseListData,
    },
    created() {},
    methods: {
      onLoad() {
        const params = { levelId: this.levelId, offset: this.offset }
        Api.getVipCourses({params}).then(({data, paging}) => {
          this.courseList = [...this.courseList, ...data];
          this.offset = this.courseList.length

          if (this.courseList.length == paging.total) {
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
