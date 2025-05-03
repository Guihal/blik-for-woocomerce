export function addPopupElement() {
    const popup = Object.assign(document.createElement('div'), {
        id: 'blikPopup',
        className: 'popup',
        innerHTML: `
        <div id="openBlikPopup"></div>
        <div class="popup__wrapper">
            <div class="popup_close"></div>
            <div class="popup_timer"></div>
            <div class="popup_text">
                ${blikObject.textInPopup}
            </div>
            <div class="timer">

            </div>
        </div>`,
    })

    document.addEventListener('DOMContentLoaded', () => {
        document.documentElement.append(popup)
    })
}
