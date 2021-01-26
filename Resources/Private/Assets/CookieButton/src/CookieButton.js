import React, { Component } from "react";
import PropTypes from "prop-types";
import { withCookies, Cookies } from "react-cookie";
import { neos } from "@neos-project/neos-ui-decorators";
import { Button, Icon } from "@neos-project/react-ui-components";
import I18n from "@neos-project/neos-ui-i18n";
import style from "./style.css";
import mergeClassNames from "classnames";

@neos((globalRegistry) => ({
    i18nRegistry: globalRegistry.get("i18n"),
}))
class CookieButton extends Component {
    static propTypes = {
        cookies: PropTypes.instanceOf(Cookies).isRequired,
        i18nRegistry: PropTypes.object.isRequired,
    };

    constructor(props) {
        super(props);
        this.domain = document.location.host.split(".").slice(-2).join(".");
        this.cookies = props.cookies;
        this.state = {
            disabledPlausible: this.cookies.get("disabledPlausible") || false,
        };
    }

    toggleCookie = () => {
        const set = !this.state.disabledPlausible;
        // 1 * 60 * 60 * 24 * 365 * 100 = 31536000000 == 100 years
        const maxAge = set ? 31536000000 : -1;
        this.cookies.set("disabledPlausible", true, {
            path: "/",
            sameSite: "lax",
            maxAge: maxAge,
            domain: this.domain,
        });
        this.setState({ disabledPlausible: set });
    };

    render() {
        const { i18nRegistry } = this.props;
        const disabledPlausible = this.state.disabledPlausible;
        const label = i18nRegistry.translate(
            disabledPlausible ? "ui.disabled.label" : "ui.enabled.label",
            disabledPlausible
                ? "Tracking from this browser is disabled. Click to enable."
                : "Tracking from this browser is enabled. Click to disable.",
            {},
            "Carbon.Plausible"
        );
        const text = i18nRegistry.translate("ui.tracking", "Tracking", {}, "Carbon.Plausible");
        const finalClassName = mergeClassNames(style.text, disabledPlausible ? style.disabled : null);

        return (
            <Button
                className={style.button}
                style="clean"
                hoverStyle="clean"
                aria-label={label}
                title={label}
                onClick={this.toggleCookie}
            >
                <Icon icon="chart-pie" color={disabledPlausible ? "default" : "warn"} />
                <span className={finalClassName}>{text}</span>
            </Button>
        );
    }
}

export default withCookies(CookieButton);
