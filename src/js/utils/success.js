export function success() {
    const blikPass = document.querySelector('[name="blik_pass"]')
    const order = document.querySelector('#place_order')
    const errorInput = document.querySelector('[name="blik_error"]')
    console.log(blikPass, order, errorInput)
    if (!blikPass || !order || !errorInput) return

    blikPass.value = 'yes'
    errorInput.value = ''
    order.click()
}
