//exhibitor-management-datatable.js
function showAlert(icon, title) {
    Swal.fire({
        // position: 'top-end',
        icon: icon,
        text: title,
        showConfirmButton: true,
        // timer: 1500
    })
}
// Initialize DataTables
jQuery(document).ready(function ($) {
    //Check email is already exists.
    $("#input_18_13, #input_18_27").on('blur', function () {
        let $this = $(this);
        jQuery.ajax({
            url: exhibitor_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'check_email_exist',
                email: $(this).val()
            },
            beforeSend: function () {
                $('body').find(`#gravity-form-container`).prepend('<div id="assistant-spinner"></div>');
            },
            success: function (response) {
                $('body').find(`#gravity-form-container #assistant-spinner`).remove();
                $('body').find('.primary-exhibitors-email-exist').remove();
                if (response.success) {
                    $(`[for="${$this.attr('id')}"]`).append(`<em class="primary-exhibitors-email-exist"><span class="dashicons-before dashicons-yes"></span>${response.data.message}</em>`);
                }
            },
            error: function (xhr, status, error) {
                $('body').find(`#gravity-form-container #assistant-spinner`).remove();
                showAlert('error', xhr.responseText);
            }
        });
    });
    //Exhibitor Status
    $('#gform_19').before($('#exhibitor-profile .exhibitor-status'));
    $(document).on('change', '.status-select', function () {
        var selectedValue = $(this);
        var statusUpdate = true;

        if ((selectedValue.data('assign_booth_number') == 0 || selectedValue.data('assign_booth_number') == 'Not Assigned') && $(this).val() == 'complete') {
            Swal.fire({
                title: 'Exhibitor\'s have not been assigned to any Booth as yet',
                text: "Do you want to set the status to complete?",
                icon: 'warning',
                width: 'auto',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, complete it!'
            }).then((result) => {
                statusUpdate = result.isConfirmed ? true : false;

                if (statusUpdate) {
                    exhibitor_update_status(selectedValue);
                }
            });
        } else {
            exhibitor_update_status(selectedValue);
        }
    });

    function exhibitor_update_status(selectedValue) {
        $.ajax({
            url: exhibitor_object.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'update_company_status',
                company_id: selectedValue.data('row'),
                new_status: selectedValue.val()
            },
            beforeSend: function () {
                $('body').find(`#exhibitor-members-list_wrapper`).prepend('<div id="assistant-spinner"></div>');
            },
            success: function (response) {
                $('body').find(`#exhibitor-members-list_wrapper #assistant-spinner`).remove();
                if (response.success) {
                    $(`select[data-row="${selectedValue.data('row')}"] option`).prop('disabled', false).prop('selected', false);
                    showAlert('success', response.data.message); //'warning','error'
                    $(`select[data-row="${selectedValue.data('row')}"] option[value='${response.data.status}']`).prop('disabled', true).prop('selected', true);
                } else {
                    showAlert('error', response.data.message); //'warning','error'
                }
            },
            error: function (xhr, status, error) {
                $('body').find(`#exhibitor-members-list_wrapper #assistant-spinner`).remove();
                showAlert('error', xhr.responseText);
            }
        });
    }
    if (typeof exhibitor_object !== "undefined" && exhibitor_object?.current_screen == "toplevel_page_exhibitor-management") {
        $(document).on('change', '.plane-to-exhibitor', function () {
            var selectedValue = $(this);
            jQuery.ajax({
                url: exhibitor_object.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'update_plane_to_exhibitor_status',
                    company_id: selectedValue.data('row'),
                    new_status: $(this).val()
                },
                beforeSend: function () {
                    $('body').find(`#exhibitor-members-list_wrapper`).prepend('<div id="assistant-spinner"></div>');
                },
                success: function (response) {
                    $('body').find(`#exhibitor-members-list_wrapper #assistant-spinner`).remove();
                    showAlert('success', 'Successfully updated!');
                },
                error: function (xhr, status, error) {
                    $('body').find(`#exhibitor-members-list_wrapper #assistant-spinner`).remove();
                    showAlert('error', xhr.responseText);
                }
            });
        });
        //DataTable

        let exhibitor_profile = exhibitor_object?.booth_admin;
        var buttonCommon = {
            exportOptions: {
                columns: [0, 16, 2, 17, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 15],
                modifier: {
                    search: 'applied'
                },
                format: {
                    body: function (data, row, column, node) {
                        console.log(column);
                        data = $.fn.DataTable.Buttons.stripData(data, null);            
                        if ((column === 1 || column === 3)) {
                            var stringArray = data.replaceAll('_', ' ').split(" ");
                            var capitalizedArray = stringArray.map(function (element) {
                                return element.charAt(0).toUpperCase() + element.slice(1);
                            });
                            return capitalizedArray.join(" ");
                        }
                        return data;
                    }
                }
            }
        };
        var t = $("#exhibitor-members-list").DataTable({
            // dom: '<"top"i>rt<"bottom"flp><"clear">',
            fixedHeader: true,
            dom: 'Blfrtip',
            "ajax": {
                "url": exhibitor_object.ajax_url,
                "type": "POST",
                "data": {
                    "action": "get_exhibitor_members"
                }
            },
            "columns": [
                { "data": "no" },
                {
                    "data": "status",
                    render: function (data, type, row, meta) {
                        var keyValueArray = [
                            { key: '', value: 'Select status' },
                            { key: 'confirm_contact', value: 'Confirm Contact' },
                            { key: 'account_pending', value: 'Account Pending' },
                            { key: 'account_activated', value: 'Account Activated' },
                            { key: 'booth_pending', value: 'Booth Pending' },
                            { key: 'pending_payment', value: 'Pending Payment' },
                            { key: 'payment_complete', value: 'Payment Complete' },
                            { key: 'complete', value: 'Complete' }
                        ];

                        // Selected key
                        var selectedKey = data; // Replace with the key you want to pre-select
                        var dropdownHtml = `<select data-row="${row.id}" data-assign_booth_number="${row.exhibit_booth_number}" class="status-select">`;

                        keyValueArray.forEach(function (item, index) {
                            var selected = (item.key == selectedKey) || (item.key == '') ? 'selected disabled' : '';
                            // var disabled = (index > 0) ? 'disabled' : '';
                            dropdownHtml += '<option value="' + item.key + '" ' + selected + '>' + item.value + '</option>';
                        });

                        dropdownHtml += '</select>';

                        return dropdownHtml;
                    }
                },
                {
                    "data": "company_name",
                    render: function (data, type, row, meta) {
                        return `<a href="${exhibitor_profile}&company_id=${row.id}">${data}</a>`;
                    }
                },
                {
                    "data": "plan_to_exhibit",
                    render: function (data, type, row, meta) {
                        // Array with key-value pairs
                        var keyValueArray = [
                            { key: '-', value: 'Select plan to exhibit' },
                            { key: 'yes', value: 'Yes' },
                            { key: 'no', value: 'No' },
                            { key: 'no_reply', value: 'No Reply' },
                            { key: 'maybe', value: 'Maybe' },
                        ];

                        // Selected key
                        var selectedKey = data; // Replace with the key you want to pre-select
                        var plan_to_exhibit = `<select data-row="${row.id}" class="plane-to-exhibitor">`;

                        keyValueArray.forEach(function (item, index) {
                            var selected = (item.key === selectedKey) || (item.key === '-') ? 'selected disabled' : '';
                            // var disabled = (index > 0) ? 'disabled' : '';
                            plan_to_exhibit += '<option value="' + item.key + '" ' + selected + '>' + item.value + '</option>';
                        });

                        plan_to_exhibit += '</select>';

                        return plan_to_exhibit;
                    }
                },
                { "data": "booth_count" },
                { "data": "exhibit_booth_number" },
                { "data": "primary_first_name" },
                { "data": "primary_last_name" },
                { "data": "primary_email" },
                { "data": "alternate_first_name" },
                { "data": "alternate_last_name" },
                { "data": "alternate_email" },
                { "data": "exhibit_rep_first_name" },
                { "data": "exhibit_rep_last_name" },
                { "data": "id" },
                { "data": "date_of_registration" },
                { "data": "active_status" },
                { "data": "active_plan_to_exhibit" },

                // Add more columns if needed
            ],
            pageLength: 25,
            aLengthMenu: [
                [10, 25, 50, 100, 200, -1],
                [10, 25, 50, 100, 200, "All"]
            ],
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
                            pageSize: 'A2',
                        }),
                        $.extend(true, {}, buttonCommon, {
                            extend: 'excelHtml5',
                            text: 'Export to Excel',
                        }),
                    ]
                }
            ],
            columnDefs: [
                {
                    searchable: false,
                    targets: [1, 3],
                    orderable: false
                },
            ],
            order: [[2, 'asc']],
            "processing": true,
            scrollX: true,
            // responsive: true
        });
        $(".dt-buttons").prepend($(".year-filter"));
        $(".dataTables_filter").prepend($(".filter-by-status"));
        $(".dataTables_filter").prepend($(".filter_plan_to_exhibit"));
        $(".dataTables_filter").append($(".reset-filter"));

        t.on('order.dt search.dt', function () {
            let i = 1;
            t.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
                this.data(i++);
            });
        }).draw();
        //Filter By Year
        var yearIndex = 0;
        $("#exhibitor-members-list th").each(function (i) {
            if ($(this).text() == "Date Approved Exhibitor List") {
                yearIndex = i; return false;
            }
        });
        $.fn.dataTable.ext.search.push(
            function (settings, data, dataIndex) {
                var selectedItem = $('#year-filter').val();
                let year = data[yearIndex];
                if (selectedItem === "" || year.includes(selectedItem)) {
                    return true;
                }
                return false;
            }
        );
        $("#year-filter").change(function (e) {
            t.draw();
        });
        //End Filter By Year
        //Filter By Current Status"
        $.fn.dataTable.ext.search.push(
            function (settings, data, dataIndex) {
                var currentStatus = $('#filter-by-status').val();  
                var category = data.filter(el => el == currentStatus );
                if ( currentStatus === "" || category.length > 0 ) {
                    return true;
                }
                return false;
            }
        );
        $("#filter-by-status").change(function (e) {
            t.draw();
        });
        //End Filter by Current Status
        //Filter by plan to exhibit
        $.fn.dataTable.ext.search.push(
            function (settings, data, dataIndex) {
                var currentPlanTo = $('#filter_plan_to_exhibit').val();
                var category = data.filter(el => el == currentPlanTo );
                if ( currentPlanTo === "" || category.length > 0 ) {
                    return true;
                }
                return false;
            }
        );
        $("#filter_plan_to_exhibit").change(function (e) {
            t.draw();
        });
        //End Filter by plan to exhibit
        //Reset All Filters
        // Clear all filters
        $('.reset-filter').on('click', function () {
            t.search('').columns().search('').draw();
            $('#year-filter').val('').trigger('change');
            $('#filter-by-status').val('').trigger('change');
            $('#filter_plan_to_exhibit').val('').trigger('change');
        });
        //End Reset All Filters
        t.draw();
    }
    //Booth Admin other pages
    if (typeof exhibitor_object !== "undefined" && exhibitor_object?.current_screen == "asgmt-exhibits_page_edit-exhibitor-profile") {
        //calculate price
        $('#calculatePrice').on('change', function () {
            var quantity = parseInt($(this).val());
            quantity = isNaN(quantity) ? 0 : quantity;
            var price = quantity * parseInt($(this).data('price'));
            $('#totalValue').text(price);
        });
        $('#send-invoice-assign-booth').on('click', function (event) {
            event.preventDefault();
            var productsValues = jQuery('#calculatePrice').val();
            productsValues = isNaN(productsValues) ? 0 : productsValues;
            console.log(jQuery(this).data('status'));
            if(jQuery(this).data('status') == 0)
            {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sorry!',
                    text: 'Previous Invoices are still in Pending Status, We are unable to process new Invoices at the moment',
                    // footer: '<a href="">Why do I have this issue?</a>'
                  })
                // showAlert('warning', 'Sorry! Previous Invoices are still in Pending Status, You can not send any New Invoices at the moment!');//'warning','error'   
            }
            else if (productsValues > 0) 
            {
                $.ajax({
                    url: exhibitor_object.ajax_url,
                    method: 'POST',
                    data: {
                        action: 'assign_booth_products',
                        products_ids: jQuery('#calculatePrice').data('product_id'),
                        qty: productsValues,
                        company_id: $('input[name="company_id"]').val()
                    },
                    beforeSend: function () {
                        $('body').find(`.assign-booth-products`).prepend('<div id="assistant-spinner"></div>');
                    },
                    success: function (response) {
                        $('body').find(`.assign-booth-products`).find('#assistant-spinner').remove();
                        if (response.success) {
                            $('#send-invoice-assign-booth').attr('data-status', '0');
                            $('#send-invoice-assign-booth').prop('disabled', true);
                            showAlert('success', 'Sent Invoice successfully!');//'warning','error'
                        } else {
                            showAlert('error', 'Something went wrong please try again!');//'warning','error'
                        }
                    },
                    error: function (xhr, status, error) {
                        $('body').find(`.assign-booth-products`).find('#assistant-spinner').remove();
                        showAlert('error', 'Something went wrong please try again!');//'warning','error'
                    }
                });
            } else {
                showAlert('error', 'Quantity should be greater than zero');//'warning','error'                      
            }
        });
        //Save booth count
        $('#save-count-booth').on('click', function (event) {
            event.preventDefault();
            var productsValues = jQuery('#calculatePrice').val();
            productsValues = isNaN(productsValues) ? 0 : productsValues;
            $.ajax({
                url: exhibitor_object.ajax_url,
                method: 'POST',
                data: {
                    action: 'add_booth_count',
                    company_id: $('input[name="company_id"]').val(),
                    count: productsValues
                },
                beforeSend: function () {
                    $('body').find(`.assign-booth-products`).prepend('<div id="assistant-spinner"></div>');
                },
                success: function (response) {
                    $('body').find(`.assign-booth-products`).find('#assistant-spinner').remove();
                    if (response.success) {
                        showAlert('success', `${productsValues} ${productsValues > 1 ? 'Booths' : 'Booth'} Added Successfully`);//'warning','error'
                    } else {
                        showAlert('error', 'Something went wrong please try again!');//'warning','error'
                    }
                },
                error: function (xhr, status, error) {
                    $('body').find(`.assign-booth-products`).find('#assistant-spinner').remove();
                    showAlert('error', 'Something went wrong please try again!');//'warning','error'
                }
            });
        });
        //Add new assistant 
        $('#add-new-assistant').on('click', function () {
            $('.add-assistant-form').slideDown();
            $('#hide-new-assistant').show();
        });
        $('#hide-new-assistant').on('click', function () {
            $('.add-assistant-form').slideUp();
            $(this).hide();
        });
        $("#tabs").tabs();
        $("#booth-admin-tabs").tabs();
        //Add Representative
        $('#representative-form').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var firstName = form.find('input[name="representative_first_name"]').val();
            var lastName = form.find('input[name="representative_last_name"]').val();
            var email = form.find('input[name="representative_email"]').val();
            var contact = form.find('input[name="representative_contact"]').val();

            if (!firstName || !lastName || !email || !contact) {
                showAlert('error', 'Please fill in all required fields');
                return;
            }
            var formData = $(`#representative-form`).serialize();
            $.ajax({
                url: exhibitor_object.ajax_url,
                method: 'POST',
                data: formData,
                beforeSend: function () {
                    $('body').find(`#booth-admin-tabs-representative`).prepend('<div id="assistant-spinner"></div>');
                },
                success: function (response) {
                    if (response.success) {
                        $('body').find(`#booth-admin-tabs-representative`).find('#assistant-spinner').remove();
                        showAlert('success', 'Representative added successfully!');
                    } else {
                        showAlert('error', 'Something went wrong please try again!');//'warning','error'
                    }
                },
                error: function (xhr, status, error) {
                    $('body').find(`#booth-admin-tabs-representative`).find('#assistant-spinner').remove();
                    showAlert('error', 'Something went wrong please try again!');//'warning','error'
                }
            });
        });
        //notes-save
        $('#notes-form').on('submit', function (e) {
            e.preventDefault();
            var form = $(this);
            var notes = form.find('textarea[name="notes"]').val();
            var formData = $(`#notes-form`).serialize();
            $.ajax({
                url: exhibitor_object.ajax_url,
                method: 'POST',
                data: formData,
                beforeSend: function () {
                    $('body').find(`#booth-admin-tabs-notes`).prepend('<div id="assistant-spinner"></div>');
                },
                success: function (response) {
                    if (response.success) {
                        $('body').find(`#booth-admin-tabs-notes`).find('#assistant-spinner').remove();
                        showAlert('success', 'Notes updated successfully!');
                    } else {
                        showAlert('error', 'Something went wrong please try again!');//'warning','error'
                    }
                },
                error: function (xhr, status, error) {
                    $('body').find(`#booth-admin-tabs-notes`).find('#assistant-spinner').remove();
                    showAlert('error', 'Something went wrong please try again!');//'warning','error'
                }
            });
        });
    }
    //Disabled Assigned Booth Numbers
    if(typeof exhibitor_object.assigned_booth_numbers != 'undefined')
    {
        let booth_numbers = exhibitor_object?.assigned_booth_numbers?.replace(/\s+/g, '');
        if (typeof acf != 'undefined') {
            acf.addAction('append', function ($el) {
                var $selectField = $el.find('[data-name="assigned_booth_number"] select');
                if (typeof booth_numbers != 'undefined') {
                    if (booth_numbers.includes(',')) {
                        booth_numbers.split(',').forEach((element) => {
                            $selectField.find(`option[value="${element}"]`).remove();
                        });
                    }else{
                        $selectField.find(`option[value="${booth_numbers}"]`).remove();
                    }
                }
            });
        }
    }
    $( "#accordion" ).accordion(
      {
        collapsible: true,
        active: false,
        heightStyle: "content" ,
        icons: {
          header: "ui-icon-circle-arrow-e",
          activeHeader: "ui-icon-circle-arrow-s"
        }
      }
    );
    $(document).on("click", "input[name='is_primary'], input[name='is_alternate']", function(){
        if($(this).attr('name') == 'is_primary' && $(this).attr('id') == 'primary')
        {
            $('#primary_sec').prop('checked', true);
        }else if($(this).attr('name') == 'is_alternate' && $(this).attr('id') == 'alternate'){            
            $('#alternate_sec').prop('checked', true);
        }else if($(this).attr('name') == 'is_primary' && $(this).attr('id') == 'alternate_sec'){
            $('#alternate').prop('checked', true);
        }else{
            $('#primary').prop('checked', true);
        }
    });   
    $('#filter-by-status').on('change', function(){
        $('body').find('#total-booth-count-by-companies-status').find('p').hide();
        $('body').find(`.${$(this).val()}`).show();
    });
});
jQuery(window).load(function($){
    jQuery.ajax({
        url: exhibitor_object.ajax_url,
        method: 'POST',
        data: {action: 'get_total_booth_count_by_companies_status'},
        success: function (response) {
            if (response.success) {
                Object.keys(response.data).forEach( (item) => {
                    // `<p class="${item}">${capitalizeWordsAndJoin(item, '_')} : Booths of ${response.data[item]}</p>`
                    jQuery('body').find(`#total-booth-count-by-companies-status`).append(`<p class="${item}" style="display:none;">Total <b>${capitalizeWordsAndJoin(item, '_')}</b> Booths <b>${response.data[item]}</b></p>`);
                });
            }
        }
    });
})

function capitalizeWordsAndJoin(str, explodeBy) {
    const words = str.split(`${explodeBy}`);
    const capitalizedWords = words.map(word => word.charAt(0).toUpperCase() + word.slice(1));
    const capitalizedString = capitalizedWords.join(' ');
    return capitalizedString;
}