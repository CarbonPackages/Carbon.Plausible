import manifest from "@neos-project/neos-ui-extensibility";
import CookieButtonContainer from "./CookieButton";

manifest("Carbon.Plausible:CookieButton", {}, (globalRegistry) => {
    const containerRegistry = globalRegistry.get("containers");
    containerRegistry.set("PrimaryToolbar/Right/CookieButton", CookieButtonContainer, "start");
});
