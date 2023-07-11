jQuery(document).ready(function ($) {
    'use strict'
    function showAlert(icon, title) {
        Swal.fire({
            icon: icon,
            text: title,
            showConfirmButton: true
        })
    }
    var dateFormat = "mm-dd-yy";
    var currentDate = new Date();
    var from = $("#custom_date_from").datepicker({
        changeMonth: true,
        maxDate: currentDate,
        dateFormat: dateFormat,
        numberOfMonths: 3
    }).on("change", function () {
        var selectedDate = getDate(this);
        to.datepicker("option", "minDate", selectedDate);
        var toDate = getDate(to[0]);
        if (selectedDate && toDate && selectedDate > toDate) {
            to.datepicker("setDate", selectedDate); // Set "to" date to "from" date
        }
        registration_report_show();
    });

    var to = $("#custom_date_to").datepicker({
        // defaultDate: "+1w",
        changeMonth: true,
        maxDate: currentDate,
        dateFormat: dateFormat,
        numberOfMonths: 3
    }).on("change", function () {
        var selectedDate = getDate(this);
        to.datepicker("option", "minDate", selectedDate);

        // Additional validation to check if "from" date is greater than "to" date
        var toDate = getDate(to[0]);
        if (selectedDate && toDate && selectedDate > toDate) {
            to.datepicker("setDate", selectedDate); // Set "to" date to "from" date
        }
        registration_report_show();
    });

    function getDate(element) {
        var date;
        try {
            date = $.datepicker.parseDate(dateFormat, element.value);
        } catch (error) {
            date = null;
        }
        return date;
    }
    $('#registration-reports').on('submit', function (e) {
        e.preventDefault();
        registration_report_show(true);
    });

    function registration_report_show(csvGenerator = false)
    {
        var formData = $('#registration-reports').serialize();
        $.ajax({
            url: reports_obj.ajax_url,
            method: 'POST',
            data: formData,
            beforeSend: function () {
                $('body').find(`.registration-reports-container`).prepend('<div id="assistant-spinner"></div>');
            },
            success: function (response) {
                $('body').find(`.registration-reports-container`).find('#assistant-spinner').remove();
                if (response.success) {
                    if (csvGenerator){ jsonToCsv(response.data.data, response.data.filename);}
                    
                    $('.registration-reports-result').html(`
                        <table id="registration-reports-result-table">
                            <thead>
                                <tr id="reports-result-headerRow"></tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    `);
                    setTimeout(() => {
                        $('body').find('#reports-result-headerRow').html(createTableHeaders(response.data.data));
                        $('body').find('#registration-reports-result-table tbody').html(createTableRows(response.data.data));
                    }, 200);
                } else {
                    showAlert('error', 'No record found.');//'warning','error'
                    $('body').find('.registration-reports-result').html('<p>No Record Found</p>');
                }
            },
            error: function (xhr, status, error) {
                $('body').find(`.registration-reports-container`).find('#assistant-spinner').remove();
                $('body').find('.registration-reports-result').html('<p>No Record Found</p>');
                // showAlert('error', 'Something went wrong please try again!');//'warning','error'
            }
        });
    }
    function jsonToCsv(jsonData, filename) {
        const jsonArray = jsonData;
        const headers = Object.keys(jsonData[0]);
        let csv = headers.join(',') + '\n';

        jsonArray.forEach((item) => {
            const values = headers.map((header) => escapeField(String(item[header])));
            csv += values.join(',') + '\n';
        });

        // Create a CSV Blob
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });

        // Create a temporary anchor element
        const link = document.createElement('a');
        if (link.download !== undefined) {
            // Set the download attribute with the filename
            link.setAttribute('href', URL.createObjectURL(blob));
            link.setAttribute('download', filename + '.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    }
    function escapeField(field) {
        if (typeof field == 'string' && field.includes(',')) {
            field = field.replaceAll(',', '');
        }
        return field;
    }
    // Function to create table headers from JSON data
    function createTableHeaders(data) {
        var headers = '';
        var keys = Object.keys(data[0]); // Get keys from the first object
        headers += `<th>No.</th>`;
        $.each(keys, function (index, key) {
            if (key === 'first_name' || key === 'last_name' || key === 'company' || key === 'product_name' || key === 'order_date' || key === 'status') {
                if (key === 'product_name') {
                    headers += `<th>Registration Type</th>`;
                } else if(key === 'order_date') {//Transaction Date
                    headers += `<th>Transaction Date</th>`;
                } else {//Transaction Date
                    headers += `<th>${explodeAndCapitalize(key)}</th>`;
                }
            }
        });
        return headers;
    }

    // Function to create table rows from JSON data
    function createTableRows(data) {
        var rows = '';
        $.each(data, function (index, item) {
            rows += '<tr>';
            rows += `<td>${index + 1}</td>`;
            $.each(item, function (key, value, index) {
                if (key === 'first_name' || key === 'last_name' || key === 'company' || key === 'product_name' || key === 'order_date' || key === 'status')
                {
                    if (key === 'order_date') {
                        var date = new Date(value.split(' ')[0]);
                        var month = date.getMonth() + 1;
                        var day = date.getDate();
                        var year = date.getFullYear();

                        // Format the date as "m-d-Y"
                        var formattedDate = month + "-" + day + "-" + year;
                        rows += `<td>${formattedDate}</td>`;
                    }
                    else{rows += `<td>${value}</td>`;}
                }
            });
            rows += '</tr>';
        });
        return rows;
    }

    function explodeAndCapitalize(str) {
        var parts = str.split('_');
        for (var i = 0; i < parts.length; i++) {
            parts[i] = parts[i].charAt(0).toUpperCase() + parts[i].slice(1);
        }
        return parts.join(' ');
    }
    $(document).on('change', '#product-select-options input[type="checkbox"], #registration_year', function () {
        registration_report_show();
    });
});
