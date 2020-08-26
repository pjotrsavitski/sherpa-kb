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
        }
    }
}