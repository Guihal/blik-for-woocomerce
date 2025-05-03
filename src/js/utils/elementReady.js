export async function elementReady(selector, parent = false) {
    return new Promise((resolve) => {
        const observer = new MutationObserver(callback)

        observer.observe(parent ? parent : document.documentElement, { childList: true, subtree: true })

        callback()
        function callback() {
            const block = parent ? parent.querySelector(selector) : document.querySelector(selector)

            if (!block) return

            resolve(block)
            observer.disconnect()
        }
    })
}
