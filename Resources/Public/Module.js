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

function showMessage(element, key, hightlightRow) {
    element.querySelector(".-js-error-" + key).classList.remove("neos-hide");
    [...element.closest("section").querySelectorAll(".-js-row-" + key + " td")].forEach((td) => {
        td.style.background = "#ff8700";
    });
}

window.addEventListener("load", () => {
    [...document.querySelectorAll(".-js-source-check")].forEach((element) => {
        const code = element.querySelector("code");
        const match = code ? code.innerText.match(/src="([^"]*)/) : null;
        const source = match ? match[1] : null;
        if (source) {
            fetch(source)
                .then((response) => {
                    if (response.status >= 300) {
                        showMessage(element, "plausible");
                    }
                })
                .catch((error) => {
                    showMessage(element, "host", true);
                });
            return;
        }
        showMessage(element, "domain", true);
    });
});
