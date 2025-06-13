(function (Drupal) {
	Drupal.behaviors.pageReloadOnVariationChange = {
		attach: function (context, settings) {
			let allColorPalettes = document.querySelector("form.commerce-order-item-add-to-cart-form #edit-purchased-entity-0-attributes-attribute-color");

			if (allColorPalettes != null) {
				function checkNumberInName(str) {

					if (/\d/.test(str)) {
						return str.match(/\d+/g);
					}
					else {
						return str;
					}
					// return [/\d/.test(str),'/\d/'];
				}
				if (allColorPalettes.children) {
					const colors = Array.from(allColorPalettes.children);

					if (allColorPalettes.children.length == 1) {
						if (!location.href.includes('?v')) {
							let selectedColorCode = allColorPalettes.children[0].querySelector('input').value;
							if (settings.stock_data[0].selected_color == selectedColorCode) {
								let variationId = settings.stock_data[0].variation_id;
								let url = new URL(window.location.href);

								// Check if the current URL already has the correct variationId
								if (url.searchParams.get('v') !== variationId.toString()) {

									// Add or update query parameter
									url.searchParams.set('v', variationId);

									// Reload with the new URL
									window.location.href = url.toString();
								}
							}
						}

					} else {
						// let sortedColorList = colors.sort((a, b) => {
						// 	if(a.querySelector('.field--name-name > div')){
						// 	let first = isNaN(checkNumberInName(a.querySelector('.field--name-name > div').innerText)) ? 10000 : checkNumberInName(a.querySelector('.field--name-name > div').innerText);
						// 	let second = isNaN(checkNumberInName(b.querySelector('.field--name-name > div').innerText)) ? 10000 : checkNumberInName(b.querySelector('.field--name-name > div').innerText);
						// 	return first - second;
						// }
						// });

						// allColorPalettes.innerHTML = "";
						// let selectedColorCode = 0;
						// sortedColorList.forEach((item, index) => {
						// 	if (index == 0) {
						// 		selectedColorCode = item.querySelector('input').value;
						// 	}
						// 	allColorPalettes.appendChild(item)
						// });
						// allColorPalettes.children[0].querySelector('input').checked = true;
						let selectedColorCode = allColorPalettes.children[0].querySelector('input').value;
						if (!location.href.includes('?v')) {

							for (let j = 0; j != settings.stock_data.length; j++) {
								if (settings.stock_data[j].selected_color == selectedColorCode) {
									let variationId = settings.stock_data[j].variation_id;
									let url = new URL(window.location.href);

									// Add or update query parameters
									url.searchParams.set('v', variationId);

									// Reload with the new URL
									window.location.href = url.toString();
									break;
								}
							}

						}
					}

				}

			}
			const urlParams = new URLSearchParams(window.location.search);
			let currentV = urlParams.get('v');

			// Monitoring changes to the URL
			window.addEventListener('popstate', function () {
				const newUrlParams = new URLSearchParams(window.location.search);
				const newV = newUrlParams.get('v');

				if (newV !== currentV) {
					// reloading the page, when 'v' changes,
					window.location.reload();
				}
			});

			// Monitoring AJAX requests if variations got updated via AJAX
			jQuery(document).ajaxComplete(function () {
				const updatedUrlParams = new URLSearchParams(window.location.search);
				const updatedV = updatedUrlParams.get('v');

				if (updatedV !== currentV) {
					// reloading the page, when 'v' changes,
					currentV = updatedV;
					window.location.reload();
				}
			});
		},
	};
})(Drupal);

