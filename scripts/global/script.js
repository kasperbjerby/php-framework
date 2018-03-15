function Alert(title, content, color, callback) {
    if(!color) {
        color = "red";
    }
    
    $.confirm({
        title: title,
        content: content,
        type: color,
        typeAnimated: true,
        escapeKey: true,
        backgroundDismiss: true,
        theme: 'modern',
        boxWidth: '25vw',
        useBootstrap: false,
        buttons: {
            close: {
                text: 'Ok',
                btnClass: 'btn-default'
            }
        },
        onClose: function () {
            if($.isFunction(callback)) {
                callback();
            }
        }
    });
}