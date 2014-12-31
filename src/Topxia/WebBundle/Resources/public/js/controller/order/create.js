define(function(require, exports, module) {
	
	var Validator = require('bootstrap.validator');
	require('common/validator-rules').inject(Validator);

	exports.run = function() {
		var cashRateElement = $('[role="cash-rate"]');
		var cashRate = 1;
		if($('[role="cash-rate"]').val() != ""){
			cashRate = $('[role="cash-rate"]').val();
			cashRate = parseInt(cashRate*100)/100;
		}
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
			var couponTotalPrice = $('[role="coupon-price"]').find("[role='price']").text();
			if($.trim(couponTotalPrice) == "" || isNaN(couponTotalPrice)){
				couponTotalPrice = 0;
			} else {
				couponTotalPrice = parseFloat(couponTotalPrice);
			}
			totalPrice = totalPrice-couponTotalPrice;
			return totalPrice;
		}

		function afterCoinPay(coinNum){
			var accountCash = $('[role="accountCash"]').text();
			if(accountCash == "" || isNaN(accountCash) || parseFloat(accountCash) == 0) {
				return;
			}
			accountCash = parseFloat(accountCash);
			coinNum = parseFloat(coinNum);

			var coin = accountCash>coinNum ? coinNum : accountCash;
			var fixedCoin = coin.toFixed(2);
			if(coin > fixedCoin) {
				coin=parseFloat(fixedCoin)+0.01;
			} else {
				coin = fixedCoin;
			}

			if(cashRateElement.data("coursePriceShowType") == "RMB"){
				var cashDiscount = (coin*100)/(cashRate*100);
				cashDiscount = parseFloat(cashDiscount*100)/100;
				var fixtedCashDiscount = parseFloat(cashDiscount.toFixed(2));
				if(Math.abs(cashDiscount-fixtedCashDiscount)>0.01) {
					cashDiscount=cashDiscount+0.01;
				} else {
					cashDiscount = parseInt(cashDiscount*100)/100;
				}
				$('[role="cash-discount"]').text(cashDiscount);
			}else{
				$('[role="cash-discount"]').text(coin);
			}
			$('[role="coinNum"]').val(coin);
			
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

				if(coinNumPay && $('[name="payPassword"]').length>0){
					if(coinNum <= coinNumPay) {
						afterCoinPay(coinNum);
					} else {
						afterCoinPay(coinNumPay);
					}
					var cashDiscount = $('[role="cash-discount"]').text();
					totalPrice = totalPrice-cashDiscount;
				} else {
					$('[role="cash-discount"]').text("0.00");
				}
			}

			shouldPay(totalPrice);
		}

		function shouldPay(totalPrice){
			if(cashRateElement.data("coursePriceShowType") == "RMB") {
				var shouldPayMoney = parseFloat(totalPrice.toFixed(2));
				if(Math.abs(totalPrice-shouldPayMoney)>0.01) {
					shouldPayMoney=shouldPayMoney+0.01;
				}
				$('[role="pay-rmb"]').text(shouldPayMoney);
				$('input[name="shouldPayMoney"]').val(shouldPayMoney);
			} else {
				var payRmb = totalPrice/cashRate;
				var shouldPayMoney = parseFloat(payRmb.toFixed(2));
				if(Math.abs(payRmb-shouldPayMoney)>0.01) {
					shouldPayMoney=shouldPayMoney+0.01;
				}
				$('[role="pay-coin"]').text(totalPrice);
				$('[role="pay-rmb"]').text(shouldPayMoney);
				$('input[name="shouldPayMoney"]').val(shouldPayMoney);
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
			$('[role="coupon-price"]').find("[role='price']").text(0);
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
						var couponPrice = parseFloat(data.decreaseAmount).toFixed(2);
						$('[role="coupon-price"]').find("[role='price']").text(couponPrice);
					} else {
						var coinPrice = data.decreaseAmount*cashRate;
						var totalPrice = parseFloat($('[role="total-price"]').text());
						if(totalPrice < coinPrice){
							coinPrice = totalPrice;
						}
						coinPrice = parseFloat(coinPrice).toFixed(2);
						$('[role="coupon-price"]').find("[role='price']").text(coinPrice);
					}
				}
				conculatePrice();
			})
		})

 		var totalPrice = parseFloat($('[role="total-price"]').text());
 		if($('[role="coinNum"]').length>0) {
 			var coinNum = $('[role="coinNum"]').val();
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
 			var discount = Math.floor(coinNum/cashRate*100)/100;
 			$('[role="cash-discount"]').text(discount);
 			totalPrice = totalPrice-discount;
 		}
 		shouldPay(totalPrice);
	}
});