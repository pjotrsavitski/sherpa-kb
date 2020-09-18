export default {
    methods: {
        displayHttpError(error) {
            const h = this.$createElement
            const messages = []
            let autoHideDelay = 2500
            let message = error.message
            
            if (error.response && error.response.data && error.response.data.message) {
                message = error.response.data.message
            }

            messages.push(h(
                'strong',
                { class: ['text-center', 'mb-1', 'd-block'] },
                message
            ))

            if (error.response && error.response.data && error.response.data.errors) {
                autoHideDelay = 5000
                const elements = []

                Object.entries(error.response.data.errors).forEach(entry => {
                    const [key, value] = entry

                    elements.push(h(
                        'dt',
                        { class: ['text-capitalize'] },
                        key
                    ))

                    value.forEach(text => {
                        elements.push(h(
                            'dd',
                            { class: ['text-center'] },
                            text
                        ))
                    })
                })

                messages.push(h(
                    'dl',
                    elements
                ))
            }
            
            this.$bvToast.toast(messages, {
                variant: 'danger',
                solid: true,
                autoHideDelay: autoHideDelay,
                noCloseButton: true
            })
        }
    }
}