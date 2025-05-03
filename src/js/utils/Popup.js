export class Popup {
    unClosable = false

    constructor(options) {
        if (!this.checkOptions(options)) return
        this.init(options)
        this.initEvents()
    }

    initEvents() {
        document.addEventListener('click', (ev) => {
            this.openEvent(ev)
            this.closeEvent(ev)
        })
    }

    init(options) {
        this.block = options.block
        this.closeBtn = options.closeBtn
        this.openBtnSelector = options.openBtnSelector

        this.optional(options)
    }

    optional(options) {
        if ('onOpen' in options) {
            if (typeof options.onOpen != 'function') {
                console.error('onOpen' + ' не функция!')
                return
            }
            this.onOpen = options.onOpen
        }

        if ('onClose' in options) {
            if (typeof options.onClose != 'function') {
                console.error('onClose' + ' не функция!')
                return
            }
            this.onClose = options.onClose
        }

        if ('unClosable' in options) {
            this.unClosable = options.unClosable
        }
    }

    openEvent(ev) {
        if (!ev.target.closest(this.openBtnSelector)) return
        this.open()
        this.onOpen()
    }

    closeEvent(ev) {
        if (!ev.target.isEqualNode(this.closeBtn) && !ev.target.isEqualNode(this.block)) return
        if (this.unClosable) return

        this.close()
        this.onClose()
    }

    open() {
        console.log('opened')
        console.log(this.block)
        document.documentElement.classList.add('ovr-hidden')
        this.block.classList.add('show')
    }

    close() {
        document.documentElement.classList.remove('ovr-hidden')
        this.block.classList.remove('show')
    }

    checkOptions(options) {
        if (!'block' in options) {
            console.error('Не передан попап!')
            return false
        }
        if (!'closeBtn' in options) {
            console.error('Не передана кнопка закрыть!')
            return false
        }
        if (!'openBtnSelector' in options) {
            console.error('Не передан селектор кнопки открыть!')
            return false
        }

        return true
    }

    onOpen() {}
    onClose() {}
}
