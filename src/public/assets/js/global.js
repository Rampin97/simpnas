feather.replace();

const modalLoading = new bootstrap.Modal(document.getElementById('pageLoadingModal'), {
    keyboard: false,
    backdrop: 'static',
    focus: true
});

const formsList = document.querySelectorAll("form");

formsList.forEach(elem => {
    elem.addEventListener("submit", () => {
        modalLoading.show();
    });
});

window.addEventListener('beforeunload', e => {
    modalLoading.show();
    // the absence of a returnValue property on the event will guarantee the browser unload happens
    // https://developer.mozilla.org/en-US/docs/Web/API/WindowEventHandlers/onbeforeunload
    delete e['returnValue'];
});
