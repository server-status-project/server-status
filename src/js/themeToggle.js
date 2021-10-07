if (sessionStorage.getItem("pref-theme") === "dark") {
    document.body.classList.add('dark');
} else if (sessionStorage.getItem("pref-theme") === "light") {
    document.body.classList.remove('dark')
} else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.body.classList.add('dark');
}

function themeToggle() {
    if (document.body.className.includes("dark")) {
        document.body.classList.remove("dark");
        sessionStorage.setItem("pref-theme", "light");
        _paq.push(["trackEvent", "pref-theme", "light"]);
    } else {
        document.body.classList.add("dark");
        sessionStorage.setItem("pref-theme", "dark");
        _paq.push(["trackEvent", "pref-theme", "dark"]);
    }
}
