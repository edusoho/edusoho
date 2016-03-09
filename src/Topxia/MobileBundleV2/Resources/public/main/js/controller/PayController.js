app.controller('CourseCouponController', ['$scope', 'CouponService', '$stateParams', '$window', CourseCouponController]);
app.controller('VipListController', ['$scope', '$stateParams', 'SchoolService', 'cordovaUtil', VipListController]);
app.controller('VipPayController', ['$scope', '$stateParams', 'SchoolService', 'VipUtil', 'OrderService', 'cordovaUtil', 'platformUtil', VipPayController]);

function BasePayController($scope, $stateParams, OrderService, cordovaUtil, platformUtil)
{
	var self = this;
	$scope.priceType = "RMB";
	$scope.payMode = "alipay";

	this.showPayResultDlg = function() {
		var dia = $.dialog({
		        title : '确认支付' ,
		        content : '是否支付完成?' ,
		        button : [ "确认" ,"取消" ]
		});

		dia.on("dialog:action",function(e){
		        if (e.index == 0) {
		        	window.history.back();
		        }
		});
	}

	this.initPayMode = function(data) {
		$scope.coin = data.coin;
		if (data.coin && data.coin.priceType) {
			$scope.priceType = data.coin.priceType;
			$scope.payMode = ($scope.checkIsCoinMode() || "Coin" == $scope.priceType) ? "coin" : "alipay";
		}
		$scope.orderLabel = self.getOrderLabel($stateParams.targetType);
	}

	$scope.checkIsCoinMode = function() {
		return false;
	}

	$scope.changePayMode = function() {
		if ("Coin" == $scope.priceType) {
			return;
		}

		if ($scope.payMode == "coin") {
			$scope.payMode = "alipay";
		} else {
			$scope.payMode = "coin";
		}

		self.changePrice($scope.payMode);
	}

	this.showErrorResultDlg = function(error) {
		if ("coin_no_enough" == error.name) {
			var buttons = platformUtil.android ? [ "确认" ] : [ "确认" ,"充值" ];
			var dia = $.dialog({
			        title : '支付提醒' ,
			        content : '账户余额不足!' ,
			        button : [ "确认" ]
			});

			dia.on("dialog:action",function(e){
			        if (e.index == 1) {
			        	cordovaUtil.startAppView("rechargeCoin", null);
			        }
			});
			return;
		}
		$scope.toast(error.message);
	}

	this.getOrderLabel  = function(type) {
		switch(type) {
			case 'course':
				return "购买课程";
			case 'vip':
				return "购买会员";
			case 'classroom':
				return "购买班级";
		}

		return "";
	}

	this.payOrder = function(price, params, payPassword) {

		var payment = $scope.payMode;
		var defaultParams = {
			payment : payment,
			payPassword : payPassword ? payPassword : "",
			totalPrice : price,
			couponCode : $scope.formData ? $scope.formData.code : "",
			targetType : $stateParams.targetType,
			targetId : $stateParams.targetId
		};

		for(var i in params) {
			defaultParams[i] = params[i];
		}

		OrderService.createOrder(defaultParams, function(data) {
			if (data.status != "ok") {
				if (data.error) {
					self.showErrorResultDlg(data.error);
					return
				}
				self.showErrorResultDlg({
					name : "error",
					message : data.message
				});
				return;
			}

			if (data.paid == true) {
				window.history.back();
			} else if (data.payUrl != "") {
				cordovaUtil.pay($scope.orderLabel, data.payUrl);
				self.showPayResultDlg();
			}
		});
	};

	this.submitToPay = function(price, params) {
		if ($scope.payMode == "coin") {
			cordovaUtil.showInput("支付提醒", "请输入支付密码", "password", function(input) {
				if (!input || input.length == 0) {
					alert("请输入支付密码!");
					return;
				}
				self.payOrder(price, params, input);
			});
			return;
		}

		self.payOrder(price, params);
	}
}

