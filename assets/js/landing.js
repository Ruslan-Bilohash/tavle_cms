document.addEventListener('DOMContentLoaded', function () {
    var burger = document.getElementById('ldBurger');
    var nav = document.getElementById('ldNav');
    if (burger && nav) burger.addEventListener('click', function () { nav.classList.toggle('open'); });

    var langBtn = document.getElementById('ldLangBtn');
    var langMenu = document.getElementById('ldLangMenu');
    if (langBtn && langMenu) {
        langBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            langMenu.hidden = !langMenu.hidden;
        });
        document.addEventListener('click', function () { langMenu.hidden = true; });
    }
});