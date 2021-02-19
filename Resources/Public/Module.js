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
    const screenshot = document.querySelector("main.plausible").dataset.screenshot;
    const markup = [...document.querySelectorAll(".plausible-markup")];

    if (screenshot === undefined) {
        markup.forEach((element) => {
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
        return;
    }

    if (screenshot === "error") {
        markup.forEach((element, index) => {
            if (index === 0) {
                showMessage(element, "domain", true);
                return;
            }
            if (index === 1) {
                showMessage(element, "host", true);
                return;
            }
            if (index === 2) {
                showMessage(element, "plausible", true);
                return;
            }
        });
    }
});
