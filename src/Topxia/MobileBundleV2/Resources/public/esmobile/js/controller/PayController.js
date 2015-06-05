app.controller('CoursePayController', ['$scope', '$stateParams', 'ServcieUtil', CoursePayController]);
app.controller('CourseCouponController', ['$scope', 'CouponService', '$stateParams', '$ionicHistory', CourseCouponController]);
app.controller('VipListController', ['$scope', '$stateParams', 'SchoolService', VipListController]);
app.controller('VipPayController', ['$scope', '$stateParams', 'SchoolService', '$ionicPopover', 'VipUtil', VipPayController]);


function VipPayController($scope, $stateParams, SchoolService, $ionicPopover, VipUtil)
{
	$scope.showLoad();
	SchoolService.getVipPayInfo({
		levelId : $stateParams.levelId,
		token : $scope.token
	}, function(data) {
		$scope.hideLoad();
		$scope.data = data.data;
		$scope.payModes = VipUtil.getPayMode(data.data.buyType);
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

		var template = '<ion-popover-view> <ion-pane><div class="list">' +
		'<a class="item" ng-repeat="mode in payModes" ng-click="selectPayMode(mode)">{{ mode.title }}</a>' +
		'</div></ion-pane></ion-popover-view>';

		  $scope.popover = $ionicPopover.fromTemplate(template, {
		    scope: $scope
		  });

		  $scope.openPopover = function($event) {
		  	$scope.popover.modalEl.style.width = $event.srcElement.clientWidth + "px";
		  	$scope.popover.modalEl.style.height = ($event.srcElement.clientHeight * $scope.payModes.length) + "px";
		    	$scope.popover.show($event);
		  };
		  $scope.closePopover = function() {
		    $scope.popover.hide();
		  };

		  $scope.$on('$destroy', function() {
		    $scope.popover.remove();
		  });

		  $scope.selectPayMode = function(payMode) {
		  	$scope.selectedPayMode = payMode;
			$scope.sumTotalPirce();
		  	$scope.popover.hide();
		  }
	}	

}

function VipListController($scope, $stateParams, SchoolService)
{
	SchoolService.getSchoolVipList({
		userId : $scope.user.id
	}, function(data) {
		console.log(data);
		$scope.data = data.data;
	});
}

function CourseCouponController($scope, CouponService, $stateParams, $ionicHistory)
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
			$ionicHistory.goBack();
			$scope.$emit("coupon", { coupon : data.data });
		}, function(data) {
			$scope.hideLoad();
			$scope.toast("检验优惠码错误");
		});
	}
}

function CoursePayController($scope, $stateParams, ServcieUtil)
{
	ServcieUtil.getService("OrderService").getPayOrder({
		courseId : $stateParams.courseId,
		token : $scope.token
	}, function(data) {
		$scope.data = data;
	});

	$scope.$parent.$on("coupon", function(event, data) {
		$scope.coupon = data.coupon;
	});

	$scope.pay = function() {
		var CourseService = ServcieUtil.getService("CourseService");
		ServcieUtil.getService("OrderService").payCourse({
			courseId : $stateParams.courseId,
        			token : $scope.token
		}, function(data) {
			console.log(data);
			if (data.status == "ok" && data.payUrl != "") {
				window.location.href = data.payUrl;
			}
		});
	}
}
