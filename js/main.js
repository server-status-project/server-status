function timeago() {
  $("time.timeago").timeago();
  $("time.timeago").each(function () {
    var date = new Date($(this).attr("datetime"));
    $(this).attr("title", date.toLocaleString());
  });
}

(function () {
  jQuery.timeago.settings.allowFuture = true;

  timeago();

  $("body").on("click", ".navbar-toggle", function () {
    $($(this).data("target")).toggleClass("collapse");
  });

  var incidents = $(".timeline");
  $("body").on("click", "#loadmore", function (e) {
    e.preventDefault();
    var url = $("#loadmore").attr("href") + "&ajax=true";
    $("#loadmore").remove();

    $.get(url, function (data) {
      incidents.append(data);
      timeago();
    });
  });
})();

var darkSwitch = document.getElementById("darkSwitch");
window.addEventListener("load", function () {
  if (darkSwitch) {
    initTheme();
    darkSwitch.addEventListener("change", function () {
      resetTheme();
    });
  }
});

function initTheme() {
  var darkThemeSelected = localStorage.getItem("darkSwitch") !== null && localStorage.getItem("darkSwitch") === "dark";
  darkSwitch.checked = darkThemeSelected;
  darkThemeSelected ? document.body.setAttribute("data-theme", "dark") : document.body.removeAttribute("data-theme");
}

function resetTheme() {
  if (darkSwitch.checked) {
    document.body.setAttribute("data-theme", "dark");
    localStorage.setItem("darkSwitch", "dark");
  } else {
    document.body.removeAttribute("data-theme");
    localStorage.removeItem("darkSwitch");
  }
}

if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
  document.body.setAttribute("data-theme", "dark");
}
window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
  const newColorScheme = e.matches ? document.body.setAttribute("data-theme", "dark") : document.body.removeAttribute("data-theme");
});