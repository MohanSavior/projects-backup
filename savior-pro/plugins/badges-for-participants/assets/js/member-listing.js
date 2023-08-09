jQuery(document).ready(function ($) {
    'use strict';
    var buttonCommon = {
        exportOptions: {
            columns: [0, 1, 3, 4, 5, 6, 7, 8, 9, 10, 11],
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
    var t = $("#member-listing").DataTable({
        title: 'Badges > ASGMT >' + new Date().getFullYear(),
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
                        extend: 'pdfHtml5',
                        text: 'Export to PDF',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        customize: function (doc) {
                            // Set PDF content styles
                            doc.pageMargins = [20, 10, 20, 20];  // Top, right, bottom, left padding
                        }
                    }),
                    $.extend(true, {}, buttonCommon, {
                        extend: 'csvHtml5',
                        text: 'Export to CSV',
                    }),
                    $.extend(true, {}, buttonCommon, {
                        extend: 'excelHtml5',
                        text: 'Export to Excel',
                    }),
                ]
            },
            {
                text: 'Print All (Not Printed)',
                action: function (e, dt, node, config) {
                    var lastColumnData = dt.rows('[data-printed="0"]').column(-1).data();
                    let customer_ids = [];
                    lastColumnData.each(function (value, index) {
                        customer_ids.push(value)
                    });
                    swal.showLoading();
                    print_badges(customer_ids, false);
                }
            },
        ],
    });

    //Append the custom filters
    $(".dt-buttons").insertAfter($("#member-listing_length"));
    $(".filter-wrapper").insertAfter($("#member-listing_length"));

    $("body").on("change", ".filter-checkbox, #filter-checkbox", function (e) {
        // t.rows().invalidate().draw();
        if ($("#filter-checkbox").is(":checked")) {
            t.column(1).search('Not printed', true, false, true).draw();
        } else {
            t.search('').columns().search('').draw();
        }
    });
    //Print 
    $('#reports-pdf, #reports-csv, #reports-excel').on('click', function () {
        t.button(`.buttons-${$(this).data('id')}`).trigger();
    });

    //Save Print values
    $('button[id*="member_print_"]').on('click', function (e) {
        let customer_ids = $(this).data('customer_id');
        // swal.showLoading();
        print_badges(customer_ids);
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

const print_badges = async (customer_ids, showRadioInput = true) => {
    if(showRadioInput)
    {
        /* inputOptions can be an object or Promise */
        const inputOptions = new Promise((resolve) => {
            setTimeout(() => {
                resolve({
                    '1': 'Dyno Format',
                    '2': 'Letter Format'
                })
            }, 1000)
        })
    
        const { value: color } = await Swal.fire({
            title: 'Print badges',
            input: 'radio',
            inputOptions: inputOptions,
            inputValue: '1',
            showCancelButton: true,
            confirmButtonText: 'Print',
            inputValidator: (value) => {
                if (!value) {
                    return 'You need to choose something!'
                }
            },
            showLoaderOnConfirm: true,
            preConfirm: (format_type) => {
                var data = new URLSearchParams();
                data.append('action', 'print_badges');
                data.append('customer_ids', customer_ids);
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
                        jQuery('#templates').html(htmlResponse.data);
                        return htmlResponse.data;
                    })
                    .catch(error => {
                        swal.showValidationMessage(
                            `Request failed: ${error}`
                        );
                    });
            },
            allowOutsideClick: false,
            didOpen: () => {
                swal.showLoading();
            },
        })
    
        if (color) {
            setTimeout(() => {
                badgePDF();
            }, 500)
        }

    }else{
        // The rest of the code for printing without asking
    var data = new URLSearchParams();
    data.append('action', 'print_badges');
    data.append('customer_ids', customer_ids);
    data.append('format_type', 2);

    try {
        const response = await fetch(member_object.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: data
        });

        const htmlResponse = await response.json();
        jQuery('#templates').attr('template', 2);
        jQuery('#templates').html(htmlResponse.data);

        setTimeout(() => {
            badgePDF();
        }, 500);
    } catch (error) {
        // Handle error here
        console.error('Request failed:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: `Request failed: ${error}`,
        });
    }
    }

}

