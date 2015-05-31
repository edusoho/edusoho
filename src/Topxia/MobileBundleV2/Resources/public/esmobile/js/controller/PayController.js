app.controller('CoursePayController', ['$scope', '$rootScope', CoursePayController]);
app.controller('CourseCouponController', ['$scope', 'CouponService', '$stateParams', CourseCouponController]);

function CourseCouponController($scope, CouponService, $stateParams)
{
	$scope.checkCoupon = function() {
		$scope.showLoad();
		CouponService.checkCoupon({
			courseId : $stateParams.courseId,
			type : "course",
			code : $scope.code
		}, function(data) {
			$scope.hideLoad();
		}, function(data) {
			$scope.hideLoad();
			$scope.toast("检验优惠码错误");
		});
	}
}

function CoursePayController($scope, $rootScope)
{
	var params = $rootScope.stateParams["coursePay"];
	$scope.course = params ? params.course : {};

	$scope.payCourseByAlipay = function() {

	}
}
