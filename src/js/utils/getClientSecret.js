export function getClientSecret() {
    return new Promise(async (resolve, reject) => {
        const promise = await fetch(`/wc-api/get_client_secret_blik`)

        if (!promise.ok) {
            console.error('Ошибка HTTP: ' + promise.status)
            reject(false)
            return
        }

        const response = await promise.json()
        console.log(response)
        if (response.status === 'error') {
            reject(false)
            return
        }

        resolve(response.client_secret)
    })
}
