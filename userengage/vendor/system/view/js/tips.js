function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1);
        if (c.indexOf(name) == 0)
            return c.substring(name.length, c.length);
    }
    return "";
}

$(function () {

    var TIPS = $('.tip');

    TIPS.each(function (k)
    {
        id = $(this).attr('id');

        console.log('#' + id);

        $('#' + id).on('click', '.close', function () {

            id = $(this).parent().attr('id');

            console.log(SETTINGS_URL + '&page=update&key=tip_' + id + '&val=1');

            $.get(SETTINGS_URL + '&page=update&key=tip_' + id + '&val=1', function (res) {
                console.log(res);
                $.get(SETTINGS_URL + '&page=update&key=tips&val=0', function (res) {
                    console.log(res);
                });
            });

        });
    });

});