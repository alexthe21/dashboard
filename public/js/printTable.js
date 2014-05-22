/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function() {
    $('#gencsv').click(function() {
        var myTableArray = [];
        var arrayOfHeaders = [];
        var tableData = $('table th');
        if (tableData.length > 0) {
            tableData.each(function() {
                arrayOfHeaders.push('"' + $(this).text() + '"');
            });
            myTableArray.push(arrayOfHeaders);
        }
        $("table tr").each(function() {
            var arrayOfThisRow = [];
            var tableData = $(this).find('td');
            if (tableData.length > 0) {
                tableData.each(function() {
                    var string = '"' + $(this).text() + '"';
                    string = string.replace(/ /g, '%20');
                    arrayOfThisRow.push(string);
                });
                myTableArray.push(arrayOfThisRow);
            }
        });
        var csvRows = [];

        for (var i = 0, l = myTableArray.length; i < l; ++i) {
            csvRows.push(myTableArray[i].join(';'));
        }

        var csvString = csvRows.join("%0A");
        var a = document.createElement('a');
        //a.href = 'data:attachment/csv;charset=utf-8,' + csvString;
        a.href = 'data:text/csv;charset=utf-8,' + csvString;
        a.target = '_blank';
        a.download = 'myFile_unicode.csv';

        document.body.appendChild(a);
        a.click();
    });
});

