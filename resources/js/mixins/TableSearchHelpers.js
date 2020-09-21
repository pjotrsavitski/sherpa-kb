export default {
    computed: {
        filteredItems() {
            if (this.form.search !== '') {
                const allowedLanguages = ['en']

                if (!allowedLanguages.includes(this.language)) {
                    allowedLanguages.push(this.language)
                }

                return this.items.filter(item => {
                    let descriptions = []

                    if (this.language) {
                        descriptions = Object.entries(item.descriptions).filter(entry => {
                            return allowedLanguages.includes(entry[0])
                        }).map(entry => {
                            return entry[1]
                        })
                    } else {
                        descriptions = Object.values(item.descriptions)
                    }
                    // TODO Consider using regular expression
                    return descriptions.join(' ').toLowerCase().includes(this.form.search.toLowerCase())
                })
            }
            
            return this.items
        },
        totalRows() {
            return this.filteredItems.length
        },
    },
    data() {
        return {
            form: {
                search: ''
            }
        }
    }
}