const Classifys = [
  {
    title: '基础组件',
    icon: 'icon-check-circle',
    components: [
      {
        title: '轮播图',
        icon: 'icon-check-circle',
        info: {
          type: 'slide_show',
          moduleType: '',
          data: {
            image: {},
            link: {
              type: 'course',
              target: {},
              url: ''
            },
            responsive: '1'
          }
        }
      },
      {
        title: '课程列表',
        icon: 'icon-check-circle',
        info: {
          type: 'course_list',
          moduleType: '',
          data: {
            title: '',
            sourceType: 'condition',
            categoryId: '0',
            sort: 'recommendedSeq',
            lastDays: '0',
            limit: '4',
            displayStyle: 'distichous',
            items: []
          }
        }
      },
      {
        title: '班级列表',
        icon: 'icon-check-circle',
        info: {
          type: 'classroom_list',
          moduleType: '',
          data: {
            title: '',
            sourceType: 'condition',
            categoryId: '0',
            sort: 'recommendedSeq',
            lastDays: '0',
            limit: '4',
            displayStyle: 'distichous',
            items: []
          }
        }
      },
      {
        title: '图片公告',
        icon: 'icon-check-circle',
        info: {
          type: 'poster',
          moduleType: '',
          data: {
            image: {},
            link: {
              type: 'course',
              target: {},
              url: ''
            },
            responsive: '1'
          }
        }
      },
      {
        title: '图文导航',
        icon: 'icon-check-circle',
        name: 'Nav',
        info: {
          type: 'graphic_navigation',
          moduleType: '',
          data: [
            {
              title: '公开课',
              image: {
                url: '',
                icon: 'icon-check-circle'
              },
              link: {
                type: 'openCourse',
                target: '跳转公开课“全部”列表',
                url: ''
              }
            },
            {
              title: '热门课',
              image: {
                url: '',
                icon: 'icon-check-circle'
              },
              link: {
                type: 'course',
                target: '跳转课程“全部”列表',
                url: ''
              }
            },
            {
              title: '热销班',
              image: {
                url: '',
                icon: 'icon-check-circle'
              },
              link: {
                type: 'classroom',
                target: '跳转班级“全部”列表',
                url: ''
              }
            }
          ]
        }
      },
      {
        title: '公开课列表',
        icon: 'icon-check-circle',
        info: {
          type: 'open_course_list',
          moduleType: '',
          data: {
            title: '',
            sourceType: 'condition',
            categoryId: '0',
            limitDays: '0',
            limit: '4',
            displayStyle: 'distichous',
            items: []
          }
        }
      },
      {
        title: '题库列表',
        icon: 'icon-check-circle',
        info: {
          type: 'item_bank_exercise',
          moduleType: '',
          data: {
            title: '',
            sourceType: 'condition',
            categoryId: '',
            sort: 'recommendedSeq',
            lastDays: '0',
            limit: '4',
            displayStyle: 'distichous',
            items: []
          }
        }
      }
    ]
  },
  {
    title: '营销组件',
    icon: 'icon-check-circle',
    components: [
      {
        title: '优惠卷',
        icon: 'icon-check-circle',
        info: {
          type: 'coupon',
          moduleType: '',
          data: {
            items: [],
            titleShow: 'show'
          }
        }
      },
      {
        title: '会员专区',
        icon: 'icon-check-circle',
        info: {
          type: 'vip',
          moduleType: '',
          data: {
            items: [],
            sort: 'asc',
            title: '',
            titleShow: 'show'
          }
        }
      }
    ]
  }
];

export {
  Classifys
};
