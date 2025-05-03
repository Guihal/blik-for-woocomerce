import { elementReady } from './utils/elementReady'
import { Popup } from './utils/Popup'
import { setTimer } from './utils/setTimer'
import { stripeConfirm } from './utils/stripeConfirm'

export async function blikPopup() {
    const [block, closeBtn, openBtnSelector] = [await elementReady('#blikPopup'), await elementReady('#blikPopup .popup_close'), '#openBlikPopup']
    const popup = new Popup({
        block: block,
        closeBtn: closeBtn,
        openBtnSelector: openBtnSelector,
        unClosable: true,
        onClose: () => {},
    })
    const timer = await setTimer(popup)
    const hiddenInput = await elementReady('[name="blik_pass"]')

    // document.addEventListener('click', async (ev) => {
    //     if (ev.target.id !== 'place_order' || hiddenInput.value !== 'no') return
    //     stripeConfirm(popup, timer)
    //     showPopupOnSubmit(popup)
    //     timer.restart()
    // })
}

function isBlikChecked() {
    const blikInput = document.querySelector('[value="blik-for-woocomerce_guiha"]')
    if (!blikInput) return false
    if (!blikInput.checked) return false

    return true
}

function showPopupOnSubmit(popup) {
    console.log(isBlikChecked())
    if (!isBlikChecked()) return

    popup.open()
}
