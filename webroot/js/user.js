
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

// function closeIt()
// {
//     alert('oooooo');
//     return "Пожалуйста, не закрывайте меня!";
// }
// window.onbeforeunload = closeIt;




