<template>
    <div class="mt-4">
        <div class="text-right">
            <b-dropdown
                id="language-expert-view"
                dropup
                text="Open language expert view"
                variant="outline-secondary"
                class="mb-4"
            >
                <b-dropdown-item
                    v-for="language in languages"
                    href="#"
                    v-bind:key="language.code"
                    class="text-uppercase text-center"
                    @click="onLanguageSelected(language)"
                >
                    {{ language.name }}
                </b-dropdown-item>
            </b-dropdown>
        </div>

        <b-tabs
            v-model="tabIndex"
            content-class="mt-4"
            active-nav-item-class="bg-primary text-white"
            fill
            v-if="!selectedLanguage"
        >
            <b-tab lazy>
                <template v-slot:title>
                    Questions
                    <b-badge :variant="tabTitleBadgeVariant(0)" pill>{{ questions.length }}</b-badge>
                </template>

                <questions-table :items="questions"></questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Pending questions
                    <b-badge :variant="tabTitleBadgeVariant(1)" pill>{{ pendingQuestions.length }}</b-badge>
                </template>

                <pending-questions-table :items="pendingQuestions"></pending-questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Answers
                    <b-badge :variant="tabTitleBadgeVariant(2)" pill>{{ answers.length }}</b-badge>
                </template>

                <answers-table :items="answers"></answers-table>
            </b-tab>
        </b-tabs>

        <b-card
            v-if="selectedLanguage"
        >
            <template v-slot:header>
                <h3 class="mb-0">
                    Country SELFIE Expert {{ selectedLanguage.name }}
                    <b-button
                        v-b-tooltip
                        title="Clear selected language"
                        @click="onLanguageSelected(null)"
                        size="sm"
                        variant="outline-secondary"
                    >
                        <font-awesome-icon :icon="['fas', 'times']" />
                    </b-button>
                </h3>
            </template>

            <language-expert-view :language="selectedLanguage.code"></language-expert-view>
        </b-card>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import QuestionsTable from './Question/Table.vue'
    import PendingQuestionsTable from './PendingQuestion/Table.vue'
    import AnswersTable from './Answer/Table.vue'
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faTimes } from '@fortawesome/free-solid-svg-icons'

    library.add(faTimes)
    
    export default {
        components: {
            QuestionsTable,
            PendingQuestionsTable,
            AnswersTable
        },
        data() {
            return {
                tabIndex: 0,
                selectedLanguage: null
            }
        },
        computed: {
            ...mapState({
                languages: state => state.app.languages
            }),
            questions() {
                return this.$store.getters['questions/forReview']
            },
            pendingQuestions() {
                return this.$store.getters['pendingQuestions/forReview']
            },
            answers() {
                return this.$store.getters['answers/forReview']
            }
        },
        methods: {
            tabTitleBadgeVariant(index) {
                return this.tabIndex === index ? 'light' : 'secondary'
            },
            onLanguageSelected(language) {
                this.selectedLanguage = language
            }
        },
        created() {
            this.$store.dispatch('questions/preloadAllQuestions')
            this.$store.dispatch('pendingQuestions/preloadAllPendingQuestions')
            this.$store.dispatch('answers/preloadAllAnswers')
        }
    }
</script>
