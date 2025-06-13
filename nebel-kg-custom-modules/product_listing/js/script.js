
Drupal.behaviors.commerce_product_scripts = {
        attach: function (context, settings) {

                function productListConvertToSelectList() {
                        let productListUL = document.querySelector('.product-list > ul');
                        const select = document.createElement('select');
                        if (productListUL != null) {
                                Array.from(productListUL.children).forEach(li => {
                                        const option = document.createElement('option');
                                        option.textContent = li.innerText;
                                        option.value = li.innerText;
                                        select.appendChild(option);
                                });

                                productListUL.replaceWith(select);
                        }
                }
                productListConvertToSelectList();

                if (location.pathname.includes('/unpublished-product-variations')) {
                        let selectedSKUs = [];
                        // For published product merge
                        let productListBox = document.querySelector('.view-unpublished-products .attachment.attachment-before');
                        if (productListBox != null) {
                                productListBox.classList.add('d-none');
                        }
                        let productSelectList = document.querySelector('.product-list > select');
                        // Merge open button for Unpublished product merge
                        let variationMergeBtn = document.querySelector('.variation-action-button > a');

                        let variationMergeLink = '/merge-products';
                        let cancelMergeButton = document.querySelector('.cancel-merge-button');
                        let mergeButton = document.querySelector('.merge-button');




                        // For Unpublished product merge
                        let unpublishedVariationMergeBtn = document.querySelector('.unpublished-variation-merge-action-button > a');
                        let confirmBox = document.getElementById('confirmBox');
                        let cancelMergeBtn = document.querySelector('.cancel-merge-btn');
                        let mergeBtn = document.querySelector('.merge-btn');
                        let unpublishedVariationMergeLink = '/merge-products-unpublished';

                        let productVariationsVBOcheckboxes = document.querySelectorAll('.views-form__bulk-operations-row input.form-checkbox');

                        if (productVariationsVBOcheckboxes != null && variationMergeBtn != null && unpublishedVariationMergeBtn != null) {
                                // For published product merge, opening product select dialog box
                                variationMergeBtn.addEventListener('click', (e) => {
                                        e.preventDefault();
                                        productListBox.classList.remove('d-none');
                                        document.body.style.overflow = 'hidden';
                                        document.querySelector('.overlay').classList.remove('d-none');
                                })
                                // For Unpublished product merge, opening confirmBox 
                                unpublishedVariationMergeBtn.addEventListener('click', (e) => {
                                        e.preventDefault();
                                        confirmBox.classList.remove('d-none');
                                        document.body.style.overflow = 'hidden';
                                        document.querySelector('.overlay').classList.remove('d-none');
                                })

                                // Merge button functionality for published product merge
                                if (mergeButton != null) {
                                        mergeButton.addEventListener('click', () => {
                                                const url = new URL(variationMergeLink, window.location.origin);
                                                const params = url.searchParams;

                                                params.set('product_title', productSelectList.value);

                                                variationMergeLink = url.pathname + '?' + params.toString();

                                                location.href = variationMergeLink;
                                        })
                                }
                                // Merge button functionality for unpublished product merge
                                if (mergeBtn != null) {
                                        mergeBtn.addEventListener('click', () => {
                                                location.href = unpublishedVariationMergeLink;
                                        });
                                }


                                // For published product merge, cancelMergeBtn functionality
                                if (cancelMergeButton != null) {
                                        cancelMergeButton.addEventListener('click', () => {
                                                productVariationsVBOcheckboxes.forEach((checkbox) => {
                                                        checkbox.checked = false;
                                                        checkbox.closest('tr').classList.remove('selected');
                                                })
                                                variationMergeBtn.parentElement.classList.add('d-none')
                                                unpublishedVariationMergeBtn.parentElement.classList.add('d-none')
                                                confirmBox.classList.add('d-none');
                                                productListBox.classList.add('d-none');
                                                document.body.style.overflow = 'unset';
                                                document.querySelector('.overlay').classList.add('d-none');
                                                selectedSKUs = [];
                                        })
                                }
                                // For Unpublished product merge, cancelMergeBtn functionality
                                if (cancelMergeBtn != null) {
                                        cancelMergeBtn.addEventListener('click', () => {
                                                productVariationsVBOcheckboxes.forEach((checkbox) => {
                                                        checkbox.checked = false;
                                                        checkbox.closest('tr').classList.remove('selected');
                                                })
                                                variationMergeBtn.parentElement.classList.add('d-none')
                                                unpublishedVariationMergeBtn.parentElement.classList.add('d-none')
                                                confirmBox.classList.add('d-none')
                                                productListBox.classList.add('d-none');
                                                document.body.style.overflow = 'unset';
                                                document.querySelector('.overlay').classList.add('d-none');
                                                selectedSKUs = [];
                                        })
                                }

                                
                                // Product variation checkboxes functionality for merge: On checked, its SKU value should be added to variation merge link.
                                productVariationsVBOcheckboxes.forEach((checkbox) => {
                                        checkbox.checked = false;
                                        checkbox.addEventListener('change', (e) => {
                                                let selectedSKU = e.target.closest('tr').querySelector('.views-field-sku').innerText;
                                                if (e.target.checked) {
                                                        if (!selectedSKUs.includes(selectedSKU))
                                                                selectedSKUs.push(selectedSKU)
                                                }
                                                else {

                                                        const index = selectedSKUs.indexOf(selectedSKU);
                                                        if (index > -1) {
                                                                selectedSKUs.splice(index, 1);
                                                        }
                                                }
                                                // For published product merge
                                                if (selectedSKUs.length > 0) {
                                                        variationMergeBtn.parentElement.classList.remove('d-none');
                                                        let mergeSKUs = selectedSKUs.reduce((link, sku) => {
                                                                return link + "+" + sku;
                                                        });
                                                        variationMergeBtn.href = "/merge-products?skus="
                                                        variationMergeLink = variationMergeBtn.href + mergeSKUs;
                                                        variationMergeBtn.href = variationMergeLink;
                                                }
                                                else {
                                                        variationMergeBtn.parentElement.classList.add('d-none');
                                                }


                                                // For Unpublished product merge
                                                if (selectedSKUs.length > 1) {
                                                        unpublishedVariationMergeBtn.parentElement.classList.remove('d-none');
                                                        let mergeSKUs = selectedSKUs.reduce((link, sku) => {
                                                                return link + "+" + sku;
                                                        });
                                                        unpublishedVariationMergeBtn.href = "/merge-products-unpublished?skus="
                                                        unpublishedVariationMergeLink = unpublishedVariationMergeBtn.href + mergeSKUs;
                                                        unpublishedVariationMergeBtn.href = unpublishedVariationMergeLink;
                                                }
                                                else {
                                                        unpublishedVariationMergeBtn.parentElement.classList.add('d-none');
                                                }
                                        });
                                })

                        }
                }
        }
}