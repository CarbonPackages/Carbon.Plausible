function showMessage(element, key, hightlightRow) {
    element.querySelector(".plausible-error--" + key).classList.remove("plausible-hide");
    if (hightlightRow) {
        const row = element.closest("section").querySelector(".plausible-row--" + key);
        if (row) {
            row.classList.add("plausible-row--error");
        }
    }
}

window.addEventListener("load", () => {
    [...document.querySelectorAll(".plausible-markup")].forEach((element) => {
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
                .catch(() => {
                    showMessage(element, "host", true);
                });
            return;
        }
        showMessage(element, "domain", true);
    });
});
