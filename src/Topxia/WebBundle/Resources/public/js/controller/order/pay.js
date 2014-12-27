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
			return Math.ceil(amount*100)/100;
		}

		function conculatePrice(){
			var totalPrice = parseFloat($('[role="total-price"]').text());
			var couponTotalPrice = $('[role="coupon-price"]').find(".price_r_num").text();
			if($.trim(couponTotalPrice) == "" || isNaN(couponTotalPrice)){
				couponTotalPrice = 0;
			}
			var couponAmount = parseFloat(couponTotalPrice);
			var payAmount = totalPrice-couponAmount;
			if(payAmount <= 0){
				payAmount = 0;
				coinPriceZero();
			}

			var totalPrice = payAmount;
			if(payAmount>0) {
				var coinAmount = parseFloat($('[role="cash-discount"]').text());
				if(!isNaN(coinAmount)) {
					payAmount = payAmount-coinAmount;
				}
				if(payAmount < 0){
					payAmount = totalPrice;
					if(cashRateElement.data("coursePriceShowType") == "Coin") {
						$('[role="coinNum"]').val(payAmount);
					}else{
						$('[role="coinNum"]').val(payAmount*cashRate);
					}
					$('[role="cash-discount"]').text(payAmount);
					payAmount = 0;
				}
			}

			if(cashRateElement.data("coursePriceShowType") == "Coin") {
				if(payAmount == 0){
					$('[role="pay-coin"]').text(0);
				} else {
					$('[role="pay-coin"]').text(roundUp(payAmount));
				}

				payAmount = payAmount/cashRate;
			}

			$('[role="pay-rmb"]').text(payAmount.toFixed(2));
			$('input[name="shouldPayMoney"]').val(payAmount.toFixed(2));
		}

		function coinPriceZero(){
			$('[role="coinNum"]').val(0);
			$('[role="cash-discount"]').text("0");
			$(".pay-password div[role='password-input']").hide();
			validator.removeItem('[name="payPassword"]');
		}

		function calculatorCoinPay(){
			var coin = $('[role="coinNum"]').val();

			validator.removeItem('[name="payPassword"]');

			if(isNaN(coin) || parseFloat(coin) <= 0){
				coinPriceZero();
			} else {
				$(".pay-password div[role='password-input']").show();
				validator.addItem({
				    element: '[name="payPassword"]',
				    rule: 'remote',
				    required : true
				});

				var cash = $('[role="accountCash"]').text();
				var discount = 0;
				if(parseFloat(cash) < parseFloat(coin)) {
					$('[role="coinNum"]').val(cash);
					if(cashRateElement.data("coursePriceShowType") != "Coin"){
						discount = cash/cashRate;
					} else {
						discount = cash;
					}
				} else {
					if(cashRateElement.data("coursePriceShowType") != "Coin"){
						discount = coin/cashRate;
					} else {
						discount = coin;
					}
				}

				var discountArray = (discount+"").split(".");
				if (discountArray.length>1 && discountArray[1].length>2) {
					coinPriceZero();
				} else {
					$('[role="cash-discount"]').text(discount);
				}
			}
		}

		$('[role="coinNum"]').blur(function(e){
			calculatorCoinPay();
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
			$('[role="coupon-price"]').find(".price_r_num").text("0");
			$('[role="code-notify"]').text("");
			$('[role="coupon-code"]').val("");
			$(this).hide();
			conculatePrice();
		});

		function shouldPayCoin() {
			var totalPrice = $("[role='total-price']").text();
			var couponPrice = $("[role='coupon-price']").find(".price_r_num").text();
			var coinPrice = parseFloat(totalPrice) - parseFloat(couponPrice);
			coinPrice = coinPrice.toFixed(2);

			var shouldPayCoin = 0;
			if(cashRateElement.data("coursePriceShowType") == "RMB") {
				shouldPayCoin = coinPrice*cashRate;
			} else {
				shouldPayCoin = coinPrice;
			}
			var cash = parseFloat($("[role='accountCash']").text());
			if(cash >= shouldPayCoin) {
				return shouldPayCoin;
			} else {
				return cash;
			}
		}

		function getCoinImpledgePrice(coinNum) {
			if(cashRateElement.data("coursePriceShowType") == "RMB") {
				return coinNum/cashRate;
			} else {
				return coinNum;
			}
		}

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

					var coinNum = shouldPayCoin();
					var coinImpledgePrice = getCoinImpledgePrice(coinNum);
					$("[role='coinNum']").val(coinNum);
					$("[role='cash-discount']").text(roundUp(coinImpledgePrice));
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