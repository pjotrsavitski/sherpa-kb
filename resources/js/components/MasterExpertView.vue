<template>
    <div class="mt-4">
        <div class="text-right">
            <b-button-group class="mb-4">
                <b-button
                    :variant="showStatistics ? 'secondary' : 'outline-secondary'"
                    v-b-tooltip.hover
                    title="Toggle statistics"
                    @click="onToggleStatistics()"
                >
                    <font-awesome-icon :icon="['fas', 'table']" />
                </b-button>
                <b-dropdown
                    id="language-expert-view"
                    dropup
                    text="Open language expert view"
                    :variant="selectedLanguage ? 'secondary' : 'outline-secondary'"
                >
                    <b-dropdown-item
                        v-for="language in languages"
                        href="#"
                        v-bind:key="language.code"
                        class="text-uppercase text-center"
                        @click="onLanguageSelected(language)"
                        :disabled="selectedLanguage && selectedLanguage.code === language.code"
                    >
                        {{ language.name }}
                    </b-dropdown-item>
                </b-dropdown>
            </b-button-group>
        </div>

        <statistics-table v-if="showStatistics"></statistics-table>

        <transition name="fade" mode="out-in">
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
                <b-tab lazy>
                    <template v-slot:title>
                        Categories
                        <b-badge :variant="tabTitleBadgeVariant(3)" pill>{{ topics.length }}</b-badge>
                    </template>

                    <topics-table :items="topics"></topics-table>
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

                <language-expert-view :language="selectedLanguage.code" :is-busy="isBusy"></language-expert-view>
            </b-card>
        </transition>
    </div>
</template>

<script>
    import { mapState } from 'vuex'
    import QuestionsTable from './Question/Table.vue'
    import PendingQuestionsTable from './PendingQuestion/Table.vue'
    import AnswersTable from './Answer/Table.vue'
    import StatisticsTable from './Statistics/Table.vue'
    import TopicsTable from './Topic/Table.vue'
    import { library } from '@fortawesome/fontawesome-svg-core'
    import { faTimes, faTable } from '@fortawesome/free-solid-svg-icons'

    library.add(faTimes)
    library.add(faTable)

    export default {
        components: {
            QuestionsTable,
            PendingQuestionsTable,
            AnswersTable,
            StatisticsTable,
            TopicsTable
        },
        data() {
            return {
                tabIndex: 0,
                selectedLanguage: null,
                isBusy: false,
                showStatistics: false
            }
        },
        computed: {
            ...mapState({
                languages: state => state.app.languages,
                topics: state => state.topics.items
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
                if (this.selectedLanguage && language && this.selectedLanguage.code !== language.code) {
                    this.isBusy = true
                    this.selectedLanguage = language

                    setTimeout(() => {
                        this.isBusy = false
                    }, 500)
                } else {
                    this.selectedLanguage = language
                }
            },
            onToggleStatistics() {
                this.showStatistics = !this.showStatistics
            }
        },
        created() {
            this.$store.dispatch('answers/preloadAllAnswers')
            this.$store.dispatch('questions/preloadAllQuestions')
            this.$store.dispatch('pendingQuestions/preloadAllPendingQuestions')
            this.$store.dispatch('topics/preloadAllTopics')
            this.$root.$emit('init-app-sync')
        }
    }
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}
.fade-enter, .fade-leave-to {
  opacity: 0;
}
table.b-table[aria-busy='true'] {
  opacity: 0.6;
}
</style>
