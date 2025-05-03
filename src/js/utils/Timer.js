export class Timer {
    currentTime
    interval

    constructor(timer, time, callbackStop = null) {
        this.timer = timer
        this.time = time

        if (callbackStop !== null && typeof callbackStop === 'function') {
            this.callbackStop = callbackStop
        }
    }

    start() {
        if (this.currentTime === 0) {
            this.restart()
        }

        this.interval = setInterval(() => {
            if (this.currentTime === 0) {
                this.stop(false)
            }
            this.currentTime--
            this.updateTime()
        }, 1000)
    }

    stop(withCallback = true) {
        clearInterval(this.interval)
        if (withCallback) this.callbackStop()
    }

    restart() {
        if (this.interval !== undefined) {
            this.stop()
        }
        this.currentTime = this.time
        this.start()
    }

    updateTime() {
        this.timer.textContent = this.currentTime
    }

    callbackStop() {}
}
