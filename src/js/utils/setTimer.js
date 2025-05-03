import { elementReady } from './elementReady'
import { setError } from './setError'
import { Timer } from './Timer'

export async function setTimer(popup) {
    const timerBlock = await elementReady('.popup_timer')

    const timer = new Timer(timerBlock, 60, () => {
        popup.close()
        // setError(`You didn't make it. Please repeat it again.`)
    })

    return timer
}
