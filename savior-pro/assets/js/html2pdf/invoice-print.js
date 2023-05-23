jQuery(document).ready(function($){
    jQuery('#download_invoice').on('click', function (event) {
        event.preventDefault();
        let orderId = jQuery(this).attr("data-orderId");
        let ajaxurl = asgmt_ajax_object.ajax_url;
        let data = {
            action: "create_invoice",
            orderId: orderId
        }
        const options = {
            margin: 0.3,
            filename: `invice-${orderId}.pdf`,
            image: {
                type: 'jpeg',
                quality: 0.98
            },
            html2canvas: {
                scale: 2
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'portrait'
            }
        }
    
        jQuery.post(ajaxurl, data, function (response) {
            
            if (response.data.success == true) {
                let data = response.data.data;
                var strr = `<html>
                                <head>
                                    <title>Invoice</title>
                                     <style>.single-invoice-print{border:0.1rem solid #ccc!important;padding:0.5rem 1.5rem 0.5rem 1.5rem;margin-top:1.5rem}</style>
                                </head>
                                <body>
                                     <div class="single-invoice-print" style=""> ${data} </div>
                                 </body>
                            </html>`;
                // 				jQuery('#test_data').html(data);
                html2pdf().from(strr).set(options).save();
                console.log(html2pdf())
            }
        });
    })
})