function VipPayController($scope, $stateParams, SchoolService, VipUtil, OrderService, cordovaUtil, platformUtil)
{
	var self = this;
	$stateParams.targetType = "vip";
	$stateParams.targetId = $stateParams.levelId;
	this.__proto__ = new BasePayController($scope, $stateParams, OrderService, cordovaUtil, platformUtil);
	
	$scope.loadPayOrder = function() {
		$scope.showLoad();
		OrderService.getPayOrder({
			targetType : 'vip',
			targetId : $stateParams.levelId
		}, function(data) {
			$scope.data = data.orderInfo;
			self.initPayMode(data);

			$scope.payModes = VipUtil.getPayMode(data.orderInfo.buyType);
			$scope.selectedNum = 1;
			$scope.selectedPayMode = $scope.payModes[0];

			self.changePrice($scope.payMode);
			$scope.totalPayPrice = self.sumTotalPirce();
			$scope.initPopver();
			$scope.hideLoad();
		});
	}
	
	this.changePrice = function(payMode) {
		var price = self.sumTotalPirce();
		if ($scope.coin && "Coin" != $scope.priceType && payMode == "coin") {
			price = price * $scope.coin.cashRate;
		}
		var couponPrice = $scope.coupon ? $scope.coupon.decreaseAmount : 0;
		$scope.totalPayPrice = price > couponPrice ? price - couponPrice : 0;
	}

	$scope.changePayMode = function() {
		if ("Coin" == $scope.priceType) {
			return;
		}

		if ($scope.payMode == "coin") {
			$scope.payMode = "alipay";
		} else {
			$scope.payMode = "coin";
		}

		self.changePrice($scope.payMode);
	}

	this.sumTotalPirce = function() {
		var level = $scope.data.level;
		var payTypes = VipUtil.getPayType();

		var price = $scope.selectedPayMode.type == payTypes.byMonth ? level.monthPrice : level.yearPrice;
		var totalPayPrice = $scope.selectedNum * price;
		return totalPayPrice;
	}

	$scope.add = function() {
		if ($scope.selectedNum < 12) {
			$scope.selectedNum ++;
			$scope.totalPayPrice = self.sumTotalPirce();
			self.changePrice($scope.payMode);
		}
	}

	$scope.sub = function() {
		if ($scope.selectedNum > 1) {
			$scope.selectedNum --;
			$scope.totalPayPrice = self.sumTotalPirce();
			self.changePrice($scope.payMode);
		}
	}

	$scope.initPopver = function() {

		  $scope.showPopover = function($event) {
		  	$scope.isShowPayMode = ! $scope.isShowPayMode ;
		  };

		  $scope.selectPayMode = function(payMode) {
		  	$scope.selectedPayMode = payMode;
			$scope.totalPayPrice = self.sumTotalPirce();
		  	$scope.isShowPayMode = false;
		  }
	}

	$scope.pay = function() {
		self.submitToPay($scope.totalPayPrice, {
			duration : $scope.selectedNum,
			unitType : $scope.selectedPayMode.name
		});
	}

	$scope.payVip = function() {
		OrderService.payVip({
			targetId : $stateParams.levelId,
			duration : $scope.selectedNum,
			unitType : $scope.selectedPayMode.name
		}, function(data) {
			if (data.status == "ok" && data.payUrl != "") {
				cordovaUtil.pay("支付会员", data.payUrl);
				self.showPayResultDlg();
			} else if (data.error) {
				$scope.toast(data.error.message);
			}
		});
	}

}

function VipListController($scope, $stateParams, SchoolService, cordovaUtil)
{
	var user = null;
	
	$scope.loadVipList = function() {
		$scope.showLoad();
		SchoolService.getSchoolVipList({
			userId : $scope.user.id
		}, function(data) {
			$scope.hideLoad();
			if (! data || !data.vips || data.vips.length == 0) {
				var dia = $.dialog({
			        title : '会员提醒' ,
			        content : '网校尚未开启Vip服务!' ,
			        button : [ "退出" ]
				});

				dia.on("dialog:action",function(e){
					cordovaUtil.closeWebView();
				});
			}
			$scope.data = data;
			user = data.user;
		});
	}

	$scope.getVipName = function() {
		if (!$scope.data) {
			return "";
		}

		if (!user || !user.vip) {
			return "暂时还不是会员";
		}
		var levelId = user.vip.levelId;
		var vips = $scope.data.vips;
		if (levelId <= 0) {
			return "暂时还不是会员";
		}
		for (var i = 0; i < vips.length; i++) {
			if (levelId == vips[i].id) {
				return vips[i].name;
			}
		};

		return "暂时还不是会员";
	}
}

