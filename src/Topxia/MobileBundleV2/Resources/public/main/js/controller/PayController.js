app.controller('CourseCouponController', ['$scope', 'CouponService', '$stateParams', '$window', CourseCouponController]);
app.controller('VipListController', ['$scope', '$stateParams', 'SchoolService', VipListController]);
app.controller('VipPayController', ['$scope', '$stateParams', 'SchoolService', 'VipUtil', 'OrderService', 'cordovaUtil', VipPayController]);

function BasePayController()
{
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
}

function VipPayController($scope, $stateParams, SchoolService, VipUtil, OrderService, cordovaUtil)
{
	var self = this;
	this.__proto__ = new BasePayController();

	$scope.showLoad();
	SchoolService.getVipPayInfo({
		levelId : $stateParams.levelId
	}, function(data) {
		$scope.hideLoad();
		$scope.data = data;
		$scope.payModes = VipUtil.getPayMode(data.buyType);
		$scope.selectedNum = 1;
		$scope.selectedPayMode = $scope.payModes[0];

		$scope.sumTotalPirce();
		$scope.initPopver();
	});
	
	$scope.sumTotalPirce = function() {
		var level = $scope.data.level;
		var payTypes = VipUtil.getPayType();

		var price = $scope.selectedPayMode.type == payTypes.byMonth ? level.monthPrice : level.yearPrice;
		$scope.totalPayPrice = $scope.selectedNum * price;
	}

	$scope.add = function() {
		if ($scope.selectedNum < 12) {
			$scope.selectedNum ++;
			$scope.sumTotalPirce();
		}
	}

	$scope.sub = function() {
		if ($scope.selectedNum > 1) {
			$scope.selectedNum --;
			$scope.sumTotalPirce();
		}
	}

	$scope.initPopver = function() {

		  $scope.showPopover = function($event) {
		  	$scope.isShowPayMode = ! $scope.isShowPayMode ;
		  };

		  $scope.selectPayMode = function(payMode) {
		  	$scope.selectedPayMode = payMode;
			$scope.sumTotalPirce();
		  	$scope.isShowPayMode = false;
		  }
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

function VipListController($scope, $stateParams, SchoolService)
{
	var user = null;
	
	SchoolService.getSchoolVipList({
		userId : $scope.user.id
	}, function(data) {
		$scope.data = data;
		user = data.user;
	});

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

app.controller('CoursePayController', ['$scope', '$stateParams', 'OrderService', 'CouponService', 'AppUtil', 'cordovaUtil', CoursePayController]);
function CoursePayController($scope, $stateParams, OrderService, CouponService, AppUtil, cordovaUtil)
{	
	var self = this;
	this.__proto__ = new BasePayController();

	$scope.priceType = "RMB";
	$scope.payMode = "alipay";

	this.loadOrder = function() {
		OrderService.getPayOrder({
			targetType : $stateParams.targetType,
			targetId : $stateParams.targetId
		}, function(data) {
			$scope.data = data;
			$scope.coin = data.coin;
			if (data.coin && data.coin.priceType) {
				$scope.priceType = data.coin.priceType;
				$scope.payMode = "Coin" == $scope.priceType ? "coin" : "alipay";
			}
			self.changePrice($scope.payMode);
			$scope.orderLabel = self.getOrderLabel($stateParams.targetType);
		});
	};

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

	this.payOrder = function(payPassword) {

		var payment = $scope.payMode;
		OrderService.createOrder({
			payment : payment,
			payPassword : payPassword ? payPassword : "",
			totalPrice : $scope.data.orderInfo.price,
			couponCode : $scope.formData ? $scope.formData.code : "",
			targetType : $stateParams.targetType,
			targetId : $stateParams.targetId
		}, function(data) {
			if (data.status != "ok") {
				$scope.toast(data.error.message);
				return;
			}

			if (data.paid == true) {
				window.history.back();
			} else if (data.payUrl != "") {
				cordovaUtil.pay("支付课程", data.payUrl);
				self.showPayResultDlg();
			}
		});
	};

	$scope.pay = function() {
		if ($scope.payMode == "coin") {
			cordovaUtil.showInput("支付提醒", "请输入支付密码", "password", function(input) {
				if (!input || input.length == 0) {
					alert("请输入支付密码!");
					return;
				}
				self.payOrder(input);
			});
			return;
		}
		self.payOrder();
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

	self.loadOrder();
}
