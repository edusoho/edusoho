let orderprogressBg = new JustGage({
  id: "orderprogress-bg",
  label:'',
  value: 0,
  hideValue:true,
  min: 0,
  max: 100,
  width: 200,
  height: 200,
  donut: true,
  gaugeWidthScale: 0.5,
  counter: true,
  labelMinFontSize: 12,
  showInnerShadow: true,
  levelColors: [
    "#f7870c",
  ]
});

let orderprogressPlan = new JustGage({
  id: "orderprogress-plan",
  label:'',
  value: 70,
  hideValue:true,
  min: 0,
  max: 100,
  width: 200,
  height: 200,
  donut: true,
  gaugeWidthScale: 0.5,
  counter: true,
  labelMinFontSize: 12,
  showInnerShadow: true,
  gaugeColor:'transparent',
  levelColors: [
    "#f7870c",
  ]
});

let orderprogress = new JustGage({
  id: "orderprogress",
  label:'',
  value: 40,
  hideValue:true,
  min: 0,
  max: 100,
  width: 200,
  height: 200,
  donut: true,
  gaugeWidthScale: 0.5,
  gaugeColor:'transparent',
  counter: true,
  labelMinFontSize: 12,
  showInnerShadow: true,
  levelColors: [
    "#45c079",
  ]
});




let freeprogress = new JustGage({
  id: "freeprogress",
  label:'',
  value: 40,
  hideValue:true,
  min: 0,
  max: 100,
  width: 200,
  height: 200,
  donut: true,
  gaugeWidthScale: 0.5,
   // gaugeColor:'transparent',
  counter: true,
  labelMinFontSize: 12,
  showInnerShadow: true,
  levelColors: [
    "#45c079",
  ]
});



