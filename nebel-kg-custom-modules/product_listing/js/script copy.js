
Drupal.behaviors.commerce_product_scripts = {
        attach: function (context, settings) {

                function productListConvertToSelectList(){
                        let productListUL = document.querySelector('.product-list > ul');
                        const select = document.createElement('select');
                        if(productListUL!=null){
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
                let productListBox = document.querySelector('.view-unpublished-products .attachment.attachment-before');
                        if(productListBox!=null){
                                productListBox.classList.add('d-none');
                        }
                let productSelectList =   document.querySelector('.product-list > select');
                  
                let variationMergeLink = '/merge-products';

                let cancelMergeButton = document.querySelector('.cancel-merge-button');
                let mergeButton = document.querySelector('.merge-button');

                if(mergeButton!=null){
                        mergeButton.addEventListener('click',()=>{
                                const url = new URL(variationMergeLink, window.location.origin);
                                const params = url.searchParams;
                               
                                params.set('product_title', productSelectList.value);
                              
                                variationMergeLink = url.pathname + '?' + params.toString();
                                
                                location.href = variationMergeLink;
                        })     
                }    
              

               let productVariationsVBOcheckboxes = document.querySelectorAll('.views-form__bulk-operations-row input.form-checkbox');
       
               let variationMergeBtn = document.querySelector('.variation-action-button > a');
            
               if(productVariationsVBOcheckboxes!=null && variationMergeBtn!=null){
                
                variationMergeBtn.addEventListener('click',(e)=>{
                        e.preventDefault();
                        productListBox.classList.remove('d-none');
                        document.body.style.overflow = 'hidden';
                        document.querySelector('.overlay').classList.remove('d-none');
                })
                if(cancelMergeButton!=null){
                        cancelMergeButton.addEventListener('click',()=>{
                                productVariationsVBOcheckboxes.forEach((checkbox)=>{
                                        checkbox.checked = false;
                                        variationMergeBtn.parentElement.classList.add('d-none')
                                        checkbox.closest('tr').classList.remove('selected');
                                        productListBox.classList.add('d-none');
                                        document.body.style.overflow = 'unset';
                                        document.querySelector('.overlay').classList.add('d-none');
                                })
                        })     
                }   
              


                let selectedSKUs = [];
                
                productVariationsVBOcheckboxes.forEach((checkbox)=>{
                        checkbox.checked = false;
                        checkbox.addEventListener('change',(e)=>{
                                let selectedSKU = e.target.closest('tr').querySelector('.views-field-sku').innerText;
                                if(e.target.checked)
                                {
                                        if(!selectedSKUs.includes(selectedSKU))
                                        selectedSKUs.push(selectedSKU)
                                }
                                else{

                                        const index = selectedSKUs.indexOf(selectedSKU);
                                        if (index > -1) {
                                                selectedSKUs.splice(index, 1);
                                        }
                                }
                                if(selectedSKUs.length>0){
                                        variationMergeBtn.parentElement.classList.remove('d-none');
                                        let mergeSKUs = selectedSKUs.reduce((link, sku) => {
                                                return link + "+" + sku;
                                        });
                                        variationMergeBtn.href = "/merge-products?skus="
                                        variationMergeLink = variationMergeBtn.href + mergeSKUs;   
                                        variationMergeBtn.href = variationMergeLink;                       
                                }
                                else{
                                        variationMergeBtn.parentElement.classList.add('d-none');
                                }
                        });
                })
                
               }
                }
        }
}