export async function setError(error) {
    const errorInput = document.querySelector('[name="blik_error"]')
    const order = document.querySelector('#place_order')
    if (!errorInput || !order) return

    errorInput.value = error
    order.click()
}
