export function useEditor(mathField, MQ) {

	// 平方
	const sup2Button = $('#option #sup2')[0];
	sup2Button.addEventListener('click', function(){
		mathField.typedText('^2');
		mathField.moveToRightEnd().focus();
	});
	const sup2MQ = MQ.StaticMath(sup2Button);
	sup2MQ.latex('a^2');

	// 上标 次方
	const supButton = $('#option #sup')[0];
	supButton.addEventListener('click', function(){
		mathField.cmd('^').focus();
	});
	const supMQ = MQ.StaticMath(supButton);
	supMQ.latex('a^x');

	// 下标
	const subButton = $('#option #sub')[0];
	subButton.addEventListener('click', function(){
		mathField.cmd('_').focus();
	});
	const subMQ = MQ.StaticMath(subButton);
	subMQ.latex('a_x');

	// 平方根
	const sqrt2Button = $('#option #sqrt2')[0];
	sqrt2Button.addEventListener('click', function(){
		mathField.cmd('\\sqrt{ }').focus();
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.keystroke('Left');
	});
	const sqrt2MQ = MQ.StaticMath(sqrt2Button);
	sqrt2MQ.latex('\\sqrt{a}');

	// 开根号
	const sqrtButton = $('#option #sqrt')[0];
	sqrtButton.addEventListener('click', function(){
		mathField.cmd('\\sqrt[ ]{ }').focus();
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.keystroke('Left Left');
	});
	const sqrtMQ = MQ.StaticMath(sqrtButton);
	sqrtMQ.latex('\\sqrt[x]{a}');

	// 除以
	const fracButton = $('#option #frac')[0];
	fracButton.addEventListener('click', function(){
		mathField.keystroke('Shift-Left');
		mathField.cmd('\\frac');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.keystroke('Down');
		mathField.focus();
	});
	const fracMQ = MQ.StaticMath(fracButton);
	fracMQ.latex('\\frac{a}{x}');

	// 绝对值
	const absButton = $('#option #abs')[0];
	absButton.addEventListener('click', function(){
		mathField.cmd('\\left|\\right|');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.keystroke('Left');
		mathField.focus();
	});
	const absMQ = MQ.StaticMath(absButton);
	absMQ.latex('\\left|a\\right|');

	// 向上取整
	const ceilButton = $('#option #ceil')[0];
	ceilButton.addEventListener('click', function(){
		mathField.cmd('\\lceil\\rceil');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.keystroke('Left');
		mathField.focus();
	});
	const ceilMQ = MQ.StaticMath(ceilButton);
	ceilMQ.latex('\\lceil x\\rceil');

	// 向下取整
	const floorButton = $('#option #floor')[0];
	floorButton.addEventListener('click', function(){
		mathField.cmd('\\lfloor\\rfloor');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.keystroke('Left');
		mathField.focus();
	});
	const floorMQ = MQ.StaticMath(floorButton);
	floorMQ.latex('\\lfloor x\\rfloor');

	// 集合
	const setButton = $('#option #set')[0];
	setButton.addEventListener('click', function(){
		mathField.cmd('\\left\\\{x\\right\\\}');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.keystroke('Left');
		mathField.focus();
	});
	const setMQ = MQ.StaticMath(setButton);
	setMQ.latex('\\left\\\{x\\right\\\}');

	// 求导
	const differentiateButton = $('#option #differentiate')[0];
	differentiateButton.addEventListener('click', function(){
		mathField.cmd('\\frac{\\text{d}x}{\\text{d}y}');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const differentiateMQ = MQ.StaticMath(differentiateButton);
	differentiateMQ.latex('\\frac{\\text{d}x}{\\text{d}y}');

	// 偏导
	const partialButton = $('#option #partial')[0];
	partialButton.addEventListener('click', function(){
		mathField.cmd('\\frac{\\partial x}{\\partial y}');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const partialQuill = MQ.StaticMath(partialButton);
	partialQuill.latex('\\frac{\\partial x}{\\partial y}');

	// 求积分
	const intButton = $('#option #int')[0];
	intButton.addEventListener('click', function(){
		mathField.cmd('\\int_x^y');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const intMQ = MQ.StaticMath(intButton);
	intMQ.latex('\\int_x^y');

	// 求曲线积分
	const ointButton = $('#option #oint')[0];
	ointButton.addEventListener('click', function(){
		mathField.cmd('\\oint_x^y');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const ointMQ = MQ.StaticMath(ointButton);
	ointMQ.latex('\\oint_x^y');

	// 取对数
	const logButton = $('#option #log')[0];
	logButton.addEventListener('click', function(){
		mathField.cmd('\\log_xy');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const logMQ = MQ.StaticMath(logButton);
	logMQ.latex('\\log_xy');

	// 取对数
	const lgButton = $('#option #lg')[0];
	lgButton.addEventListener('click', function(){
		mathField.cmd('\\lg_{10}{x}');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const lgMQ = MQ.StaticMath(lgButton);
	lgMQ.latex('\\lg_{10}{x}');

	// 取对数
	const lnButton = $('#option #ln')[0];
	lnButton.addEventListener('click', function(){
		mathField.cmd('\\ln_{e}{x}');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const lnMQ = MQ.StaticMath(lnButton);
	lnMQ.latex('\\ln_{e}{x}');

	// 取极限
	const limButton = $('#option #lim')[0];
	limButton.addEventListener('click', function(){
		mathField.cmd('\\lim_{x\\rightarrow y}');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const limMQ = MQ.StaticMath(limButton);
	limMQ.latex('\\lim_{x\\rightarrow y}');

	// 求和
	const sumButton = $('#option #sum')[0];
	sumButton.addEventListener('click', function(){
		mathField.cmd('\\sum_x^y');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const sumMQ = MQ.StaticMath(sumButton);
	sumMQ.latex('\\sum_x^y');

	// 连乘
	const prodButton = $('#option #prod')[0];
	prodButton.addEventListener('click', function(){
		mathField.cmd('\\prod_x^y');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const prodMQ = MQ.StaticMath(prodButton);
	prodMQ.latex('\\prod_x^y');

	// 并集
	const unionButton = $('#option #union')[0];
	unionButton.addEventListener('click', function(){
		mathField.cmd('\\bigcup_{x}^{y}');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const unionMQ = MQ.StaticMath(unionButton);
	unionMQ.latex('\\bigcup_{x}^{y}');

	// 交集
	const intersectionButton = $('#option #intersection')[0];
	intersectionButton.addEventListener('click', function(){
		mathField.cmd('\\bigcap_{x}^{y}');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const intersectionMQ = MQ.StaticMath(intersectionButton);
	intersectionMQ.latex('\\bigcap_{x}^{y}');

	// 左向量
	const overleftarrowButton = $('#option #overleftarrow')[0];
	overleftarrowButton.addEventListener('click', function(){
		mathField.cmd('\\overleftarrow{xy}');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const overleftarrowMQ = MQ.StaticMath(overleftarrowButton);
	overleftarrowMQ.latex('\\overleftarrow{xy}');

	// 平均值
	const overlineButton = $('#option #overline')[0];
	overlineButton.addEventListener('click', function(){
		mathField.cmd('\\overline{xy}');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const overlineMQ = MQ.StaticMath(overlineButton);
	overlineMQ.latex('\\overline{xy}');

	// 左向量
	const overrightarrowButton = $('#option #overrightarrow')[0];
	overrightarrowButton.addEventListener('click', function(){
		mathField.cmd('\\overrightarrow{xy}');
		const newLatex = (mathField.latex())
		mathField.latex('');
		mathField.write(newLatex);
		mathField.focus();
	});
	const overrightarrowMQ = MQ.StaticMath(overrightarrowButton);
	overrightarrowMQ.latex('\\overrightarrow{xy}');

	// 加法
	const addButton = $('#option #add')[0];
	addButton.addEventListener('click', function(){
		mathField.cmd('+').focus();
	});
	const addMQ = MQ.StaticMath(addButton);
	addMQ.latex('+');

	// 减法
	const subtractButton = $('#option #subtract')[0];
	subtractButton.addEventListener('click', function(){
		mathField.cmd('-').focus();
	});
	const subtractMQ = MQ.StaticMath(subtractButton);
	subtractMQ.latex('-');

	// 正负
	const pmButton = $('#option #pm')[0];
	pmButton.addEventListener('click', function(){
		mathField.cmd('\\pm').focus();
	});
	const pmMQ = MQ.StaticMath(pmButton);
	pmMQ.latex('\\pm');

	// 叉乘
	const timesButton = $('#option #times')[0];
	timesButton.addEventListener('click', function(){
		mathField.cmd('\\times').focus();
	});
	const timesMQ = MQ.StaticMath(timesButton);
	timesMQ.latex('\\times');

	// 点乘
	const cdotButton = $('#option #cdot')[0];
	cdotButton.addEventListener('click', function(){
		mathField.cmd('\\cdot').focus();
	});
	const cdotMQ = MQ.StaticMath(cdotButton);
	cdotMQ.latex('\\cdot');

	// 除以
	const divButton = $('#option #div')[0];
	divButton.addEventListener('click', function(){
		mathField.cmd('\\div').focus();
	});
	const divMQ = MQ.StaticMath(divButton);
	divMQ.latex('\\div');

	// 等于 相等
	const equalButton = $('#option #equal')[0];
	equalButton.addEventListener('click', function(){
		mathField.cmd('=').focus();
	});
	const equalMQ = MQ.StaticMath(equalButton);
	equalMQ.latex('=');

	// 小于
	const lButton = $('#option #l')[0];
	lButton.addEventListener('click', function(){
		mathField.cmd('<').focus();
	});
	const lMQ = MQ.StaticMath(lButton);
	lMQ.latex('<');

	// 大于
	const gButton = $('#option #g')[0];
	gButton.addEventListener('click', function(){
		mathField.cmd('>').focus();
	});
	const gMQ = MQ.StaticMath(gButton);
	gMQ.latex('>');

	// 不等于
	const neButton = $('#option #ne')[0];
	neButton.addEventListener('click', function(){
		mathField.cmd('\\ne').focus();
	});
	const neMQ = MQ.StaticMath(neButton);
	neMQ.latex('\\ne');

	// 小于等于
	const leButton = $('#option #le')[0];
	leButton.addEventListener('click', function(){
		mathField.cmd('\\le').focus();
	});
	const leMQ = MQ.StaticMath(leButton);
	leMQ.latex('\\le');

	// 大于等于
	const geButton = $('#option #ge')[0];
	geButton.addEventListener('click', function(){
		mathField.cmd('\\ge').focus();
	});
	const geMQ = MQ.StaticMath(geButton);
	geMQ.latex('\\ge');

	// 恒等于
	const equivButton = $('#option #equiv')[0];
	equivButton.addEventListener('click', function(){
		mathField.cmd('\\equiv').focus();
	});
	const equivMQ = MQ.StaticMath(equivButton);
	equivMQ.latex('\\equiv');

	// 约等于
	const approxButton = $('#option #approx')[0];
	approxButton.addEventListener('click', function(){
		mathField.cmd('\\approx').focus();
	});
	const approxMQ = MQ.StaticMath(approxButton);
	approxMQ.latex('\\approx');

	// 渐近等于
	const congButton = $('#option #cong')[0];
	congButton.addEventListener('click', function(){
		mathField.cmd('\\cong').focus();
	});
	const congMQ = MQ.StaticMath(congButton);
	congMQ.latex('\\cong');

	// pi 圆周率 派
	const piButton = $('#option #pi')[0];
	piButton.addEventListener('click', function(){
		mathField.cmd('\\pi').focus();
	});
	const piMQ = MQ.StaticMath(piButton);
	piMQ.latex('\\pi');

	// theta
	const thetaButton = $('#option #theta')[0];
	thetaButton.addEventListener('click', function(){
		mathField.cmd('\\theta').focus();
	});
	const thetaMQ = MQ.StaticMath(thetaButton);
	thetaMQ.latex('\\theta');

	// delta
	const deltaButton = $('#option #delta')[0];
	deltaButton.addEventListener('click', function(){
		mathField.cmd('\\Delta').focus();
	});
	const deltaMQ = MQ.StaticMath(deltaButton);
	deltaMQ.latex('\\Delta');

	// alpha
	const alphaButton = $('#option #alpha')[0];
	alphaButton.addEventListener('click', function(){
		mathField.cmd('\\alpha').focus();
	});
	const alphaMQ = MQ.StaticMath(alphaButton);
	alphaMQ.latex('\\alpha');

	// beta
	const betaButton = $('#option #beta')[0];
	betaButton.addEventListener('click', function(){
		mathField.cmd('\\beta').focus();
	});
	const betaMQ = MQ.StaticMath(betaButton);
	betaMQ.latex('\\beta');

	// nabla
	const nablaButton = $('#option #nabla')[0];
	nablaButton.addEventListener('click', function(){
		mathField.cmd('\\nabla').focus();
	});
	const nablaMQ = MQ.StaticMath(nablaButton);
	nablaMQ.latex('\\nabla');

	// parallel
	const parallelButton = $('#option #parallel')[0];
	parallelButton.addEventListener('click', function(){
		mathField.cmd('\\parallel').focus();
	});
	const parallelMQ = MQ.StaticMath(parallelButton);
	parallelMQ.latex('\\parallel');

	// perp
	const perpButton = $('#option #perp')[0];
	perpButton.addEventListener('click', function(){
		mathField.cmd('\\perp').focus();
	});
	const perpMQ = MQ.StaticMath(perpButton);
	perpMQ.latex('\\perp');

	// angle
	const angleButton = $('#option #angle')[0];
	angleButton.addEventListener('click', function(){
		mathField.cmd('\\angle').focus();
	});
	const angleMQ = MQ.StaticMath(angleButton);
	angleMQ.latex('\\angle');

	// degree
	const degreeButton = $('#option #degree')[0];
	degreeButton.addEventListener('click', function(){
		mathField.cmd('\\degree').focus();
	});
	const degreeMQ = MQ.StaticMath(degreeButton);
	degreeMQ.latex('\\degree');

	// infty
	const inftyButton = $('#option #infty')[0];
	inftyButton.addEventListener('click', function(){
		mathField.cmd('\\infty').focus();
	});
	const inftyMQ = MQ.StaticMath(inftyButton);
	inftyMQ.latex('\\infty');

	// propto
	const proptoButton = $('#option #propto')[0];
	proptoButton.addEventListener('click', function(){
		mathField.cmd('\\propto').focus();
	});
	const proptoMQ = MQ.StaticMath(proptoButton);
	proptoMQ.latex('\\propto');

	// leftarrow
	const leftarrowButton = $('#option #leftarrow')[0];
	leftarrowButton.addEventListener('click', function(){
		mathField.cmd('\\leftarrow').focus();
	});
	const leftarrowMQ = MQ.StaticMath(leftarrowButton);
	leftarrowMQ.latex('\\leftarrow');

	// rightarrow
	const rightarrowButton = $('#option #rightarrow')[0];
	rightarrowButton.addEventListener('click', function(){
		mathField.cmd('\\rightarrow').focus();
	});
	const rightarrowMQ = MQ.StaticMath(rightarrowButton);
	rightarrowMQ.latex('\\rightarrow');

	// leftrightarrow
	const leftrightarrowButton = $('#option #leftrightarrow')[0];
	leftrightarrowButton.addEventListener('click', function(){
		mathField.cmd('\\leftrightarrow').focus();
	});
	const leftrightarrowMQ = MQ.StaticMath(leftrightarrowButton);
	leftrightarrowMQ.latex('\\leftrightarrow');

	// gamma
	const gammaButton = $('#option #gamma')[0];
	gammaButton.addEventListener('click', function(){
		mathField.cmd('\\gamma').focus();
	});
	const gammaMQ = MQ.StaticMath(gammaButton);
	gammaMQ.latex('\\gamma');

	// delta2
	const delta2Button = $('#option #delta2')[0];
	delta2Button.addEventListener('click', function(){
		mathField.cmd('\\delta').focus();
	});
	const delta2MQ = MQ.StaticMath(delta2Button);
	delta2MQ.latex('\\delta');

	// epsilon
	const epsilonButton = $('#option #epsilon')[0];
	epsilonButton.addEventListener('click', function(){
		mathField.cmd('\\epsilon').focus();
	});
	const epsilonMQ = MQ.StaticMath(epsilonButton);
	epsilonMQ.latex('\\epsilon');

	// zeta
	const zetaButton = $('#option #zeta')[0];
	zetaButton.addEventListener('click', function(){
		mathField.cmd('\\zeta').focus();
	});
	const zetaMQ = MQ.StaticMath(zetaButton);
	zetaMQ.latex('\\zeta');

	// eta
	const etaButton = $('#option #eta')[0];
	etaButton.addEventListener('click', function(){
		mathField.cmd('\\eta').focus();
	});
	const etaMQ = MQ.StaticMath(etaButton);
	etaMQ.latex('\\eta');

	// iota
	const iotaButton = $('#option #iota')[0];
	iotaButton.addEventListener('click', function(){
		mathField.cmd('\\iota').focus();
	});
	const iotaMQ = MQ.StaticMath(iotaButton);
	iotaMQ.latex('\\iota');

	// kappa
	const kappaButton = $('#option #kappa')[0];
	kappaButton.addEventListener('click', function(){
		mathField.cmd('\\kappa').focus();
	});
	const kappaMQ = MQ.StaticMath(kappaButton);
	kappaMQ.latex('\\kappa');

	// lambda
	const lambdaButton = $('#option #lambda')[0];
	lambdaButton.addEventListener('click', function(){
		mathField.cmd('\\lambda').focus();
	});
	const lambdaMQ = MQ.StaticMath(lambdaButton);
	lambdaMQ.latex('\\lambda');

	// mu
	const muButton = $('#option #mu')[0];
	muButton.addEventListener('click', function(){
		mathField.cmd('\\mu').focus();
	});
	const muMQ = MQ.StaticMath(muButton);
	muMQ.latex('\\mu');

	// nu
	const nuButton = $('#option #nu')[0];
	nuButton.addEventListener('click', function(){
		mathField.cmd('\\nu').focus();
	});
	const nuMQ = MQ.StaticMath(nuButton);
	nuMQ.latex('\\nu');

	// xi
	const xiButton = $('#option #xi')[0];
	xiButton.addEventListener('click', function(){
		mathField.cmd('\\xi').focus();
	});
	const xiMQ = MQ.StaticMath(xiButton);
	xiMQ.latex('\\xi');

	// o
	const oButton = $('#option #o')[0];
	oButton.addEventListener('click', function(){
		mathField.cmd('o').focus();
	});
	const oMQ = MQ.StaticMath(oButton);
	oMQ.latex('o');

	// rho
	const rhoButton = $('#option #rho')[0];
	rhoButton.addEventListener('click', function(){
		mathField.cmd('\\rho').focus();
	});
	const rhoMQ = MQ.StaticMath(rhoButton);
	rhoMQ.latex('\\rho');

	// sigma
	const sigmaButton = $('#option #sigma')[0];
	sigmaButton.addEventListener('click', function(){
		mathField.cmd('\\sigma').focus();
	});
	const sigmaMQ = MQ.StaticMath(sigmaButton);
	sigmaMQ.latex('\\sigma');

	// tau
	const tauButton = $('#option #tau')[0];
	tauButton.addEventListener('click', function(){
		mathField.cmd('\\tau').focus();
	});
	const tauMQ = MQ.StaticMath(tauButton);
	tauMQ.latex('\\tau');

	// upsilon
	const upsilonButton = $('#option #upsilon')[0];
	upsilonButton.addEventListener('click', function(){
		mathField.cmd('\\upsilon').focus();
	});
	const upsilonMQ = MQ.StaticMath(upsilonButton);
	upsilonMQ.latex('\\upsilon');

	// phi
	const phiButton = $('#option #phi')[0];
	phiButton.addEventListener('click', function(){
		mathField.cmd('\\phi').focus();
	});
	const phiMQ = MQ.StaticMath(phiButton);
	phiMQ.latex('\\phi');

	// chi
	const chiButton = $('#option #chi')[0];
	chiButton.addEventListener('click', function(){
		mathField.cmd('\\chi').focus();
	});
	const chiMQ = MQ.StaticMath(chiButton);
	chiMQ.latex('\\chi');

	// psi
	const psiButton = $('#option #psi')[0];
	psiButton.addEventListener('click', function(){
		mathField.cmd('\\psi').focus();
	});
	const psiMQ = MQ.StaticMath(psiButton);
	psiMQ.latex('\\psi');

	// omega
	const omegaButton = $('#option #omega')[0];
	omegaButton.addEventListener('click', function(){
		mathField.cmd('\\omega').focus();
	});
	const omegaMQ = MQ.StaticMath(omegaButton);
	omegaMQ.latex('\\omega');

	// gamma2
	const gamma2Button = $('#option #gamma2')[0];
	gamma2Button.addEventListener('click', function(){
		mathField.cmd('\\Gamma').focus();
	});
	const gamma2MQ = MQ.StaticMath(gamma2Button);
	gamma2MQ.latex('\\Gamma');

	// theta2
	const theta2Button = $('#option #theta2')[0];
	theta2Button.addEventListener('click', function(){
		mathField.cmd('\\Theta').focus();
	});
	const theta2MQ = MQ.StaticMath(theta2Button);
	theta2MQ.latex('\\Theta');

	// lambda2
	const lambda2Button = $('#option #lambda2')[0];
	lambda2Button.addEventListener('click', function(){
		mathField.cmd('\\Lambda').focus();
	});
	const lambda2MQ = MQ.StaticMath(lambda2Button);
	lambda2MQ.latex('\\Lambda');

	// xi2
	const xi2Button = $('#option #xi2')[0];
	xi2Button.addEventListener('click', function(){
		mathField.cmd('\\Xi').focus();
	});
	const xi2MQ = MQ.StaticMath(xi2Button);
	xi2MQ.latex('\\Xi');

	// pi2
	const pi2Button = $('#option #pi2')[0];
	pi2Button.addEventListener('click', function(){
		mathField.cmd('\\Pi').focus();
	});
	const pi2MQ = MQ.StaticMath(pi2Button);
	pi2MQ.latex('\\Pi');

	// sigma2
	const sigma2Button = $('#option #sigma2')[0];
	sigma2Button.addEventListener('click', function(){
		mathField.cmd('\\Sigma').focus();
	});
	const sigma2MQ = MQ.StaticMath(sigma2Button);
	sigma2MQ.latex('\\Sigma');

	// upsilon2
	const upsilon2Button = $('#option #upsilon2')[0];
	upsilon2Button.addEventListener('click', function(){
		mathField.cmd('\\Upsilon').focus();
	});
	const upsilon2MQ = MQ.StaticMath(upsilon2Button);
	upsilon2MQ.latex('\\Upsilon');

	// phi2
	const phi2Button = $('#option #phi2')[0];
	phi2Button.addEventListener('click', function(){
		mathField.cmd('\\Phi').focus();
	});
	const phi2MQ = MQ.StaticMath(phi2Button);
	phi2MQ.latex('\\Phi');

	// psi2
	const psi2Button = $('#option #psi2')[0];
	psi2Button.addEventListener('click', function(){
		mathField.cmd('\\Psi').focus();
	});
	const psi2MQ = MQ.StaticMath(psi2Button);
	psi2MQ.latex('\\Psi');

	// omega2
	const omega2Button = $('#option #omega2')[0];
	omega2Button.addEventListener('click', function(){
		mathField.cmd('\\Omega').focus();
	});
	const omega2MQ = MQ.StaticMath(omega2Button);
	omega2MQ.latex('\\Omega');

	// subset
	const subsetButton = $('#option #subset')[0];
	subsetButton.addEventListener('click', function(){
		mathField.cmd('\\subset').focus();
	});
	const subsetMQ = MQ.StaticMath(subsetButton);
	subsetMQ.latex('\\subset');

	// subseteq
	const subseteqButton = $('#option #subseteq')[0];
	subseteqButton.addEventListener('click', function(){
		mathField.cmd('\\subseteq').focus();
	});
	const subseteqMQ = MQ.StaticMath(subseteqButton);
	subseteqMQ.latex('\\subseteq');

	// notsubset
	const notsubsetButton = $('#option #notsubset')[0];
	notsubsetButton.addEventListener('click', function(){
		mathField.cmd('\\nsubset').focus();
	});
	const notsubsetMQ = MQ.StaticMath(notsubsetButton);
	notsubsetMQ.latex('\\nsubset');

	// notsubseteq
	const notsubseteqButton = $('#option #notsubseteq')[0];
	notsubseteqButton.addEventListener('click', function(){
		mathField.cmd('\\nsubseteq').focus();
	});
	const notsubseteqMQ = MQ.StaticMath(notsubseteqButton);
	notsubseteqMQ.latex('\\nsubseteq');

	// supset
	const supsetButton = $('#option #supset')[0];
	supsetButton.addEventListener('click', function(){
		mathField.cmd('\\supset').focus();
	});
	const supsetMQ = MQ.StaticMath(supsetButton);
	supsetMQ.latex('\\supset');

	// supseteq
	const supseteqButton = $('#option #supseteq')[0];
	supseteqButton.addEventListener('click', function(){
		mathField.cmd('\\supseteq').focus();
	});
	const supseteqMQ = MQ.StaticMath(supseteqButton);
	supseteqMQ.latex('\\supseteq');

	// nsupset
	const nsupsetButton = $('#option #notsupset')[0];
	nsupsetButton.addEventListener('click', function(){
		mathField.cmd('\\nsupset').focus();
	});
	const nsupsetMQ = MQ.StaticMath(nsupsetButton);
	nsupsetMQ.latex('\\nsupset');

	// nsupseteq
	const nsupseteqButton = $('#option #notsupseteq')[0];
	nsupseteqButton.addEventListener('click', function(){
		mathField.cmd('\\nsupseteq').focus();
	});
	const nsupseteqMQ = MQ.StaticMath(nsupseteqButton);
	nsupseteqMQ.latex('\\nsupseteq');

	// in
	const inButton = $('#option #in')[0];
	inButton.addEventListener('click', function(){
		mathField.cmd('\\in').focus();
	});
	const inMQ = MQ.StaticMath(inButton);
	inMQ.latex('\\in');

	// ni
	const niButton = $('#option #ni')[0];
	niButton.addEventListener('click', function(){
		mathField.cmd('\\ni').focus();
	});
	const niMQ = MQ.StaticMath(niButton);
	niMQ.latex('\\ni');

	// notin
	const notinButton = $('#option #notin')[0];
	notinButton.addEventListener('click', function(){
		mathField.cmd('\\notin').focus();
	});
	const notinMQ = MQ.StaticMath(notinButton);
	notinMQ.latex('\\notin');

	// notni
	const notniButton = $('#option #notni')[0];
	notniButton.addEventListener('click', function(){
		mathField.cmd('\\notni').focus();
	});
	const notniMQ = MQ.StaticMath(notniButton);
	notniMQ.latex('\\notni');

	// cup
	const cupButton = $('#option #cup')[0];
	cupButton.addEventListener('click', function(){
		mathField.cmd('\\cup').focus();
	});
	const cupMQ = MQ.StaticMath(cupButton);
	cupMQ.latex('\\cup');

	// cap
	const capButton = $('#option #cap')[0];
	capButton.addEventListener('click', function(){
		mathField.cmd('\\cap').focus();
	});
	const capMQ = MQ.StaticMath(capButton);
	capMQ.latex('\\cap');

	// forall
	const forallButton = $('#option #forall')[0];
	forallButton.addEventListener('click', function(){
		mathField.cmd('\\forall').focus();
	});
	const forallMQ = MQ.StaticMath(forallButton);
	forallMQ.latex('\\forall');

	// exists
	const existsButton = $('#option #exists')[0];
	existsButton.addEventListener('click', function(){
		mathField.cmd('\\exists').focus();
	});
	const existsMQ = MQ.StaticMath(existsButton);
	existsMQ.latex('\\exists');

	// vee
	const veeButton = $('#option #vee')[0];
	veeButton.addEventListener('click', function(){
		mathField.cmd('\\vee').focus();
	});
	const veeMQ = MQ.StaticMath(veeButton);
	veeMQ.latex('\\vee');

	// because
	const becauseButton = $('#option #because')[0];
	becauseButton.addEventListener('click', function(){
		mathField.cmd('\\because').focus();
	});
	const becauseMQ = MQ.StaticMath(becauseButton);
	becauseMQ.latex('\\because');

	// therefore
	const thereforeButton = $('#option #therefore')[0];
	thereforeButton.addEventListener('click', function(){
		mathField.cmd('\\therefore').focus();
	});
	const thereforeMQ = MQ.StaticMath(thereforeButton);
	thereforeMQ.latex('\\therefore');

	// Longleftarrow
	const LongleftarrowButton = $('#option #Longleftarrow')[0];
	LongleftarrowButton.addEventListener('click', function(){
		mathField.cmd('\\Longleftarrow').focus();
	});
	const LongleftarrowMQ = MQ.StaticMath(LongleftarrowButton);
	LongleftarrowMQ.latex('\\Longleftarrow');

	// Longrightarrow
	const LongrightarrowButton = $('#option #Longrightarrow')[0];
	LongrightarrowButton.addEventListener('click', function(){
		mathField.cmd('\\Longrightarrow').focus();
	});
	const LongrightarrowMQ = MQ.StaticMath(LongrightarrowButton);
	LongrightarrowMQ.latex('\\Longrightarrow');

	// Longleftrightarrow
	const LongleftrightarrowButton = $('#option #Longleftrightarrow')[0];
	LongleftrightarrowButton.addEventListener('click', function(){
		mathField.cmd('\\Longleftrightarrow').focus();
	});
	const LongleftrightarrowMQ = MQ.StaticMath(LongleftrightarrowButton);
	LongleftrightarrowMQ.latex('\\Longleftrightarrow');

	// uparrow
	const uparrowButton = $('#option #uparrow')[0];
	uparrowButton.addEventListener('click', function(){
		mathField.cmd('\\uparrow').focus();
	});
	const uparrowMQ = MQ.StaticMath(uparrowButton);
	uparrowMQ.latex('\\uparrow');

	// uparrow2
	const uparrow2Button = $('#option #uparrow2')[0];
	uparrow2Button.addEventListener('click', function(){
		mathField.cmd('\\Uparrow').focus();
	});
	const uparrow2MQ = MQ.StaticMath(uparrow2Button);
	uparrow2MQ.latex('\\Uparrow');

	// updownarrow
	const updownarrowButton = $('#option #updownarrow')[0];
	updownarrowButton.addEventListener('click', function(){
		mathField.cmd('\\updownarrow').focus();
	});
	const updownarrowMQ = MQ.StaticMath(updownarrowButton);
	updownarrowMQ.latex('\\updownarrow');

	// downarrow
	const downarrowButton = $('#option #downarrow')[0];
	downarrowButton.addEventListener('click', function(){
		mathField.cmd('\\downarrow').focus();
	});
	const downarrowMQ = MQ.StaticMath(downarrowButton);
	downarrowMQ.latex('\\downarrow');

	// downarrow2
	const downarrow2Button = $('#option #downarrow2')[0];
	downarrow2Button.addEventListener('click', function(){
		mathField.cmd('\\Downarrow').focus();
	});
	const downarrow2MQ = MQ.StaticMath(downarrow2Button);
	downarrow2MQ.latex('\\Downarrow');

	// updownarrow2
	const updownarrow2Button = $('#option #updownarrow2')[0];
	updownarrow2Button.addEventListener('click', function(){
		mathField.cmd('\\Updownarrow').focus();
	});
	const updownarrow2MQ = MQ.StaticMath(updownarrow2Button);
	updownarrow2MQ.latex('\\Updownarrow');

	// ldots
	const ldotsButton = $('#option #ldots')[0];
	ldotsButton.addEventListener('click', function(){
		mathField.cmd('\\ldots').focus();
	});
	const ldotsMQ = MQ.StaticMath(ldotsButton);
	ldotsMQ.latex('\\ldots');

	// cdots
	const cdotsButton = $('#option #cdots')[0];
	cdotsButton.addEventListener('click', function(){
		mathField.cmd('\\cdots').focus();
	});
	const cdotsMQ = MQ.StaticMath(cdotsButton);
	cdotsMQ.latex('\\cdots');
}