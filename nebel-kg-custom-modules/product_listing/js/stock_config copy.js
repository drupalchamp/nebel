

Drupal.behaviors.myBehavior = {

        attach: function (context, settings) {

                if (location.pathname.includes('/product')) {


                        // allColorPalettes.innerHTML = ""; 
                        // items.forEach(item => ul.appendChild(item));
                        let currentLangcode = location.pathname.split('/')[1];

                        let selectedVariationElement = document.querySelector('.selected-variation');
                        let stockMessageElement = document.querySelector('.product-detail-page .stock-message');

                        if (selectedVariationElement != null) {
                                //getting current vID from route
                                let variationId = location.href.split('?v=')[1];
                                console.log(settings.stock_data);
                                if (variationId != null) {
                                        if (settings.stock_data != null) {
                                                settings.stock_data.forEach((v) => {
                                                        if (v.variation_id == variationId) {
                                                                // if(v.variation_title.startsWith('*',0)){
                                                                //         selectedVariationElement.innerText = v.variation_title.substring(1,);       
                                                                // }
                                                                // else
                                                                selectedVariationElement.innerText = v.variation_title;
                                                                if (stockMessageElement != null) {
                                                                        console.log(stockMessageElement);
                                                                        if (v.stock == 0) {
                                                                                stockMessageElement.classList.remove('stock-available');
                                                                                stockMessageElement.classList.add('stock-unavailable');
                                                                                if (currentLangcode == 'en') {
                                                                                        stockMessageElement.textContent = 'Out of Stock';
                                                                                } else {
                                                                                        stockMessageElement.textContent = 'Nicht Vorrätig';
                                                                                }
                                                                        } else {
                                                                                stockMessageElement.classList.add('stock-available');
                                                                                stockMessageElement.classList.remove('stock-unavailable');
                                                                                if (currentLangcode == 'en') {
                                                                                        stockMessageElement.textContent = 'In Stock';
                                                                                } else {
                                                                                        stockMessageElement.textContent = 'Auf Lager';
                                                                                }
                                                                        }
                                                                }
                                                                //   console.log(selectedVariationElement.innerText)
                                                        }
                                                });
                                        }
                                }
                                else {
                                        let variationId = settings.stock_data[0].variation_id;
                                        selectedVariationElement.innerText = drupalSettings.first_variation;
                                        if (settings.stock_data != null) {
                                                settings.stock_data.forEach((v) => {
                                                        if (v.variation_id == variationId) {
                                                                if (stockMessageElement != null) {
                                                                        if (v.stock == 0) {
                                                                                stockMessageElement.classList.remove('stock-available');
                                                                                stockMessageElement.classList.add('stock-unavailable');
                                                                                if (currentLangcode == 'en') {
                                                                                        stockMessageElement.textContent = 'Out of Stock';
                                                                                } else {
                                                                                        stockMessageElement.textContent = 'Nicht Vorrätig';
                                                                                }
                                                                        } else {
                                                                                stockMessageElement.classList.add('stock-available');
                                                                                stockMessageElement.classList.remove('stock-unavailable');
                                                                                if (currentLangcode == 'en') {
                                                                                        stockMessageElement.textContent = 'In Stock';
                                                                                } else {
                                                                                        stockMessageElement.textContent = 'Auf Lager';
                                                                                }
                                                                        }
                                                                }
                                                        }
                                                });
                                        }
                                }
                        }



                        let OriginalPrice = document.querySelector('.field--type-commerce-price .field__item:last-child');

                        let prices = document.querySelectorAll('#edit-default-price label');

                        if (OriginalPrice != null) {
                                let price = OriginalPrice.innerText;
                                if (currentLangcode == 'de') {
                                        // OriginalPrice.innerText = price.replace('.', ',');
                                        if (prices != null) {
                                                prices.forEach((p) => {
                                                        p.innerText = p.innerText.replace('.', ',');
                                                        // console.log(p.innerText);
                                                })
                                        }
                                }
                        }

                        let quantityValues = document.querySelectorAll('.js-form-item-default-quantity label');


                        let quantityInput = document.querySelector('.js-form-item-quantity-0-value input[type="number"]');

                        let mainPrice = document.querySelector('.field--type-commerce-price div:last-child');
                        let allQuantities = document.querySelectorAll(".form-item-default-quantity label");

                        let allPrices = document.querySelectorAll(".form-item-default-price label");
                     


                        if (quantityInput != null) {

                                quantityInput.addEventListener('input', (e) => {
                                        let currentQuantity = e.target.value;
                                        // console.log('Choosed quantity: '+parseFloat(currentQuantity))

                                        if (currentQuantity.length == 0) {
                                                mainPrice.innerText = '€' + 0;
                                        }
                                        else {
                                                if (allQuantities != null && allPrices != null) {

                                                        if (allQuantities.length > 1) {
                                                               

                                                                // Loop through the quantity-price pairs
                                                                // let price = null;
                                                                // for (let i = 0; i < allQuantities.length; i++) {
                                                                //   const quantityThreshold = parseFloat(allQuantities[i].innerText.replace(',', '.'));
                                                                //   const unitPrice = parseFloat(allPrices[i].innerText.replace(',', '.'));
                                                                
                                                                //   if (currentQuantity >= quantityThreshold) {
                                                                //     price = unitPrice;
                                                                //     console.log('Chosen price: ' + price);
                                                                //     console.log('Quantity: ' + currentQuantity);
                                                                
                                                                //     let total = currentQuantity * price;
                                                                //     mainPrice.innerText = '€' + total.toFixed(2);
                                                                
                                                                // //     if (currentLangcode === 'de') {
                                                                // //       mainPrice.innerText = mainPrice.innerText.replace('.', ',').replace(',00', '');
                                                                // //     }
                                                                //   } else {
                                                                //     break;
                                                                //   }
                                                                // }
                                                                                                                                // for (let i = 0; i != allQuantities.length; i++) {

                                                                //         if (i === allQuantities.length - 1) {
                                                                //                 if (parseFloat(currentQuantity) >= allQuantities[i].innerText) {
                                                                //                         let currentPrice = parseFloat(allPrices[i].innerText.replace(',', '.'));
                                                                //                         let currentQuantityVal = parseFloat(currentQuantity)
                                                                //                         mainPrice.innerText = '€' + (currentQuantityVal * currentPrice).toFixed(2);
                                                                //                         if (currentLangcode == 'de') {

                                                                //                                 mainPrice.innerText = mainPrice.innerText.replace('.', ',').replace(',00', '');
                                                                //                         }
                                                                //                 }
                                                                //         }
                                                                //         else {
                                                                //                 // 0 1 2 3    49
                                                                //                 // console.log(parseFloat(allQuantities[i].innerText))
                                                                //                 // console.log(parseFloat(allQuantities[i+1].innerText))
                                                                //                 if (parseFloat(currentQuantity) == parseFloat(allQuantities[i].innerText) || (parseFloat(currentQuantity) > parseFloat(allQuantities[i].innerText) && parseFloat(currentQuantity) < parseFloat(allQuantities[i + 1].innerText))) {
                                                                //                         let currentPrice = parseFloat(allPrices[i].innerText.replace(',', '.'));
                                                                //                         // console.log(currentPrice)
                                                                //                         let currentQuantityVal = parseFloat(currentQuantity)
                                                                //                         let calculatedPrice = Math.round(currentQuantityVal * currentPrice * 100) / 100;
                                                                //                         console.log("Calculated price: " + calculatedPrice);
                                                                //                         mainPrice.innerText = '€' + calculatedPrice;
                                                                //                         if (currentLangcode == 'de') {
                                                                //                                 mainPrice.innerText = mainPrice.innerText.replace('.', ',').replace(',00', '');
                                                                //                         }
                                                                //                         break;
                                                                //                 }
                                                                //                 else {
                                                                //                         let currentPrice = parseFloat(allPrices[i + 1].innerText.replace(',', '.'));
                                                                //                         let currentQuantityVal = parseFloat(currentQuantity)
                                                                //                         mainPrice.innerText = '€' + (currentQuantityVal * currentPrice).toFixed(2);
                                                                //                         if (currentLangcode == 'de') {
                                                                //                                 mainPrice.innerText = mainPrice.innerText.replace('.', ',').replace(',00', '');
                                                                //                         }
                                                                //                         break;
                                                                //                 }
                                                                //         }
                                                                // }
                                                        } else {
                                                                let currentPrice = parseFloat(allPrices[0].innerText.replace(',', '.'));
                                                                // console.log(currentPrice)
                                                                let currentQuantityVal = parseFloat(currentQuantity)
                                                                mainPrice.innerText = '€' + (currentQuantityVal * currentPrice).toFixed(2);
                                                                if (currentLangcode == 'de') {
                                                                        mainPrice.innerText = mainPrice.innerText.replace('.', ',').replace(',00', '');
                                                                }
                                                        }
                                                }
                                        }
                                });
                        }
                }

        }
};
