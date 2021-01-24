[![Latest stable version]][packagist] [![Total downloads]][packagist] [![License]][packagist] [![GitHub forks]][fork] [![GitHub stars]][stargazers] [![GitHub watchers]][subscription]

# Carbon.Plausible Package for Neos CMS

Easily integrate [Plausible Analytics][plausible] into your [Neos site][neos].

## Introduction

[Plausible] is a lightweight and open-source website analytics tool. It doesn’t use cookies and is fully compliant with GDPR, CCPA, and PECR. This plugin is meant to remove all friction from adding the [Plausible Analytics tracking script code] to your Neos site. All you need to do is define your [Plausible domain] in your Neos [`Settings.yaml`] file.

## Features

-   Multi-site compatibility
-   Backend module
-   Check if the requested domain matches the defined domain to track
-   Enabled per default only on `Production` environment

### Multi-site compatibility

If you run a multi-site setup, we got you covered! You can set different trackings for the sites based on the root node name.

### Backend module

This package adds a backend module to your Neos instance, which helps check your configuration and opt-out your browser for tracking.

![screenshot of backend module]

It also checks if the resulting javascript path don't return a 404 error:

![error in the backend module]

### NodeType mixins for disable tracking on a document or set custom events

This package contains two mixins:

-   [Carbon.Plausible.Mixin:CustomEvent]: This allows you to set [custom events] to a document via the inspector. Of course, you can do this also directly in your JavaScript or Fusion
-   [Carbon.Plausible.Mixin:DoNotTrack]: This allows you to disable the tracking for a specific document

![video of the inspector]

## Installation

Run the following command in your site package

```bash
composer require --no-update carbon/plausible
```

Then run `composer update` in your project root.

## Configuration

### Single-site setup

If you have a single site setup, you can adjust the configuration under the key `Carbon.Plausible.default` in your [`Settings.yaml`]:

| Key                | Default | Description                                                                                                                                                                                                                                                                                                                                  |
| ------------------ | ------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `domain`           | `null`  | Set here the [plausible domain]. This setting is required                                                                                                                                                                                                                                                                                    |
| `host`             | `null`  | If you have set a [custom domain], you can set it here. Example: `stats.jonnitto.ch`                                                                                                                                                                                                                                                         |
| `outboundLinks`    | `false` | If you want the enable [outbound link click tracking], set this to `true`                                                                                                                                                                                                                                                                    |
| `hashBasedRouting` | `false` | If you want the enable [Hash-based routing], set this to `true`                                                                                                                                                                                                                                                                              |
| `exclusions`       | `false` | If you want to [exclude specific pages] from the analytics, you can set an array with strings or a string. If you want to load just the [exclusion variant], set this to `true`                                                                                                                                                              |
| `customEvents`     | `false` | If you want to set [custom events] in your javascript, set this to `true` or a string. If set to a string, this whole string gets included on every document. If you set custom events via Fusion or the [Carbon.Plausible.Mixin:CustomEvent] mixin, you don’t have to set it to `true`. The snippet gets activated automatically if needed. |

### Multisite setup

If you run multiple sites on one Neos installation, you can set this under the key `Carbon.Plausible.sites` in your [`Settings.yaml`]. Be aware that if you set one value in `Carbon.Plausible.default`, these are set as the new fallback value. For example, if you set `Carbon.Plausible.default.outboundLinks` to `true`, is `outboundLinks` set to `true` per default for all sites. Of course, you can disable this again if you set this to `false` on your site setting.

Example:

```yaml
Carbon:
    Plausible:
        sites:
            myfirstsite:
                host: stats.domain.com
                domain: domain.com
                outboundLinks: true
            mysecondsite:
                domain: domain.org
                hashBasedRouting: true
                exclusions: "/blog4, /rule/*, /how-to-*, /*/admin"
            mythirdsite:
                domain: domain.net
                customEvent: "plausible('Download', {props: {method: 'HTTP'}})"
                exclusions:
                    - /blog4
                    - /rule/*
                    - /how-to-*
                    - /*/admin
```

The key of the site (e.g. `myfirstsite`) is the root node name found under Administration » Sites Management.

## Fusion Component

The main Fusion component is [Carbon.Plausible:Component.TrackingCode]. This component gets included into [Neos.Neos:Page] under the path `plausibleTrackingCode`. So if you want to add a [custom event][custom events] to a ceratin document, you can do it like this:

```
prototype(Vendor.Site:Document.NotFound) < prototype(Neos.Neos:Page) {
    plausibleTrackingCode.customEvents = 'plausible("404",{ props: { path: document.location.pathname } });'
}
```

[packagist]: https://packagist.org/packages/carbon/plausible
[latest stable version]: https://poser.pugx.org/carbon/plausible/v/stable
[total downloads]: https://poser.pugx.org/carbon/plausible/downloads
[license]: https://poser.pugx.org/carbon/plausible/license
[github forks]: https://img.shields.io/github/forks/CarbonPackages/Carbon.Plausible.svg?style=social&label=Fork
[github stars]: https://img.shields.io/github/stars/CarbonPackages/Carbon.Plausible.svg?style=social&label=Stars
[github watchers]: https://img.shields.io/github/watchers/CarbonPackages/Carbon.Plausible.svg?style=social&label=Watch
[fork]: https://github.com/CarbonPackages/Carbon.Plausible/fork
[stargazers]: https://github.com/CarbonPackages/Carbon.Plausible/stargazers
[subscription]: https://github.com/CarbonPackages/Carbon.Plausible/subscription
[screenshot of backend module]: https://user-images.githubusercontent.com/4510166/105641544-c5abd980-5e84-11eb-8515-80ae5bda9000.png
[error in the backend module]: https://user-images.githubusercontent.com/4510166/105641545-c80e3380-5e84-11eb-8fda-31a080e990e2.png
[video of the inspector]: https://user-images.githubusercontent.com/4510166/105546876-29f06100-5cfe-11eb-98d3-a5f37ad5af2a.gif
[neos]: https://www.neos.io
[plausible]: https://plausible.io
[plausible analytics tracking script code]: https://docs.plausible.io/plausible-script
[plausible domain]: https://docs.plausible.io/add-website
[carbon.plausible.mixin:customevent]: Configuration/NodeTypes.Mixin.CustomEvent.yaml
[carbon.plausible.mixin:donottrack]: Configuration/NodeTypes.Mixin.DoNotTrack.yaml
[custom events]: https://plausible.io/docs/custom-event-goals
[`settings.yaml`]: Configuration/Settings.Carbon.yaml
[custom domain]: https://plausible.io/docs/custom-domain
[outbound link click tracking]: https://plausible.io/docs/outbound-link-click-tracking
[hash-based routing]: https://plausible.io/docs/hash-based-routing
[exclude specific pages]: https://plausible.io/docs/excluding-pages
[exclusion variant]: https://plausible.io/docs/excluding
[carbon.plausible:component.trackingcode]: Resources/Private/Fusion/Component/TrackingCode.fusion
[neos.neos:page]: Resources/Private/Fusion/Override/Page.fusion
