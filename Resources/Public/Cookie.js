function setCookie() {
    // cookie will die in +100 years from now
    // 1 * 60 * 60 * 24 * 365 * 100 = 31536000000
    cookie(31536000000);
}

function deleteCookie() {
    cookie(-1);
}

function cookie(maxAge) {
    var domain = document.location.host.split(".").slice(-2).join(".");
    document.cookie = "disabledPlausible=true; SameSite=Lax; path=/; max-age=" + maxAge + "; domain=" + domain;
}
