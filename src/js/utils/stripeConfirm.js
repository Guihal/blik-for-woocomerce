import { getBlikCode } from './getBlikCode'
import { getClientSecret } from './getClientSecret'
import { setError } from './setError'
import { success } from './success'

export async function stripeConfirm(popup, timer) {
    const clientSecret = await getClientSecret()
    const code = getBlikCode()
    if (clientSecret === false) return false

    const stripe = Stripe(blikObject.publicKey)
    // const stripe = Stripe('pk_live_51QKip9LwIfxaeCGwpCG6C7ETLQvCd2XYOGTC4ZZRvqdo6JiWG77n0GIkLucDu2jELwlOe12FlKjjgZLLwJGT0Cyc00oBdQ4d2A')

    const { error } = await stripe.confirmBlikPayment(clientSecret, {
        payment_method: {
            blik: {},
        },
        payment_method_options: {
            blik: {
                code: code,
            },
        },
    })

    if (error != undefined) {
        console.log(error.message)
        // setError('error')
        timer.stop()
        popup.close()
        return
    }

    console.log(error)
    popup.close()
    timer.stop()
    success()
}
