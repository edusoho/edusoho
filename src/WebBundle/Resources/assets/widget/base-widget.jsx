import 'rc-steps/assets/index.css';
import 'rc-steps/assets/iconfont.css';

import React, {Component} from 'react';
import ReactDOM from 'react-dom';
import Steps from 'rc-steps';

// class MySteps extends Component {

//   render() {
//     let data = [{
//       title: '已完成',
//       description: '这里是多信息的描述啊描述啊描述啊描述啊哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶',
//     }, {
//       title: '进行中',
//       description: '这里是多信息的描述啊描述啊描述啊描述啊哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶',
//     }, {
//       title: '待运行',
//       description: '这里是多信息的描述啊描述啊描述啊描述啊哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶',
//     }, {
//       title: '待运行',
//       description: '这里是多信息的描述啊描述啊描述啊描述啊哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶',
//     }];

//     let mySteps = [];

//     data.map((s, i) => {
//       mySteps.push(
//         <Steps.Step
//           key={i}
//           status={s.status}
//           title={s.title}
//           description={s.description}
//         />
//       )
//     });
//     console.log(mySteps);
//     return (
//       <div>
//       aaasaaaaaaaaaa
//       </div>
//     );
//   }
// }

let data = [{
  title: '已完成',
  description: '这里是多信息的描述啊描述啊描述啊描述啊哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶',
}, {
  title: '进行中',
  description: '这里是多信息的描述啊描述啊描述啊描述啊哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶',
}, {
  title: '待运行',
  description: '这里是多信息的描述啊描述啊描述啊描述啊哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶',
}, {
  title: '待运行',
  description: '这里是多信息的描述啊描述啊描述啊描述啊哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶哦耶',
}];

let mySteps = [];

data.map((s, i) => {
  mySteps.push(
    <Steps.Step
      key={i}
      status={s.status}
      title={s.title}
      description={s.description}
    />
  )
});


ReactDOM.render(
  <Steps current={1}>
    {mySteps}
  </Steps>
, document.getElementById('test'));