// const jsPDF = require('jsPDF');
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
    $('#reports-pdf, #reports-csv, #reports-excel').on('click', function (e) {
        let exportType;
        switch ($(this).attr('id')) {
            case 'reports-pdf':
                exportType = 'pdf';
                break;
            case 'reports-excel':
                exportType = 'excel';
                break;
            default:
                exportType = 'csv';
        }
        registration_report_show(exportType);
    });

    function registration_report_show(exportType) {
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
                    if (response.data.analytics) {
                        $('#analytic-views-today').text(`${response?.data?.analytics?.count_today}`);
                        $('#analytic-views-last-seven-days').text(`${response?.data?.analytics?.count_last_7_days}`);
                        $('#analytic-views-this-month').text(`${response?.data?.analytics?.count_current_month}`);
                        $('#analytic-views-this-year').text(`${response?.data?.analytics?.count_current_year}`);
                    }
                    if (exportType) { jsonToExportType(exportType, response.data.data, response.data.filename); }

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
    function jsonToExportType(exportType, jsonData, filename) {
        switch (exportType) {
            case 'pdf':
                generatePDF(jsonData, filename);
                break;
            case 'excel':
                generateExcel(jsonData, filename);
                break;
            default:
                generateCSV(jsonData, filename);
        }
    }
    function generateCSV(jsonData, filename) {
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
    function generatePDF(jsonData, filename) {
        // Create a new jsPDF instance
        const { jsPDF } = window.jspdf;
        var doc = new jsPDF({
            orientation: "landscape",
            format: 'a2'
        });
        // Define the columns and rows for the table
        // var columns = Object.keys(jsonData[0]);
        // // var columns = Object.keys(jsonData[0]).map( header => explodeAndCapitalize(header));
        // // console.log(columns)
        // // console.log(header)
        // var rows = jsonData.map( items => {
        //     let d= Object.keys(items).map( item => (/^\d+$/.test(items[item])) ? `"${item}": "${items[item].toString()}"` : `"${item}": "${items[item]}"`).join(',');
        //     return JSON.parse(`{${d}}`);
        //   });
        
        // // Set the desired font size and style
        // // doc.setFontSize(10);
        // // doc.setFont("helvetica", "bold");

        // // Set the table header
        // var currentYear = (new Date()).getFullYear();
        // // doc.text(`Registration Reports - ${currentYear}`, 10, 10,{align : 'center'});
        // doc.table(10, 20, rows, columns,
        // {
        //     // autoSize
        // });

        // doc.save(`${filename}.pdf`);

        var columns = Object.keys(jsonData[0]).map( header => explodeAndCapitalize(header));
        var rows = jsonData.map( items => {
          return Object.keys(items).map( item => items[item]);
        });
        var currentYear = (new Date()).getFullYear();
        doc.setLineWidth(2);
        doc.text(270,15, "Registration Reports " + currentYear);
        doc.autoTable({
          head: [columns],
          body: rows,
          margin: { top: 25 },
          styles: {
            minCellHeight: 10,
            halign: "left",
            valign: "top",
            theme: 'grid',
            fontSize: 10,
            headStyles :{lineWidth: 0,fillColor: [247, 195, 56],textColor: [255,255,255]}
          },
        });
        doc.save(`${filename}.pdf`);
    }

    function generateExcel(jsonData, filename) {
        // Create a new workbook
        var workbook = XLSX.utils.book_new();
        // Convert the JSON data to a worksheet
        var worksheet = XLSX.utils.json_to_sheet(jsonData);
        // Add the worksheet to the workbook
        XLSX.utils.book_append_sheet(workbook, worksheet, "Sheet 1");
        // Generate the Excel file binary data
        var excelData = XLSX.write(workbook, { type: "binary", bookType: "xlsx" });
        // Create a Blob object from the binary data
        var blob = new Blob([s2ab(excelData)], { type: "application/octet-stream" });
        // Create a temporary anchor element
        var a = document.createElement("a");
        a.setAttribute("id", "generateExcel");
        a.href = URL.createObjectURL(blob);
        a.download = `${filename}.xlsx`;
        a.click();

        // Cleanup
        setTimeout(function () {
            URL.revokeObjectURL(a.href);
            a.parentNode.removeChild(a);
        }, 100);
    }

    // Utility function to convert string to ArrayBuffer
    function s2ab(s) {
        var buf = new ArrayBuffer(s.length);
        var view = new Uint8Array(buf);
        for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xff;
        return buf;
    }


    function escapeField(field) {
        if (typeof field == 'string' && field.includes(',')) {
            field = field.replaceAll(',', '');
        }
        return field;
    }
    // Function to create table headers from JSON data
    let allowedKeys = ['product_name', 'first_name', 'last_name', 'company', 'order_date', 'status'];
    function createTableHeaders(data) {
        var headers = '';
        var keys = Object.keys(data[0]); // Get keys from the first object
        headers += `<th>No.</th>`;
        // $.each(keys, function (index, key) {
        allowedKeys.map(key => {
            // if (key === 'first_name' || key === 'last_name' || key === 'company' || key === 'product_name' || key === 'order_date' || key === 'status') {
            if (key === 'product_name') {
                headers += `<th>Registration Type</th>`;
            } else if (key === 'order_date') {//Transaction Date
                headers += `<th>Transaction Date</th>`;
            } else {//Transaction Date
                headers += `<th>${explodeAndCapitalize(key)}</th>`;
            }
            // }
        });
        // });
        return headers;
    }

    // Function to create table rows from JSON data
    function createTableRows(data) {
        var rows = '';
        $.each(data, function (index, item) {
            rows += '<tr>';
            rows += `<td>${index + 1}</td>`;
            // $.each(item, function (key, value, index) {
            allowedKeys.map(key => {
                if (key === 'first_name' || key === 'last_name' || key === 'company' || key === 'product_name' || key === 'order_date' || key === 'status') {
                    if (key === 'order_date') {
                        var date = new Date(item[key].split(' ')[0]);
                        var month = date.getMonth() + 1;
                        var day = date.getDate();
                        var year = date.getFullYear();

                        // Format the date as "m-d-Y"
                        var formattedDate = month + "-" + day + "-" + year;
                        rows += `<td>${formattedDate}</td>`;
                    }
                    else { rows += `<td>${item[key]}</td>`; }
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
