/**
* Theme: Ubold - Responsive Bootstrap 5 Admin Dashboard
* Author: Coderthemes
* Module/App: Theme Config Js
*/

(function () {
    var savedConfig = sessionStorage.getItem("__UBOLD_CONFIG__");
    // var savedConfig = localStorage.getItem("__UBOLD_CONFIG__");

    var html = document.getElementsByTagName("html")[0];

    //  Default Config Value
    var defaultConfig = {
        theme: "light",

        layout: {
            mode: "default",
            width: "default",
        },

        topbar: {
            color: "light",
        },

        menu: {
            color: "light",
            icon: "default",
        },

        // This option for only vertical (left Sidebar) layout
        sidenav: {
            size: "default",
            twocolumn: "light",
            user: false,
        },
    };

    window.html = html;

    var config = Object.assign(JSON.parse(JSON.stringify(defaultConfig)), {});

    var layoutColor = html.getAttribute('data-bs-theme');
    config['theme'] = layoutColor !== null ? layoutColor : defaultConfig.theme;

    var layoutMode = html.getAttribute('data-layout-mode');
    config['layout']['mode'] = layoutMode != null ? layoutMode : defaultConfig.layout.mode;

    var layoutWidth = html.getAttribute('data-layout-width');
    config['layout']['width'] = layoutWidth != null ? layoutWidth : defaultConfig.layout.width;

    var topbarColor = html.getAttribute('data-topbar-color');
    config['topbar']['color'] = topbarColor != null ? topbarColor : defaultConfig.topbar.color;

    var leftbarSize = html.getAttribute('data-sidenav-size');
    config['sidenav']['size'] = leftbarSize !== null ? leftbarSize : defaultConfig.sidenav.size;

    var sidebarUser = html.getAttribute('data-sidenav-user')
    config['sidenav']['user'] = sidebarUser !== null ? true : defaultConfig.sidenav.user;

    var menuColor = html.getAttribute('data-menu-color');
    config['menu']['color'] = menuColor !== null ? menuColor : defaultConfig.menu.color;

    var menuIcon = html.getAttribute('data-menu-icon');
    config['menu']['icon'] = menuIcon !== null ? menuIcon : defaultConfig.menu.icon;

    var twocolumnColor = html.getAttribute('data-two-column-color');
    config['sidenav']['twocolumn'] = twocolumnColor !== null ? twocolumnColor : defaultConfig.sidenav.twocolumn;

    window.defaultConfig = JSON.parse(JSON.stringify(config));

    if (savedConfig !== null) {
        config = JSON.parse(savedConfig);
    }

    window.config = config;

    if (config) {
        html.setAttribute("data-bs-theme", config.theme);
        html.setAttribute("data-layout-mode", config.layout.mode);
        html.setAttribute("data-layout-width", config.layout.width);
        html.setAttribute("data-topbar-color", config.topbar.color);
        html.setAttribute("data-menu-color", config.menu.color);
        html.setAttribute("data-menu-icon", config.menu.icon);
        html.setAttribute("data-sidenav-size", config.sidenav.size);

        if (html.getAttribute("data-layout") === "two-column") {
            html.setAttribute("data-two-column-color", config.sidenav.twocolumn);
        }

        if (config.sidenav.user && config.sidenav.user.toString() === "true") {
            html.setAttribute("data-sidenav-user", true);
        } else {
            html.removeAttribute("data-sidenav-user");
        }
    }

    if (window.innerWidth <= 1040) {
        html.setAttribute("data-sidenav-size", "full");
        if (html.getAttribute("data-layout") === "horizontal") {
            html.setAttribute("data-layout", "vertical");
        }
    }

})();