<template>
    <div class="mt-4">
        <b-tabs
            v-model="tabIndex"
            content-class="mt-4"
            active-nav-item-class="bg-primary text-white"
            fill
        >
            <b-tab lazy>
                <template v-slot:title>
                    Questions
                    <b-badge :variant="tabTitleBadgeVariant(0)" pill>{{ questions.length }}</b-badge>
                </template>

                <questions-table :items="questions" :language="language"></questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Pending questions
                    <b-badge :variant="tabTitleBadgeVariant(1)" pill>{{ pendingQuestions.length }}</b-badge>
                </template>

                <pending-questions-table :items="pendingQuestions" :language="language"></pending-questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Answers
                    <b-badge :variant="tabTitleBadgeVariant(2)" pill>{{ answers.length }}</b-badge>
                </template>
            </b-tab>
        </b-tabs>
    </div>
</template>

<script>
    export default {
        props: ['language'],
        mounted() {
            axios.get('/pending_questions', {
                params: {
                    language: this.language

                }
            })
                .then(response => {
                    this.pendingQuestions = response.data.data;
                })
                .catch(error => {
                    console.error('Pending questions loading:', error);
                });

            axios.get('/questions', {
                params: {
                    language: this.language
                }
            })
                .then(response => {
                    this.questions = response.data.data;
                })
                .catch(error => {
                    console.error('Questions loading:', error);
                });
        },
        data() {
            return {
                tabIndex: 0,
                questions: [],
                pendingQuestions: [],
                answers: []
            };
        },
        methods: {
            tabTitleBadgeVariant(index) {
                return this.tabIndex === index ? 'light' : 'secondary';
            }
        }
    }
</script>
