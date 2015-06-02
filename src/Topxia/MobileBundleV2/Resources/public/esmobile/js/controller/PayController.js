app.controller('CoursePayController', ['$scope', '$stateParams', 'ServcieUtil', CoursePayController]);
app.controller('CourseCouponController', ['$scope', 'CouponService', '$stateParams', '$ionicHistory', CourseCouponController]);
app.controller('VipListController', ['$scope', '$stateParams', 'SchoolService', VipListController]);

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
