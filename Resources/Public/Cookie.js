(function () {
    window.setCookie = function () {
        // cookie will die in +100 years from now
        // 1 * 60 * 60 * 24 * 365 * 100 = 31536000000
        cookie(31536000000);
    };

    window.deleteCookie = function () {
        cookie(-1);
    };

    var host = document.location.host;
    var mainDomain = host.split(".").slice(-2).join(".");
    var value = "disabledPlausible=true";

    function set(maxAge, domain) {
        document.cookie = value + "; SameSite=Lax; path=/; max-age=" + maxAge + "; domain=" + domain;
    }

    function check(maxAge) {
        var isSet = document.cookie.indexOf(value) !== -1;
        var shouldBeSet = maxAge !== -1;
        if ((shouldBeSet && !isSet) || (!shouldBeSet && isSet)) {
            set(maxAge, host);
        }
    }

    function cookie(maxAge) {
        set(maxAge, mainDomain);
        if (host != mainDomain) {
            check(maxAge);
        }
    }
})();
