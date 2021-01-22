function setCookie(domain) {
    // cookie will die in +100 years from now
    // 1 * 60 * 60 * 24 * 365 * 100 = 31536000000
    cookie(domain, 31536000000);
}

function deleteCookie(domain) {
    cookie(domain, -1);
}

function cookie(domain, maxAge) {
    document.cookie = "disabledPlausible=true; path=/; max-age=" + maxAge + "; domain=" + domain;
    window.location.reload();
}
