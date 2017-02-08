class DataReported {
  constructor(props) {
    this.datas = {};
    this.url = '';

    this.reportedTime = props.reportedTime || 120;
    this.serverInterval = null;
    
    this.startTime = 1486455374420; // 页面上获取

    this.init();

    window.onbeforeunload = () => {  
      this.clear(); 
      this.flush();
    } 
  }

  init() {
    this.clear();
    this.serverInterval = setInterval(() => this.flush(),this.reportedTime * 1000);
  }

  clear() {
    clearInterval(this.serverInterval);
  }

  set(type,data) {
    Object.assign(this.data, {
      type: data
    });
    this.flush();
  }

  flush() {
    Object.assign(this.data, {
      'stayTime': {
        'startTime': this.startTime
      }
    });

    $.post(this.url,this.datas)
      .done((data)=> {
        this.startTime = 1486450420202;
        console.log('上报成功');
      })
      .fail((error) => {
        console.log('上报失败');
      })
  }

}



// var I64BIT_TABLE =
//  'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_-'.split('');
  
// function hash(input){
//  var hash = 5381;
//  var i = input.length - 1;
  
//  if(typeof input == 'string'){
//   for (; i > -1; i--)
//    hash += (hash << 5) + input.charCodeAt(i);
//  }
//  else{
//   for (; i > -1; i--)
//    hash += (hash << 5) + input[i];
//  }
//  var value = hash & 0x7FFFFFFF;
  
//  var retValue = '';
//  do{
//   retValue += I64BIT_TABLE[value & 0x3F];
//  }
//  while(value >>= 6);
  
//  return retValue;
// }

// class DataReported {
//   constructor(props) {
//     // this.dataTypes = props.dataTypes|| [];
//     // this.datas = props.datas || {};
//     this.datas = {};
//     this.url = '';

//     this.reportedTime = props.reportedTime || 120;
//     // this.localStorageTime = props.localStorageTime || 1;
//     // this.localInterval = null;
//     this.serverInterval = null;
    
//     this.startTime = 1486455374420; // 页面上获取

//     // this.init(this.datas);
//     this.init();

//     window.onbeforeunload = () => {  
//       this.clear(); 
//       this.flush();
//       // return false; // 可以阻止关闭 
//     } 
//   }

//   // getHash() {
//   //   return hash(location.pathname)
//   //   // console.log(location.pathname,hash(location.pathname))
//   // }

//   init() {
//     // console.log('datas',datas);
//     // datas ? Object.assign(this.data, datas) : '';
//     // this.interval = setInterval(() => this.dataAction(),this.localStorageTime * 1000);
//     this.serverInterval = setInterval(() => this.flush(),this.reportedTime * 1000);
//   }

//   clear() {
//     // clearInterval(this.interval);
//     clearInterval(this.serverInterval);
//   }

//   set(type,data) {
//     Object.assign(this.data, {
//       type: data
//     });
//     this.clear();
//     this.flush();
//     this.init();
//   }

//   // dataAction() {
//   //   const nowTime = new Date();
//   //   const hash = this.getHash();
//   //   Object.assign(this.datas,{
//   //     startTime: this.startTime.getTime(),
//   //     endTime: nowTime.getTime(),
//   //   })

//   //   this.dataTypes.map((type,i)=> {
//   //     switch(type) {
//   //       case 'stayTime': 
//   //         this._stayTime(nowTime);
//   //         break;
//   //       case 'videoViewsTime':
//   //         this._videoViewsTime();
//   //         break;
//   //       default:
//   //         break;
//   //     }
//   //   });
//   //   // if (localStorage.)
//   //   // localStorage.setItem('reportedDatas',JSON.stringify(this.datas))
//   // }


//   flush() {
//     // console.log(JSON.parse(localStorage.getItem('reportedDatas')));

//     // let data = JSON.parse(localStorage.getItem('reportedDatas'));

//     Object.assign(this.data, {
//       'stayTime': {
//         'startTime': this.startTime
//       }
//     });

//     $.post(this.url,this.datas)
//       .done((data)=> {
//         this.startTime = 1486450420202;
//         // this.startTime = data.startTime;
//         console.log('上报成功');
//       })
//       .fail((error) => {
//         console.log('上报失败');
//       })
//   }

//   // _stayTime(nowTime) {
//   //   let stayTime = 0;

//   //   stayTime = Math.floor((nowTime.getTime() - this.startTime.getTime()) / 1000);

//   //   Object.assign(this.datas,{
//   //     stayTime: stayTime
//   //   })  
//   // }
//   // _videoViewsTime() {
    
//   // }
// }

export default DataReported;