import { selectorAll } from "./Helper";

const dataName = "data-analytics";
const clickEventName = "click";
const auxclickEventName = "auxclick";

/**
 * Iterate over elements and add event listeners
 *
 * @param {string} tagName - name of the tag to search for
 * @param {function} callback - callback function
 */
const registerAnalyticsEvents = (tagName, callback) => {
    selectorAll(tagName + "[data-analytics]").forEach((element) => {
        [clickEventName, auxclickEventName].forEach((eventType) => element.addEventListener(eventType, callback));
    });
};

/**
 * Handle click event
 *
 * @param {Event} event - click event
 */
const handleLinkEvent = (event) => {
    let link = event.target;
    const middle = event.type == auxclickEventName && event.which == 2;
    const click = event.type == clickEventName;

    while (link && (typeof link.tagName == "undefined" || link.tagName.toLowerCase() != "a" || !link.href)) {
        link = link.parentNode;
    }

    if (middle || click) {
        registerEvent(link.getAttribute(dataName));
    }

    // Delay navigation so that Plausible is notified of the click
    if (!link.target && !(event.ctrlKey || event.metaKey || event.shiftKey) && click) {
        setTimeout(() => {
            location.href = link.href;
        }, 150);
        event.preventDefault();
    }
};

/**
 * Handle form event
 *
 * @param {Event} click - click event
 */
const handleFormEvent = (event) => {
    event.preventDefault();

    registerEvent(event.target.getAttribute(dataName));

    setTimeout(() => {
        event.target.form.submit();
    }, 150);
};

/**
 * Parse data and call plausible
 * Using data attribute in html eg. data-analytics='"Register", {"props":{"plan":"Starter"}}'
 *
 * @param {string} data - plausible event "Register", {"props":{"plan":"Starter"}}
 */
const registerEvent = (data) => {
    // break into array
    const attributes = data.split(/,(.+)/);

    // Parse it to object
    const events = [JSON.parse(attributes[0]), JSON.parse(attributes[1] || "{}")];

    window.plausible(...events);
};

registerAnalyticsEvents("a", handleLinkEvent);
registerAnalyticsEvents("button", handleFormEvent);
