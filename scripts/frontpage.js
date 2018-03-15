$(function() {
    $("form").on("submit", function(e) {
        e.preventDefault();
        
        var form = $(this);
        
        form.find("input").prop("disabled", true);
        
        var username = $("input[name='username'").val();
        
        $.post("#",{
            login: true,
            username: username,
            password: $("input[name='password'").val()
        }).done(function(data) {
            if(data === "true") {
                $("h1, form").fadeOut(function() {
                    $("h1").text("Welcome, " + username).fadeIn();
                    form.fadeIn().find("input:not([type='submit'])").hide();
                    form.find("input[type='submit']").val("Logout");
                });
            } else if(data === "false") {
                $("h1, form").fadeOut(function() {
                    $("h1").text("Frontpage").fadeIn();
                    form.fadeIn().find("input").val("").show();
                    form.find("input[type='submit']").val("Log in");
                });
            } else {
                Alert("Login failed!", data);
            }
        }).fail(function() {
            Alert("Something went wrong!", "Failed to connect to the server!");
        }).always(function() {
            form.find("input").prop("disabled", false);
        })
    });
});