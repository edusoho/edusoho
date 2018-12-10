const head = {
  course_list: [
    {
      col: 5,
      title: '课程名称',
      label: 'displayedTitle',
    }, {
      col: 3,
      title: '商品价格',
      label: 'price',
    }, {
      col: 3,
      title: '创建时间',
      label: 'createdTime',
    }, {
      col: 0,
      title: '操作',
      label: 'delete',
    }
  ],
  classroom_list: [
    {
      col: 4,
      title: '班级名称',
      label: 'title',
    }, {
      col: 2,
      title: '商品价格',
      label: 'price',
    }, {
      col: 2,
      title: '课程数量',
      label: 'courseNum',
    }, {
      col: 3,
      title: '创建时间',
      label: 'createdTime',
    }, {
      col: 0,
      title: '操作',
      label: 'delete',
    }
  ],
  groupon: [
    {
      col: 4,
      title: '活动名称',
      label: 'name',
    }, {
      col: 1,
      title: '商品原价',
      label: 'originPrice',
    }, {
      col: 1,
      title: '团长价格',
      label: 'ownerPrice',
    }, {
      col: 1,
      title: '团员价格',
      label: 'memberPrice',
    }, {
      col: 3,
      title: '创建时间',
      label: 'createdTime',
    }, {
      col: 0,
      title: '操作',
      label: 'delete',
    }
  ],
  coupon: [
    {
      col: 2,
      title: '优惠券名称',
      label: 'name',
    },
     {
      col: 1,
      title: '前缀',
      label: 'prefix',
    },
    {
      col: 1,
      title: '使用对象',
      label: 'targetType',
    }, {
      col: 2,
      title: '优惠内容',
      label: 'rate',
    }, {
      col: 1,
      title: '剩余/总量',
      label: 'generatedNum',
    }, {
      col: 2,
      title: '有效期至',
      label: 'deadline',
    }, {
      col: 0,
      title: '操作',
      label: 'delete'
    }
  ]
};

export default head;
