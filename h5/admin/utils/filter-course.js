const courseListData = (data, type) => {
  console.log(data, type)
  if (type === 'class_list') {
    return {
      id: data.id,
      targetId: data.targetId,
      imgSrc: data.cover.middle,
      title: data.title,
      price: data.price,
      courseNum: data.courseNum,
      studentNum: data.studentNum
    }
  } else if (type === 'course_list') {
    return{
      imgSrc: data.courseSet.cover.middle,
      title: data.courseSetTitle,
      teachPlan: data.title,
      price: data.price,
      studentNum: data.studentNum
    }
  }
}
export default courseListData;