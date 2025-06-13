

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

                }

        }
};
