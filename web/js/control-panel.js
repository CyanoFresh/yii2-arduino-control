var ledURL;
var ledStatusURL;

$(document).ready(function () {
    $.get(ledStatusURL, function (response) {
        if (response === 1) {
            $('#ledCheckbox').prop('checked', true);
        }
    });

    $('#ledCheckbox').change(function () {
        $.get(ledURL, function (response) {
            console.log(response);
        });
    });
});