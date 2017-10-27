

function confirmDelete() {

    if (confirm('Удалить этот пункт?')) {
        return true;
    } else {
        return false;
    }
}

function myAjax(uri, data, success, error) {

    $.ajax({
        url: uri,
        type: "POST",
        data: data,
        // dataType: "text",
        error: error,
        success: success
    })
}

function error() {
    alert('Error ajax');
}








