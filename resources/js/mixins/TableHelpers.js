import { mapGetters, mapState } from 'vuex'

export default {
    computed: {
        ...mapGetters({
            totalLanguages: 'app/totalLanguages'
        }),
        ...mapState({
            languages: state => state.app.languages
        }),
    },
    methods: {
        languagesPopoverData(item) {
            const languages = this.languages.filter(language => {
                return item.descriptions.hasOwnProperty(language.code)
            })
            .map(language => {
                return language.name
            })

            return {
                title: 'Translated to languages',
                content: languages.join(', ')
            }
        },
        languagesButtonVariant(item) {
            return Object.keys(item.descriptions).length >= this.totalLanguages ? 'outline-success' : 'outline-secondary'
        },
        formatDate(dateString) {
            const date = new Date(dateString)

            return `${date.getDate().toString().padStart(2, '0')}.${(date.getMonth() + 1).toString().padStart(2, '0')}.${date.getFullYear()}`
        },
        descriptionInLanguageOrEnglish(descriptions, language) {
            return descriptions.hasOwnProperty(language) ? descriptions[language] : descriptions.en
        },
        shortenDescription(description, length) {
            // TODO See if there is a cleaner way to proide default value
            if (!length) {
                length = 50
            }

            // TODO It might make sense to remove newlines before checking length
            if (description.length > length) {
                return `${description.substr(0, length)}...`
            }

            return description
        }
    }
}