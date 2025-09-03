if (document.getElementById('exercise')?.value) {
  const exercise = JSON.parse(document.getElementById('exercise').value)
  if (exercise.isAgentActive === '1') {
    window.agentSdk.showReminder({
      title: "HI，我是你的 AI 老师小知～",
      content: `欢迎加入《${exercise.title}》课程，我将在你学习的过程中为你提供专业答疑、督学提醒等学习服务，现在点击下方「制定学习计划」来生成专属学习计划吧！`,
    })
  }

}