const badgePDF = async () => {
    // swal.showLoading();
    const $templates = jQuery('#templates');
    const $badges_items = $templates.find('.badge_1');
    var tpl = $templates.attr("template"),
        doc,
        topMargin,
        leftMargin,
        hBadge,
        wBadge;

    if (tpl === "1") {
        doc = new jsPDF({
            format: ['100', '57'],
            unit: "mm",
        });

        topMargin = 0;
        leftMargin = 0;
        hBadge = 58;
        wBadge = 100;
    } else {
        doc = new jsPDF({
            format: ['217', '281'],
            unit: "mm",
        });

        topMargin = 20;
        leftMargin = 6.5;
        hBadge = 77;
        wBadge = 102;
    }

    var items = $templates.find(".badge_1").length,
        counter = 0,
        images = [],
        itemOrder = 1,
        ids = [],
        members = [];

        $badges_items.each(function (index) {
            var $badge = jQuery(this);
        
            members.push($badge.attr("data-id"));
        
            domtoimage.toPng($badge.get(0))
                .then(async function (dataUrl) {
                    var image = new Image();
                    image.src = dataUrl;
        
                    counter++;
        
                    images.push([$badge.attr("data-id"), image]);
        
                    ids.push($badge.attr("data-reg-id"));
                    if (items === counter) {
                        var newImages = images.reduce(function (r, v) {
                            r[parseInt(v[0])] = v;
                            return r;
                        }, []);
        
                    if (tpl === '1') {
                        for (var i = 0; i < newImages.length; i++) {
                            if (newImages[i]) {
                                doc.addImage(newImages[i][1], 'PNG', leftMargin, topMargin, wBadge, hBadge);

                                if (i + 1 !== newImages.length) {
                                    doc.addPage();
                                }
                            }else{
                                console.log("Image data not found for index:", i);
                            }

                        }
                    } else {
                        for (var i = 0; i < newImages.length; i++) {
                            switch (itemOrder) {
                                case 1:
                                    doc.addImage(newImages[i][1], 'PNG', leftMargin, topMargin, wBadge, hBadge);
                                    itemOrder = 2;
                                    break;
                                case 2:
                                    doc.addImage(newImages[i][1], 'PNG', leftMargin + wBadge, topMargin, wBadge, hBadge);
                                    itemOrder = 3;
                                    break;
                                case 3:
                                    doc.addImage(newImages[i][1], 'PNG', leftMargin, topMargin + hBadge, wBadge, hBadge);
                                    itemOrder = 4;
                                    break;
                                case 4:
                                    doc.addImage(newImages[i][1], 'PNG', leftMargin + wBadge, topMargin + hBadge, wBadge, hBadge);
                                    itemOrder = 5;
                                    break;
                                case 5:
                                    doc.addImage(newImages[i][1], 'PNG', leftMargin, topMargin + hBadge + hBadge, wBadge, hBadge);
                                    itemOrder = 6;
                                    break;
                                case 6:
                                    if (newImages[i]) {
                                        doc.addImage(newImages[i][1], 'PNG', leftMargin + wBadge, topMargin + hBadge + hBadge, wBadge, hBadge);
                                        itemOrder = 1;
                                        doc.addPage();
                                    } else {
                                        console.log("Image data not found for index:", i);
                                    }
                            }
                        }
                    }

                    doc.save("badges.pdf");
                    Swal.close();
                    const data_ids = {
                        action: 'action_printed_in_ids',
                        ids: ids
                        };
                    try {
                        const response = await jQuery.ajax({
                            url: member_object.ajax_url,
                            type: 'POST',
                            data: data_ids,
                            dataType: 'json'
                        });
                    
                        if (response.success) {
                            // console.log(ids)
                            ids.forEach((userId) => {
                                jQuery(`#user_badge_print_${userId}`).html('<b style="color:green;">Printed</b>');
                                jQuery(`#user_badge_print_${userId}`).next('td').find('button').text('Printed');
                                jQuery(`#user_badge_print_${userId}`).parents('tr').attr('data-printed', '1'); //data-printed
                                t.rows( tr ).invalidate().draw();
                                // let rowId = jQuery(`#user_badge_print_${userId}`).parents('tr').attr('id');
                                // const rowData = t.row(`tr[data-id="${rowId}"]`).data();
                                // rowData[colIndex] = 'Printed';
                                // t.row(`tr[data-id="${rowId}"]`).data(rowData).draw(false);
                            });
                        }                        
                    } catch (error) {
                        showAlert('error', error);
                        Swal.close();
                    }
                }
            })
            .catch(function (error) {
                console.error("oops, something went wrong!", error);
                Swal.close();
            });
        });

    // jQuery('#templates').html('');

};
