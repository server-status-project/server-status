(function (factory) {
  if (typeof define === 'function' && define.amd) {
    define(['jquery'], factory);
  } else if (typeof module === 'object' && typeof module.exports === 'object') {
    factory(require('jquery'));
  } else {
    factory(jQuery);
  }
}(function (jQuery) {
  // English (Template)
  jQuery.timeago.settings.strings = {
    prefixAgo: null,
    prefixFromNow: null,
    suffixAgo: "siden",
    suffixFromNow: "fra nå",
    seconds: "mindre en ett minutt",
    minute: "ca ett minutt",
    minutes: "%d minutter",
    hour: "ca en time",
    hours: "ca %d timer",
    day: "en dag",
    days: "%d dager",
    month: "ca en måned",
    months: "%d måneder",
    year: "about a year",
    years: "%d år",
    wordSeparator: " ",
    numbers: []
  };
}));
