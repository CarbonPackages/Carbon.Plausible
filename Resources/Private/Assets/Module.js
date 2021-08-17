import {
    selector,
    selectorAll,
    trackingIsDisabled,
    enableTracking,
    disableTracking,
    docClassListToggle,
} from "./Helper";

/**
 * Show a message to the user.
 *
 * @param {NodeElement} element - The element to show the message in.
 * @param {string} key - The key to select
 * @param {boolean} hightlightRow - Whether to highlight the row.
 */
const showMessage = (element, key, hightlightRow) => {
    selector(".plausible-error--" + key, element)?.classList.remove("plausible-hide");
    if (hightlightRow) {
        selector(".plausible-row--" + key, element.closest("section"))?.classList.add("plausible-row--error");
    }
};

/**
 * Set the status if the tracking is enabled or not
 */
const setStatus = () => {
    const disabled = trackingIsDisabled();
    docClassListToggle("-plausible-tracking-disabled", disabled);
    docClassListToggle("-plausible-tracking-enabled", !disabled);
};

setStatus();

window.addEventListener("load", () => {
    const screenshot = selector("main.plausible")?.dataset.screenshot;
    const markups = selectorAll(".plausible-markup");

    if (screenshot === undefined) {
        markups.forEach((element) => {
            const source = selector("code", element)?.innerText.match(/src="([^"]*)/)?.[1];
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

    const moduleWrap = selector(".neos-module-wrap");
    selector("#neos-application")?.remove();
    moduleWrap.style.paddingTop = "15px";

    if (screenshot === "error") {
        selector(".neos-breadcrumb")?.remove();
        moduleWrap.style.paddingBottom = "1px";
        markups.forEach((element, index) => {
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

// Make functions globally available
window.setStatus = setStatus;
window.enableTracking = enableTracking;
window.disableTracking = disableTracking;
