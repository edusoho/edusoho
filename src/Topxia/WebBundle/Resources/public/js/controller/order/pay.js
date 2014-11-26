define(function(require, exports, module) {

	exports.run = function() {
		function conculatePrice(){
			var totalPrice = parseFloat($('[role="total-price"]').text());
			var couponAmount = parseFloat($('[role="coupon-price"]').find(".price_r_num").text());
			var coinAmount = parseFloat($('[role="cash-discount"]').text());
			var activeAmount = totalPrice-couponAmount-coinAmount;
			if(activeAmount<0){
				activeAmount=0;
			}
			$('[role="pay-amount"]').text(activeAmount);
		}

		$('[role="coinNum"]').blur(function(e){
			var cashRate = $('[role="cash-rate"]').val();
			var coin = $(this).val();
			if(isNaN(coin)){
				$(this).val("0.00");
				$('[role="cash-discount"]').text("0.00");
				return;
			}
			var cash = $('[role="accountCash"]').text();
			var discount = 0;
			$('[role="pay-amount"]').text();
			if(parseFloat(cash) < parseFloat(coin)) {
				$(this).val(cash);
				discount = cash/cashRate;
			} else {
				discount = coin/cashRate;
			}
			$('[role="cash-discount"]').text(discount);
			conculatePrice();
		});

		$("#add_card").click(function(e){
			$('[role="coupon-code"]').show().focus();
			$('[role="no-use-coupon-code"]').hide();
			$('[role="cancel-coupon"]').show();
			$('[role="code-notify"]').show();
			$(this).hide();
		})

		$('[role="cancel-coupon"]').click(function(e){
			$('[role="coupon-code"]').hide();
			$('[role="no-use-coupon-code"]').show();
			$("#add_card").show();
			$('[role="code-notify"]').hide();
			$('[role="coupon-price"]').find(".price_r_num").text("0.00");
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
			data.targetId = 1;
			data.amount = 0.01;
			
			$.post('/coupon/check', data, function(data){
				if(data.useable == "no") {
					$('[role="code-notify"]').css("color","red").text(data.message);
				} else if(data.useable == "yes"){
					$('[role="code-notify"]').css("color","green").text("优惠码可用");
					$('[role="coupon-price"]').find(".price_r_num").text(data.decreaseAmount);
				}
				conculatePrice();
			})
		})

	}
});