(function ($, Drupal) {
	Drupal.behaviors.productListing = {
		attach: function (context, settings) {

			// For converting price value as calculatable (regardless of english or german)
			function parseLocaleNumber(str) {
				// Remove all non-digit, non-comma, non-dot characters
				str = str.replace(/[^\d.,]/g, '');
			      
				// If string contains both comma and dot, figure out which is decimal
				const hasComma = str.includes(',');
				const hasDot = str.includes('.');
			      
				if (hasComma && hasDot) {
				  // If dot comes before comma => German format: "1.234,56"
				  if (str.indexOf('.') < str.indexOf(',')) {
				    str = str.replace(/\./g, '').replace(',', '.');
				  }
				  // If comma comes before dot => English format: "1,234.56"
				  else {
				    str = str.replace(/,/g, '');
				  }
				} else if (hasComma && !hasDot) {
				  // Assume German format like "1234,56"
				  str = str.replace(',', '.');
				} else {
				  // English format: remove thousand separator if needed
				  str = str.replace(/,/g, '');
				}
			      
				return parseFloat(str);
			      }

			function checkNumberInName(str) {

				if (/\d/.test(str)) {
					return str.match(/\d+/g);
				}
				else {
					return str;
				}

			}
			let colorNames = document.querySelectorAll('.form-item-purchased-entity-0-attributes-attribute-color .field--name-name');
			let variationName = document.querySelector('.selected-variation');
			if (variationName != null) {

			}
			if (colorNames != null) {
				colorNames.forEach((item) => {
					if (item.innerText.startsWith('*', 0)) {
						item.innerText = item.innerText.substring(1,);
					}
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

			function updatePrice() {
				var currentLangcode = location.pathname.split('/')[1];
				var currentQuantity = parseInt(jQuery('#quantity-wrapper .field--name-quantity .form-number').val());
				let allQuantities = document.querySelectorAll(".form-item-default-quantity label");
				let allPrices = document.querySelectorAll(".form-item-default-price label");
				let mainPrice = document.querySelector('.field--type-commerce-price div:last-child');
				if (currentQuantity == 0) {
					jQuery('.field--type-commerce-price div:last-child').text('€' + 0);
				}
				else {
					if (allQuantities != null && allPrices != null) {
						if (allQuantities.length > 1) {
							// Loop through the quantity-price pairs
							let price = null;
							for (let i = 0; i < allQuantities.length; i++) {
								const quantityThreshold = parseFloat(allQuantities[i].innerText.replace(',', '.'));
								// const unitPrice = parseFloat(allPrices[i].innerText.replace(',', '.'));
								const unitPrice = parseLocaleNumber(allPrices[i].innerText);
								if (currentQuantity >= quantityThreshold) {
									price = unitPrice;
									let total = currentQuantity * price;
									let calculated_price_2_decimal = parseFloat(total).toFixed(2);
									let parts = calculated_price_2_decimal.split('.'); // Separating integer and decimal parts
									let intPart = parts[0];
									let decimalPart = parts[1];



									if (currentLangcode && currentLangcode == 'de') {
										intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
										jQuery('.field--type-commerce-price div:last-child').text('€ ' + intPart + ',' + decimalPart);
									}
									else {
										intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
										jQuery('.field--type-commerce-price div:last-child').text('€ ' + intPart + '.' + decimalPart);
									}
									// //     mainPrice.innerText = '€' + total;
									// console.log( intPart + ',' + decimalPart)
								} else {
									break;
								}

							}
						}
						else {
							// let price = parseFloat(allPrices[0].innerText.replace(',', '.'));
							let price = parseLocaleNumber(allPrices[0].innerText);
							let total = currentQuantity * price;
							let calculated_price_2_decimal = parseFloat(total).toFixed(2);
							let parts = calculated_price_2_decimal.split('.'); // Separating integer and decimal parts
							let intPart = parts[0];
							let decimalPart = parts[1];
							// Adding thousands separator to the integer part

							// console.log(currentPrice)
							if (currentLangcode && currentLangcode == 'de') {
								intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
								jQuery('.field--type-commerce-price div:last-child').text('€ ' + intPart + ',' + decimalPart);
							}
							else {
								intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
								jQuery('.field--type-commerce-price div:last-child').text('€ ' + intPart + '.' + decimalPart);
							}
						}

					}
				}

			}

			function updateProductDetail() {
				var product_quantity = jQuery('#quantity-wrapper .field--name-quantity .form-number').val();
				jQuery('.price-quantity-wrapper').insertAfter('.commerce-order-item-add-to-cart-form .field--name-purchased-entity');
				jQuery('#price-wrapper').insertAfter('.price-quantity-wrapper');
				jQuery('.product-detail ul.product-attribute-label').empty();
				var attributes_name =
					jQuery(".product--rendered-attribute .form-item-purchased-entity-0-attributes-attribute-color").find(".product--rendered-attribute__selected").parents('.form-item-purchased-entity-0-attributes-attribute-color').find('.field--name-name >div:first-child').text();
				if (attributes_name != '') {

					jQuery('<li>Farbe: ' + attributes_name + '</li>').appendTo('.product-detail ul.product-attribute-label');
				}
				jQuery('<li>Menge: ' + product_quantity + '</li>').appendTo('.product-detail ul.product-attribute-label');

				jQuery(document).ready(function () {
					jQuery(".product--rendered-attribute .form-item-purchased-entity-0-attributes-attribute-color").click(function () {
						jQuery(document).ajaxComplete(function () {
							location.reload(true);
						});
					});
				});
			}
			updateProductDetail();

			//  Price and quantity change functionality Quantity radio button click 
			jQuery('.price-quantity-wrapper .form-item-default-quantity').each(function () {
				jQuery(this).find("label.option").click(function () {

					var quantity = parseInt(jQuery(this).text());
					jQuery('#quantity-wrapper .field--name-quantity .form-number').val(quantity);
					updatePrice();
					// jQuery('#quantity-wrapper .field--name-quantity .form-number').val(quantity);
					// var next_quantity = jQuery(this).parents('.form-item-default-quantity').next().find('label.option').text();
					// var prev_quantity = jQuery(this).parents('.form-item-default-quantity').prev().find('label.option').text();
					// var price = parseFloat(jQuery(this).parents('.form-item-default-quantity').find('input').val());
					// var next_price = parseFloat(jQuery(this).parents('.form-item-default-quantity').next().find('input').val());
					// var prev_price = parseFloat(jQuery(this).parents('.form-item-default-quantity').prev().find('input').val());
					// var calculated_price = 0;
					// var current_quantity = jQuery('#quantity-wrapper .field--name-quantity .form-number').val();
					// jQuery('#quantity-wrapper .field--name-quantity .form-number').val(current_quantity);
					// if (current_quantity == next_quantity) {
					// 	var calculated_price = current_quantity * next_price;
					// } else if (current_quantity == prev_quantity) {
					// 	var calculated_price = current_quantity * prev_price;
					// } else if (current_quantity < next_quantity) {
					// 	var calculated_price = current_quantity * price;
					// } else {
					// 	var calculated_price = current_quantity * price;
					// }
					// let calculated_price_2_decimal = parseFloat(calculated_price).toFixed(2);
					// let parts = calculated_price_2_decimal.split('.'); // Separate integer and decimal parts
					// let intPart = parts[0];
					// let decimalPart = parts[1];
					// Adding thousands separator to the integer part


					// if (currentLangcode == 'de') {
					// 	intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
					// 	jQuery('#price-wrapper .field--type-commerce-price .field__item').text('€' + intPart + ',' + decimalPart);
					// }
					// else {
					// 	intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
					// 	jQuery('#price-wrapper .field--type-commerce-price .field__item').text('€' + intPart + '.' + decimalPart);
					// }

					updateProductDetail();
				});

			});




			// Price change functionality on + and - button click 

			jQuery(".quantity-plus").click(function (e) {
				e.preventDefault()
				var current_quantity = parseInt(jQuery('#quantity-wrapper .field--name-quantity .form-number').val());
				jQuery('#quantity-wrapper .field--name-quantity .form-number').val(current_quantity + 1);
				updatePrice();
				updateProductDetail();
			});

			jQuery(".quantity-minus").click(function (e) {
				e.preventDefault()
				var current_quantity = parseInt(jQuery('#quantity-wrapper .field--name-quantity .form-number').val());
				if (current_quantity != 0)
					jQuery('#quantity-wrapper .field--name-quantity .form-number').val(current_quantity - 1);
				updatePrice()
				updateProductDetail();
			});

			jQuery("#quantity-wrapper .field--name-quantity .form-number").change(() => {
				updatePrice();
				updateProductDetail();
			})
			let quantityInput = document.querySelector('.js-form-item-quantity-0-value input[type="number"]');
			if (quantityInput != null) {
				quantityInput.addEventListener('input', (e) => {
					updatePrice();
					updateProductDetail();
				});
			}
		}
	};
})(jQuery, Drupal);