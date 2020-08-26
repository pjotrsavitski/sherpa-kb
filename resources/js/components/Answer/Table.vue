<template>
    <div>
        <h3>Answers</h3>
        
        <b-table
            striped
            hover
            :fields="fields"
            :items="items"
            primary-key="id"
            thead-class="text-center"
            stacked="lg"
            :per-page="perPage"
            :current-page="currentPage"
        >
            <template v-slot:cell(id)="data">
                {{ data.value }}
                <answer-edit :answer="data.item" :language="language" v-if="language"></answer-edit>
                <answer-review :answer="data.item" v-if="!language"></answer-review>
            </template>

            <template v-slot:cell(description)="data" v-if="language">
                <b-button v-b-modal="modalId(data.item.id)" variant="link" :class="{ 'text-secondary': !hasDescription(data.item) }" v-b-popover.hover.top="popoverData(data.item, language)">{{ descriptionOrPlaceholderText(data.item) }}</b-button>
            </template>

            <template v-slot:cell(english_translation)="data">
                <b-button v-b-modal="modalId(data.item.id)" variant="link" v-b-popover.hover.top="popoverData(data.item, 'en')">{{ data.item.descriptions.en }}</b-button>
            </template>

            <template v-slot:cell(languages)="data">
                <b-button
                    pill
                    :variant="answerLanguagesVariant(data.item)"
                    v-b-popover.hover.click.blur.top="languagesPopoverData(data.item)"
                >
                    {{ descriptionsCount(data.item) }} / {{ totalLanguages }}
                </b-button>
            </template>

            <template v-slot:cell(date)="data">
                {{ data.value }}
            </template>

            <template v-slot:cell(status)="data">
                {{ data.item.status.status }}
            </template>
        </b-table>

        <b-pagination
          v-model="currentPage"
          :total-rows="totalRows"
          :per-page="perPage"
          align="center"
          v-if="totalRows > perPage"
        ></b-pagination>
    </div>
</template>

<script>
    import { mapGetters, mapState } from 'vuex'
    import AnswerEdit from './Edit.vue'
    import AnswerReview from './Review.vue'

    export default {
        props: ['items', 'language'],
        components: {
            AnswerEdit,
            AnswerReview
        },
        computed: {
            ...mapGetters({
                totalLanguages: 'app/totalLanguages'
            }),
            ...mapState({
                languages: state => state.app.languages,
                perPage: state => state.app.itemsPerPage
            }),
            totalRows() {
                return this.items.length
            },
            fields() {
                const fields = [
                    {
                        key: 'id',
                        label: 'ID',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'description',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'english_translation',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'date',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'languages',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'status',
                        sortable: false,
                        tdClass: (value, key, item) => {
                            const classes = ['align-middle', 'text-center']

                            switch(value.value) {
                                case 'in_translation':
                                    classes.push('table-warning')
                                    break
                                case 'translated':
                                    classes.push('table-info')
                                    break
                                case 'published':
                                    classes.push('table-success')
                            }

                            return classes
                        },
                    }
                ]

                if (!this.language) {
                    return fields.filter(field => field.key !== 'description')
                }

                return fields
            }
        },
        data() {
            return {
                currentPage: 1
            }
        },
        methods: {
            modalId(id) {
                const type = this.language ? 'edit' : 'review'
                return `answer-${type}-${id}`
            },
            descriptionsCount(item) {
                return Object.keys(item.descriptions).length
            },
            hasDescription(item) {
                return item.descriptions.hasOwnProperty(this.language) && item.descriptions[this.language].trim()
            },
            descriptionOrPlaceholderText(item) {
                return this.hasDescription(item) ? item.descriptions[this.language] : 'Add translation'
            },
            popoverData(item, code) {
                return {
                    content: item.descriptions.hasOwnProperty(code) ? item.descriptions[code] : '',
                    customClass: 'popover-preserve-new-lines'
                }
            },
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
            answerLanguagesVariant(item) {
                return this.descriptionsCount(item) >= this.totalLanguages ? 'outline-success' : 'outline-secondary'
            }
        },
        created() {
            this.$store.dispatch('answers/preloadStates')
        }
    }
</script>

<style>
.popover-preserve-new-lines .popover-body{
    white-space: pre-wrap;
}
</style>
