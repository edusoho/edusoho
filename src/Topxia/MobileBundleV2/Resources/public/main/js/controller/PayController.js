app.controller('CoursePayController', ['$scope', '$stateParams', 'ServcieUtil', 'AppUtil', CoursePayController]);
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
		levelId : $stateParams.levelId,
		token : $scope.token
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
			token : $scope.token,
			duration : $scope.selectedNum,
			unitType : $scope.selectedPayMode.name
		}, function(data) {
			if (data.status == "ok" && data.payUrl != "") {
				cordovaUtil.pay("支付会员", data.payUrl);
				self.showPayResultDlg();
			}
		});
	}

}

function VipListController($scope, $stateParams, SchoolService)
{
	SchoolService.getSchoolVipList({
		userId : $scope.user.id
	}, function(data) {
		$scope.data = data;
	});
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

function CoursePayController($scope, $stateParams, ServcieUtil, AppUtil, cordovaUtil)
{	
	var self = this;
	this.__proto__ = new BasePayController();
	
	ServcieUtil.getService("OrderService").getPayOrder({
		courseId : $stateParams.courseId,
		token : $scope.token
	}, function(data) {
		$scope.$apply(function() {
			$scope.data = data;
		});
	});

	$scope.$parent.$on("coupon", function(event, data) {
		$scope.$apply(function() {
			$scope.coupon = data.coupon;
		});
	});

	$scope.selectCoupon = function() {
		$scope.formData = { code : "", error : '' };
		self.dialog = $(".ui-dialog");
		self.dialog.dialog("show");
	}

	$scope.pay = function() {
		var CourseService = ServcieUtil.getService("CourseService");
		ServcieUtil.getService("OrderService").payCourse({
			courseId : $stateParams.courseId,
        			token : $scope.token
		}, function(data) {
			if (data.status == "ok" && data.payUrl != "") {
				cordovaUtil.pay("支付课程", data.payUrl);
				self.showPayResultDlg();
			}
		});
	}

	$scope.checkCoupon = function() {
		$scope.showLoad();
		ServcieUtil.getService("CouponService").checkCoupon({
			courseId : $stateParams.courseId,
			type : "course",
			code : $scope.formData.code
		}, function(data) {
			$scope.hideLoad();
			if (data.error) {
				$scope.$apply(function() {
					$scope.formData.error = data.error.message;
				});
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
}
