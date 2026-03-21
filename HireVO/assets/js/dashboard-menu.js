document.addEventListener('DOMContentLoaded', function () {
    var profileMenu = document.getElementById('profile-menu');
    if (!profileMenu) {
        return;
    }

    var trigger = profileMenu.querySelector('.profile-trigger');
    if (!trigger) {
        return;
    }

    function closeMenu() {
        profileMenu.classList.remove('open');
        trigger.setAttribute('aria-expanded', 'false');
    }

    trigger.addEventListener('click', function (event) {
        event.stopPropagation();
        var isOpen = profileMenu.classList.toggle('open');
        trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });

    document.addEventListener('click', function (event) {
        if (!profileMenu.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });
});
