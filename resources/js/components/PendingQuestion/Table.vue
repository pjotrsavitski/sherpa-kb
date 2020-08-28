<template>
    <div>
        <h3>Pending questions</h3>
        
        <b-table
            striped
            hover
            :fields="fields"
            :items="items"
            primary-key="id"
            thead-class="text-center"
            :per-page="perPage"
            :current-page="currentPage"
            stacked="lg"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            :busy="isBusy"
        >
            <template v-slot:cell(id)="data">
                {{ data.value }}
                <pending-question-edit :pending-question="data.item" v-if="language"></pending-question-edit>
                <pending-question-review :pending-question="data.item" v-if="!language"></pending-question-review>
            </template>

            <template v-slot:cell(description)="data">
                <b-button v-b-modal="editModalId(data.item.id)" variant="link">{{ getDescription(data.item) }}</b-button>
            </template>

            <template v-slot:cell(english_translation)="data">
                <b-button v-b-modal="editModalId(data.item.id)" variant="link" :class="{ 'text-secondary': !hasEnglishDescription(data.item) }">{{ englishTranslationOrPlaceholderText(getEnglishDescription(data.item)) }}</b-button>
            </template>

            <template v-slot:cell(group)="data">
                {{ data.value }}
            </template>

            <template v-slot:cell(date)="data">
                {{ formatDate(data.value) }}
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
    import { mapState } from 'vuex'
    import PendingQuestionEdit from './Edit.vue'
    import PendingQuestionReview from './Review.vue'
    import TableHelpers from '../../mixins/TableHelpers'

    export default {
        props: ['items', 'language', 'isBusy'],
        mixins: [TableHelpers],
        components: {
            PendingQuestionEdit,
            PendingQuestionReview
        },
        computed: {
            ...mapState({
                perPage: state => state.app.itemsPerPage
            }),
            totalRows() {
                return this.items.length
            }
        },
        data() {
            return {
                currentPage: 1,
                sortBy: 'id',
                sortDesc: true,
                fields: [
                    {
                        key: 'id',
                        label: 'ID',
                        sortable: true,
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
                        key: 'group',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'date',
                        sortable: false,
                        tdClass: ['align-middle', 'text-center']
                    },
                    {
                        key: 'status',
                        sortable: false,
                        tdClass: (value, key, item) => {
                            let classes = ['align-middle', 'text-center']

                            switch(value.value) {
                                case 'pending':
                                    classes.push('table-warning')
                                    break
                                case 'completed':
                                    classes.push('table-success')
                                    break
                                case 'canceled':
                                    classes.push('table-danger')
                            }

                            return classes
                        },
                    }
                ]
            }
        },
        methods: {
            getDescription(item) {
                const languages = Object.keys(item.descriptions)

                if (languages.length === 1 && languages[0] === 'en') {
                    return item.descriptions.en
                }

                return (languages[0] !== 'en') ? item.descriptions[languages[0]] : item.descriptions[languages[1]]
            },
            hasEnglishDescription(item) {
                return item.descriptions.hasOwnProperty('en')
            },
            getEnglishDescription(item) {
                return this.hasEnglishDescription(item) ? item.descriptions.en : null
            },
            englishTranslationOrPlaceholderText(value) {
                return (value && value.trim()) ? value : 'Add English translation'
            },
            editModalId(id) {
                const type = this.language ? 'edit' : 'review'
                return 'pending-question-' + type + '-' + id
            }
        },
        created() {
            this.$store.dispatch('pendingQuestions/preloadStates')
        }
    }
</script>
