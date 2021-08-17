import { selectorAll, trackingIsDisabled, toggleTracking } from "./Helper";

const disabledStatus = selectorAll(".-plausible-disabled");
const enabledStatus = selectorAll(".-plausible-enabled");

const setStatus = (disable) => {
    disabledStatus.forEach((message) => (message.style.display = disable ? null : "none"));
    enabledStatus.forEach((message) => (message.style.display = disable ? "none" : null));
};

setStatus(trackingIsDisabled());
selectorAll(".-plausible-status").forEach((status) => {
    status.style.display = null;
});
selectorAll(".-plausible-button").forEach((button) => {
    button.addEventListener("click", () => setStatus(toggleTracking()));
    button.style.display = null;
});
