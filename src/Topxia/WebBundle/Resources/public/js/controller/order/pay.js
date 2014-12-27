define(function(require, exports, module) {
	
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

	exports.run = function() {
		var cashRateElement = $('[role="cash-rate"]');
		var cashRate = 1;
		if($('[role="cash-rate"]').val() != "")
			cashRate = $('[role="cash-rate"]').val();

		var validator = new Validator({
            element: '#order-create-form',
            triggerType: 'change',
            //autoSubmit: false,
            onFormValidated: function(error){
                if (error) {
                    return false;
                }
                $('#order-create-btn').button('submiting').addClass('disabled');
            }
        });

		function roundUp(amount){
			return (amount*100/100).toFixed(2);
		}

		function afterCouponPay(){
			var totalPrice = parseFloat($('[role="total-price"]').text());
			var couponTotalPrice = $('[role="coupon-price"]').find(".price_r_num").text();
			if($.trim(couponTotalPrice) == "" || isNaN(couponTotalPrice)){
				couponTotalPrice = 0;
			} else {
				couponTotalPrice = parseFloat(couponTotalPrice);
			}
			totalPrice = totalPrice-couponTotalPrice;

			return totalPrice;
		}

		function afterCoinPay(totalPrice, coinNum){
			var accountCash = $('[role="accountCash"]').text();
			coinNum = parseFloat(coinNum);
			if(accountCash>coinNum) {
				coinNum = coinNum.toFixed(2);
				if(cashRateElement.data("coursePriceShowType") == "RMB"){
					var cashDiscount = coinNum/cashRate;
					$('[role="cash-discount"]').text(cashDiscount.toFixed(2));
				}else{
					$('[role="cash-discount"]').text(coinNum);
				}
				$('[role="coinNum"]').val(coinNum);
			} else {
				accountCash = accountCash.toFixed(2);
				if(cashRateElement.data("coursePriceShowType") == "RMB"){
					var cashDiscount = accountCash/cashRate;
					$('[role="cash-discount"]').text(cashDiscount.toFixed(2));
				}else{
					$('[role="cash-discount"]').text(accountCash);
				}
				$('[role="coinNum"]').val(accountCash);
			}
		}

		function conculatePrice(){
			var totalPrice = afterCouponPay();
			if(totalPrice <= 0){
				totalPrice = 0;
				coinPriceZero();
				$('[role="pay-coin"]').text(0);
				$('[role="pay-rmb"]').text(0);
			} else {
				var coinNum = 0;
				if(cashRateElement.data("coursePriceShowType") == "RMB") {
					coinNum = totalPrice*cashRate;
				} else {
					coinNum = totalPrice;
				}
				var coinNumPay = $('[role="coinNum"]').val();
				if(coinNum<=coinNumPay) {
					afterCoinPay(totalPrice, coinNum);
				} else {
					afterCoinPay(totalPrice, coinNumPay);
				}
				var cashDiscount = $('[role="cash-discount"]').text();
				totalPrice = totalPrice-cashDiscount;
			}

			totalPrice = totalPrice.toFixed(2);

			if(cashRateElement.data("coursePriceShowType") == "RMB") {
				var payCoin = totalPrice*cashRate;
				$('[role="pay-coin"]').text(payCoin.toFixed(2));
				$('[role="pay-rmb"]').text(totalPrice);
				$('input[name="shouldPayMoney"]').val(totalPrice);
			} else {
				var payRmb = totalPrice/cashRate;
				$('[role="pay-coin"]').text(totalPrice);
				$('[role="pay-rmb"]').text(payRmb.toFixed(2));
				$('input[name="shouldPayMoney"]').val(payRmb.toFixed(2));
			}
		}

		function coinPriceZero(){
			$('[role="coinNum"]').val(0);
			$('[role="cash-discount"]').text("0");
			$(".pay-password div[role='password-input']").hide();
			validator.removeItem('[name="payPassword"]');
		}

		

		$('[role="coinNum"]').blur(function(e){
			var coinNum = $(this).val();
			if(isNaN(coinNum) || coinNum<=0){
				$(this).val(0);
				coinPriceZero();
			} else {
				$(".pay-password div[role='password-input']").show();
				validator.addItem({
					element: '[name="payPassword"]',
					required: true,
        			rule: 'remote'
				});
			}
			conculatePrice();

		});

		$("#coupon-code-btn").click(function(e){
			$('[role="coupon-code"]').show().focus();
			$('[role="no-use-coupon-code"]').hide();
			$('[role="cancel-coupon"]').show();
			$('[role="code-notify"]').show();
			$(this).hide();
		})

		$('[role="cancel-coupon"]').click(function(e){
			$('[role="coupon-code"]').hide();
			$('[role="no-use-coupon-code"]').show();
			$("#coupon-code-btn").show();
			$('[role="code-notify"]').hide();
			$('[role="coupon-price"]').find(".price_r_num").text(0);
			$('[role="code-notify"]').text("");
			$('[role="coupon-code"]').val("");
			$(this).hide();
			conculatePrice();
		});

		$('[role="coupon-code"]').blur(function(e){
			var data={};
			data.code = $(this).val();
			if(data.code == ""){
				return;
			}
			data.targetType = "course";
			data.targetId = $(this).data("targetId");
			data.amount = $(this).data("amount");
			
			$.post('/course/'+data.targetId+'/coupon/check', data, function(data){
				if(data.useable == "no") {
					$('[role="code-notify"]').css("color","red").text(data.message);
				} else if(data.useable == "yes"){
					$('[role="code-notify"]').css("color","green").text("优惠码可用");
					if(cashRateElement.data("coursePriceShowType") == "RMB") {
						$('[role="coupon-price"]').find(".price_r_num").text(data.decreaseAmount);
					} else {
						var coinPrice = data.decreaseAmount*cashRate;
						var totalPrice = parseFloat($('[role="total-price"]').text());
						if(totalPrice < coinPrice){
							coinPrice = totalPrice;
						}
						$('[role="coupon-price"]').find(".price_r_num").text(coinPrice);
					}
				}
				conculatePrice();
			})
		})
 		
 		if($('[role="coinNum"]').length>0) {
 			$('[role="coinNum"]').blur();
 		}else{
 			conculatePrice();
 		}
	}
});