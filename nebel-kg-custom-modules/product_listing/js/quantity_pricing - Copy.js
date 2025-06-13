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
					
					if(allColorPalettes.children.length == 1){
						
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
						
					}else{
					let sortedColorList = colors.sort((a, b) => {
						if(a.querySelector('.field--name-name > div')){
						let first = isNaN(checkNumberInName(a.querySelector('.field--name-name > div').innerText)) ? 10000 : checkNumberInName(a.querySelector('.field--name-name > div').innerText);
						let second = isNaN(checkNumberInName(b.querySelector('.field--name-name > div').innerText)) ? 10000 : checkNumberInName(b.querySelector('.field--name-name > div').innerText);
						return first - second;
					}
					});
					
					allColorPalettes.innerHTML = "";
					let selectedColorCode = 0;
					sortedColorList.forEach((item, index) => {
						if (index == 0) {
							selectedColorCode = item.querySelector('input').value;
						}
						allColorPalettes.appendChild(item)
					});
					// allColorPalettes.children[0].querySelector('input').checked = true;
					
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
				
			}
			if (colorNames != null) {
				colorNames.forEach((item) => {
					if (item.innerText.startsWith('*', 0)) {
						item.innerText = item.innerText.substring(1,);
					}
					// let actualColorName = item.innerText.split('(')[1].split(')')[0];
					
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
						

						let calculated_price_2_decimal = parseFloat(calculated_price).toFixed(2); 
						let parts = calculated_price_2_decimal.split('.'); // Separate integer and decimal parts
						let intPart = parts[0];
						let decimalPart = parts[1];
						// Adding thousands separator to the integer part
						intPart = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
					      
						
						if (currentLangcode == 'de') {
							let calculated_price_in_german = calculated_price_2_decimal.toLocaleString('de-DE');
							jQuery('#price-wrapper .field--type-commerce-price .field__item').text('€' + intPart + ',' + decimalPart);
						}
						else
							jQuery('#price-wrapper .field--type-commerce-price .field__item').text('€' + calculated_price_2_decimal);
					});
				});
			});


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
	};
})(jQuery, Drupal);