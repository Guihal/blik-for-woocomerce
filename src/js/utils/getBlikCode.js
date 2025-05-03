export function getBlikCode() {
    const input = document.querySelector('[name="blik_token"]')
    if (!input) return

    return input.value
}
