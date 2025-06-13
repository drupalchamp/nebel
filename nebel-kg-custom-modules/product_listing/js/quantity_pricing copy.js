
(function ($, Drupal) {
	Drupal.behaviors.productListing = {
		attach: function (context, settings) {

			// let pnames = document.querySelectorAll('.product-search-data .views-view-responsive-grid__item .views-field-title');
			// if(pnames!=null){
			// 	let totalPnames = [];
			// 	pnames.forEach((p)=>{
			// 		if(!totalPnames.includes(p.innerText)){
			// 			totalPnames.push(p.innerText)
			// 		}
			// 		else{
			// 		p.closest('.views-view-responsive-grid__item').classList.add('hide');	
			// 		}
			// 	})

			// }

			function checkNumberInName(str) {

				if (/\d/.test(str)) {
					return str.match(/\d+/g);
				}
				else {
					return str;
				}
				// return [/\d/.test(str),'/\d/'];
			}
			let colorNames = document.querySelectorAll('.form-item-purchased-entity-0-attributes-attribute-color .field--name-name');
			let variationName = document.querySelector('.selected-variation');
			if (variationName != null) {
				console.log(variationName.innerText);
			}
			if (colorNames != null) {
				colorNames.forEach((item) => {
					if (item.innerText.startsWith('*', 0)) {
						item.innerText = item.innerText.substring(1,);
					}
					// let actualColorName = item.innerText.split('(')[1].split(')')[0];
					//   console.log("COLOR"+actualColorName)
					// 	// item.innerText = checkNumberInName(actualColorName);
					// 	let newColor = document.createElement('div');
					// 	newColor.innerText = checkNumberInName(actualColorName);
					// 	item.appendChild(newColor);
				})
			}
			let currentLangcode = location.pathname.split('/')[1];
			jQuery('#edit-default-quantity .form-item-default-quantity').each(function () {
				var price = jQuery(this).find('input').val();
				var quantity = jQuery(this).find('label.option').text();
				var next_quantity = jQuery(this).next().find('label.option').text();
				var dataTo = next_quantity - 1;
				jQuery(this).attr({
					'data-from': quantity,
					'data-to': dataTo,
					'data-price': price,
				});

			});

			jQuery("#quantity-wrapper .field--name-quantity .form-number").change(function () {
				var current_quantity = jQuery(this).val();
				jQuery(document).ajaxComplete(function () {
					// Your price calculation logic
					var elements = jQuery('#edit-default-quantity .form-item-default-quantity');

					var priceUpdated = false;
					var calculated_price = 0;

					for (var i = 0; i < elements.length; i++) {
						var element = jQuery(elements[i]);

						var price = parseFloat(element.find('input').val());
						var quantity = parseInt(element.find('label.option').text());
						var toValue = parseInt(element.attr("data-to"));

						if (isNaN(price)) {
							price = parseFloat(element.attr("data-price"));
						}

						if (current_quantity == quantity) {
							calculated_price = quantity * price;
							priceUpdated = true;
							break;
						} else if (current_quantity <= toValue) {
							calculated_price = current_quantity * price;
							priceUpdated = true;
							break;
						} else {
							calculated_price = current_quantity * price;
							priceUpdated = true;
						}
					}

					if (priceUpdated) {
						if (currentLangcode == 'de') {
							
							jQuery('#price-wrapper .field--type-commerce-price .field__item').text('€' + calculated_price.replace(/,(\d{2})$/, '.$1'));
						} else {
							jQuery('#price-wrapper .field--type-commerce-price .field__item').text('€' + calculated_price);
						}
					}

				});

			});

			jQuery('.price-quantity-wrapper .form-item-default-quantity').each(function () {
				jQuery(this).find("label.option").click(function () {
					var quantity = parseInt(jQuery(this).text());
					jQuery('#quantity-wrapper .field--name-quantity .form-number').val(quantity);
					var next_quantity = jQuery(this).parents('.form-item-default-quantity').next().find('label.option').text();
					var prev_quantity = jQuery(this).parents('.form-item-default-quantity').prev().find('label.option').text();
					var price = parseFloat(jQuery(this).parents('.form-item-default-quantity').find('input').val());
					var next_price = parseFloat(jQuery(this).parents('.form-item-default-quantity').next().find('input').val());
					var prev_price = parseFloat(jQuery(this).parents('.form-item-default-quantity').prev().find('input').val());
					var calculated_price = 0;
					jQuery(document).ajaxComplete(function () {
						var current_quantity = jQuery('#quantity-wrapper .field--name-quantity .form-number').val();
						jQuery('#quantity-wrapper .field--name-quantity .form-number').val(current_quantity);
						if (current_quantity == next_quantity) {
							var calculated_price = current_quantity * next_price;
						} else if (current_quantity == prev_quantity) {
							var calculated_price = current_quantity * prev_price;
						} else if (current_quantity < next_quantity) {
							var calculated_price = current_quantity * price;
						} else {
							var calculated_price = current_quantity * price;
						}
						//console.log('calculated_price',calculated_price);
						if (currentLangcode == 'de') {
							jQuery('#price-wrapper .field--type-commerce-price .field__item').text('€' + calculated_price.toFixed(2).replace('.', ','));
						}
						else
							jQuery('#price-wrapper .field--type-commerce-price .field__item').text('€' + calculated_price.toFixed(2));
					});
				});
			});
			var current_quantity = jQuery('#quantity-wrapper .field--name-quantity .form-number').val();

			jQuery(document).ajaxComplete(function () {
				var elements = jQuery('#edit-default-quantity .form-item-default-quantity');
				var priceUpdated = false;
				var calculated_price = 0;
				for (var i = 0; i < elements.length; i++) {
					var element = jQuery(elements[i]);
					var price = parseFloat(element.find('input').val());
					var quantity = parseInt(element.find('label.option').text());
					var toValue = parseInt(element.attr("data-to"));
					if (isNaN(price)) {
						price = parseFloat(element.attr("data-price"));
					}
					// if (current_quantity == quantity) {
					// 	calculated_price = quantity * price;
					// 	priceUpdated = true;
					// 	break;
					// } else if (current_quantity <= toValue) {
					// 	calculated_price = current_quantity * price;
					// 	priceUpdated = true;
					// 	break;
					// } else {
					// 	calculated_price = current_quantity * price;
					// 	priceUpdated = true;
					// }
				}

				// if (priceUpdated) {
				// 	if (currentLangcode == 'de') {
				// 		jQuery('#price-wrapper .field--type-commerce-price .field__item').text('€' + calculated_price.toFixed(2).replace('.', ','));
				// 	}
				// 	else
				// 		jQuery('#price-wrapper .field--type-commerce-price .field__item').text('€' + calculated_price.toFixed(2));
				// }

			});

			var product_quantity = jQuery('#quantity-wrapper .field--name-quantity .form-number').val();
			jQuery('.price-quantity-wrapper').insertAfter('.commerce-order-item-add-to-cart-form .field--name-purchased-entity');
			jQuery('#price-wrapper').insertAfter('.price-quantity-wrapper');
			jQuery('.product-detail ul.product-attribute-label').empty();
			var attributes_name =
				jQuery(".product--rendered-attribute .form-item-purchased-entity-0-attributes-attribute-color").find(".product--rendered-attribute__selected").parents('.form-item-purchased-entity-0-attributes-attribute-color').find('.field--name-name >div:first-child').text();


			if (attributes_name != '') {
				console.log("attribute name:" + attributes_name)
				jQuery('<li>Farbe: ' + attributes_name + '</li>').appendTo('.product-detail ul.product-attribute-label');
			}
			jQuery('<li>Menge: ' + product_quantity + '</li>').appendTo('.product-detail ul.product-attribute-label');

			jQuery(document).ready(function () {
				jQuery(".product--rendered-attribute .form-item-purchased-entity-0-attributes-attribute-color").click(function () {
					jQuery(document).ajaxComplete(function () {
						location.reload(true);
					});
				});

				// var button_text = jQuery(".commerce-order-item-add-to-cart-form .form-actions .form-submit").val();
				// var button_text_en = jQuery(".product-detail-right .product-add-to-cart-button a.request-a-quote-button").text();
				// if (button_text == 'Produkt nicht vorrätig') {
				// 	jQuery(".product-detail-right p.stock-available").removeClass("stock-available").addClass("stock-unavailable");
				// 	jQuery(".product-detail-right p.stock-unavailable").text('Produkt nicht vorrätig');
				// 	jQuery(".commerce-order-item-add-to-cart-form").addClass("product-detail-color-palette-disable");
				// 	jQuery(".product-detail-color-palette-disable #edit-purchased-entity-0-attributes-attribute-color .form-item .form-radio").prop("disabled", true);
				// 	jQuery(".product-detail-color-palette-disable #edit-purchased-entity-0-attributes-attribute-select-format").prop("disabled", true).trigger("chosen:updated");
				// 	jQuery(".product-detail-color-palette-disable .form-item-default-quantity .form-radio").prop("disabled", true);
				// }
				// if (button_text == 'Out of stock') {
				// 	jQuery(".product-detail-right p.stock-available").removeClass("stock-available").addClass("stock-unavailable");
				// 	jQuery(".product-detail-right p.stock-unavailable").text('Out of stock');
				// 	jQuery(".commerce-order-item-add-to-cart-form").addClass("product-detail-color-palette-disable");
				// 	jQuery(".product-detail-color-palette-disable #edit-purchased-entity-0-attributes-attribute-color .form-item .form-radio").prop("disabled", true);
				// 	jQuery(".product-detail-color-palette-disable #edit-purchased-entity-0-attributes-attribute-select-format").prop("disabled", true).trigger("chosen:updated");
				// 	jQuery(".product-detail-color-palette-disable .form-item-default-quantity .form-radio").prop("disabled", true);
				// }
				// if (button_text_en == 'Request a quote' || button_text_en == 'Angebot anfordern') {
				// 	// jQuery(".product-detail-right p.stock-available").removeClass(f s"stock-available").addClass("stock-unavailable");
				// 	// jQuery(".product-detail-right p.stock-unavailable").text('Out otock');
				// 	jQuery(".commerce-order-item-add-to-cart-form").addClass("product-detail-color-palette-disable");
				// 	jQuery(".product-detail-color-palette-disable #edit-purchased-entity-0-attributes-attribute-color .form-item .form-radio").prop("disabled", true);
				// 	jQuery(".product-detail-color-palette-disable #edit-purchased-entity-0-attributes-attribute-select-format").prop("disabled", true).trigger("chosen:updated");
				// 	jQuery(".product-detail-color-palette-disable .form-item-default-quantity .form-radio").prop("disabled", true);
				// }


			});


		}
	};
})(jQuery, Drupal);