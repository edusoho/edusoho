if (document.getElementById('exercise')?.value) {
  const exercise = JSON.parse(document.getElementById('exercise').value)
  if (exercise.isAgentActive === '1') {
    window.agentSdk.showReminder({
      title: "HI，我是你的 AI 老师小知～",
      content: `欢迎加入《${exercise.title}》题库，我将在你学习的过程中为你提供专业答疑、题目分析等学习服务，现在开始刷题计划吧！`,
    })
  }

}