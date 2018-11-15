const courseListData = (data, type) => {
  console.log(data, type)
  if (type === 'class_list') {
    return {
      id: data.id,
      targetId: data.targetId,
      imgSrc: {
        url: data.cover.middle,
        width: '140px',
        height: '93px',
      },
      title: data.title,
      price: data.price,
      courseNum: data.courseNum,
      studentNum: data.studentNum
    }
  } else if (type === 'course_list') {
    return{
      imgSrc: {
        url: data.courseSet.cover.middle,
        width: '140px',
        height: '79px',
      },
      title: data.courseSetTitle,
      teachPlan: data.title,
      price: data.price,
      studentNum: data.studentNum
    }
  }
}
export default courseListData;