function CourseCouponController($scope, CouponService, $stateParams, $window)
{	
	$scope.formData = { code : "" };
	$scope.checkCoupon = function() {
		$scope.formData.error = "";
		$scope.showLoad();
		CouponService.checkCoupon({
			courseId : $stateParams.courseId,
			type : "course",
			code : $scope.formData.code
		}, function(data) {
			$scope.hideLoad();
			if (data.meta.code != 200) {
				$scope.formData.error = data.meta.message;
				return;
			}
			$window.history.back();
			$scope.$emit("coupon", { coupon : data.data });
		}, function(data) {
			$scope.hideLoad();
			$scope.toast("检验优惠码错误");
		});
	}
}

app.controller('CoursePayController', ['$scope', '$stateParams', 'OrderService', 'CouponService', 'AppUtil', 'cordovaUtil', 'platformUtil', CoursePayController]);
function CoursePayController($scope, $stateParams, OrderService, CouponService, AppUtil, cordovaUtil, platformUtil)
{	
	var self = this;
	this.__proto__ = new BasePayController($scope, $stateParams, OrderService, cordovaUtil, platformUtil);

	this.loadOrder = function() {
		OrderService.getPayOrder({
			targetType : $stateParams.targetType,
			targetId : $stateParams.targetId
		}, function(data) {
			$scope.data = data;
			self.initPayMode(data);
			self.changePrice($scope.payMode);
		});
	};

	$scope.$parent.$on("coupon", function(event, data) {
		$scope.coupon = data.coupon;
	});

	this.changePrice = function(payMode) {
		var price = $scope.data.orderInfo.price;
		if ($scope.coin && "Coin" != $scope.priceType && payMode == "coin") {
			price = price * $scope.coin.cashRate;
		}
		var couponPrice = $scope.coupon ? $scope.coupon.decreaseAmount : 0;
		$scope.payPrice = price > couponPrice ? price - couponPrice : 0;
	}

	$scope.selectCoupon = function() {
		$scope.formData = { code : "", error : '' };
		self.dialog = $(".ui-dialog");
		self.dialog.dialog("show");
	}

	$scope.changePayMode = function() {
		if ("Coin" == $scope.priceType) {
			return;
		}

		if ($scope.payMode == "coin") {
			$scope.payMode = "alipay";
		} else {
			$scope.payMode = "coin";
		}

		self.changePrice($scope.payMode);
	}

	$scope.pay = function() {
		self.submitToPay($scope.data.orderInfo.price, null);
	}

	$scope.checkCoupon = function() {
		if ($scope.formData.code.length <= 0) {
			alert("请输入优惠码");
			return;
		}

		$scope.showLoad();
		CouponService.checkCoupon({
			courseId : $stateParams.courseId,
			type : "course",
			code : $scope.formData.code
		}, function(data) {
			$scope.hideLoad();
			if (data.error) {
				$scope.formData.error = data.error.message;
				return;
			}
			$scope.$emit("coupon", { coupon : data });
			$scope.close();

		}, function(data) {
			$scope.hideLoad();
			$scope.toast("检验优惠码错误");
		});
	}

	$scope.close = function() {
		self.dialog.dialog("hide");
	}

	$scope.isShowCoupon = function() {
		if (platformUtil.native && (platformUtil.iPhone || platformUtil.iPad)) {
			return false;
		}
		if ($scope.data && $scope.data.isInstalledCoupon) {
			return true;
		}
		return false;
	};

	self.loadOrder();
}
