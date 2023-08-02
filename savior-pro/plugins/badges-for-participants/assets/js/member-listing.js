jQuery(document).ready(function ($) {
    'use strict';
    var buttonCommon = {
        exportOptions: {
            columns: [0, 1, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
            modifier: {
                search: 'applied'
            },
            // format: {
            //     body: function (data, row, column, node) {
            //         console.log(column);
            //         data = $.fn.DataTable.Buttons.stripData(data, null);            
            //         if ((column === 1 || column === 3)) {
            //             var stringArray = data.replaceAll('_', ' ').split(" ");
            //             var capitalizedArray = stringArray.map(function (element) {
            //                 return element.charAt(0).toUpperCase() + element.slice(1);
            //             });
            //             return capitalizedArray.join(" ");
            //         }
            //         return data;
            //     }
            // }
        }
    };
    $("#member-listing").DataTable({
        fixedHeader: true,
        dom: 'Blfrtip',
        pageLength: 25,
        aLengthMenu: [
            [10, 25, 50, 100, 200, -1],
            [10, 25, 50, 100, 200, "All"]
        ],
        // order: [[4, 'asc']],
        "processing": true,
        scrollX: true,
        buttons: [
            {
                extend: 'collection',
                text: 'Export',
                buttons: [
                    $.extend(true, {}, buttonCommon, {
                        extend: 'csvHtml5',
                        text: 'Export to CSV',
                    }),
                    $.extend(true, {}, buttonCommon, {
                        extend: 'pdfHtml5',
                        text: 'Export to PDF',
                        orientation: 'landscape',
                        pageSize: 'A3',
                    }),
                    $.extend(true, {}, buttonCommon, {
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                    }),
                ]
            }
        ],
    });

    //Save Print values
    $('button[id*="member_print_"]').on('click', function (e) {
        let id = $(this).attr('id');
        let customer_id = $(this).data('customer_id');
        let order_id = $(this).data('order_id');
        let product_id = $(this).data('product_id');
        // showAlert('error', 'ID : ' + id);
        print_badges(customer_id, order_id, product_id);
    })
});
function showAlert(icon, title) {
    Swal.fire({
        // position: 'top-end',
        icon: icon,
        text: title,
        showConfirmButton: true,
        // timer: 1500
    })
}

const print_badges = async (customer_id, order_id, product_id) => {

    /* inputOptions can be an object or Promise */
    const inputOptions = new Promise((resolve) => {
        setTimeout(() => {
            resolve({
                '1': 'Dyno Format',
                '2': 'Letter Format'
            })
        }, 500)
    })

    const { value: color } = await Swal.fire({
        title: 'Print badges',
        input: 'radio',
        inputOptions: inputOptions,
        showCancelButton: true,
        confirmButtonText: 'View',
        inputValidator: (value) => {
            if (!value) {
                return 'You need to choose something!'
            }
        },
        showLoaderOnConfirm: true,
        preConfirm: (format_type) => {
            var data = new URLSearchParams();
            data.append('action', 'print_badges');
            data.append('customer_id', customer_id);
            data.append('product_id', product_id);
            data.append('order_id', order_id);
            data.append('format_type', format_type);

            return fetch(member_object.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: data
            })
                .then(response => {
                    return response.json();
                })
                .then(htmlResponse => {
                    jQuery('#templates').attr('template', format_type);
                    jQuery('#templates').append(htmlResponse.data);
                    return htmlResponse.data;
                })
                .catch(error => {
                    Swal.showValidationMessage(
                        `Request failed: ${error}`
                    );
                });
        },
        allowOutsideClick: false,
        onBeforeOpen: () => {
            Swal.showLoading();
        },
    })

    if (color) {
        // Swal.fire({ html: `${color}` })
        badgePDF();
    }

}

const badgePDF = async () => {
    const $badges = jQuery('body').find('#badges');
    const $templates = jQuery('body').find('#templates');
    const $badges_items = $templates.find('.badge_1');

    const tpl = $templates.attr('template');
    let doc, topMargin, leftMargin, hBadge, wBadge;

    if (tpl === '1') {
        doc = new jsPDF({
            unit: 'mm',
            format: ['100', '57'], // 4" x 3" in millimeters
        });

        topMargin = 0;
        leftMargin = 0;
        hBadge = 58;
        wBadge = 100;
    } else {
        doc = new jsPDF({
            format: ['217', '281'],
            unit: 'mm',
        });

        topMargin = 24;
        leftMargin = 6.5;
        hBadge = 77;
        wBadge = 102;
    }

    const images = await Promise.all(
        $badges_items.map(async function () {
            const $badge = jQuery(this);
            const id = $badge.attr('data-id');
            const regId = $badge.attr('data-reg-id');

            const dataUrl = await domtoimage.toPng($badge.get(0));
            const image = new Image();
            image.src = dataUrl;

            return { id, regId, image };
        })
    );

    let page = doc.internal.getCurrentPageInfo().pageNumber;

    for (const [index, imageInfo] of images.entries()) {
        const { id, regId, image } = imageInfo;
        const row = Math.floor(index / 2) % 3;
        const col = index % 2;

        if (tpl === '2' && row === 0 && col === 0 && index > 0) {
            doc.addPage();
            page = doc.internal.getCurrentPageInfo().pageNumber;
        }

        const x = leftMargin + col * wBadge;
        const y = topMargin + row * hBadge + (hBadge + 5) * Math.floor(index / 6);
        doc.addImage(image, 'PNG', x, y, wBadge, hBadge);
    }

    doc.save('badges.pdf');

    const ids = images.map((imageInfo) => imageInfo.regId);

    const data_ids = {
        action: 'action_printed_in_ids',
        ids: ids,
    };
    jQuery.ajax({
        url: member_object.ajax_url,
        type: 'POST',
        data: data_ids,
        datatype: 'json',
        success: function (data) { 
            if(data.success)
            {
                ids.map((userId) => jQuery(`#user_badge_print_${userId}`).html('<b style="color:green;">Printed</b>'))
            }
        },
        error: function (jqXHR, textStatus, errorThrown) { showAlert('error', errorThrown); }
    });
    jQuery('#templates').html('');
    Swal.close();
}