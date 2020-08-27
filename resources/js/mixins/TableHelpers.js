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
        }
    }
}