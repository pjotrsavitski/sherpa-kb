<template>
    <div class="mt-4">
        <b-tabs
            content-class="mt-4"
            fill
        >
            <b-tab active lazy>
                <template v-slot:title>
                    Questions
                    <b-badge variant="secondary" pill>{{ questions.length }}</b-badge>
                </template>

                <questions-table :items="questions"></questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Pending questions
                    <b-badge variant="secondary" pill>{{ pendingQuestions.length }}</b-badge>
                </template>

                <pending-questions-table :items="pendingQuestions"></pending-questions-table>
            </b-tab>
            <b-tab lazy>
                <template v-slot:title>
                    Answers
                    <b-badge variant="secondary" pill>{{ answers.length }}</b-badge>
                </template>
            </b-tab>
        </b-tabs>
    </div>
</template>

<script>
    export default {
        mounted() {
            axios.get('/pending_questions')
                .then(response => {
                    this.pendingQuestions = response.data.data;
                })
                .catch(error => {
                    console.error('Pending questions loading:', error);
                });

            axios.get('/questions')
                .then(response => {
                    this.questions = response.data.data;
                })
                .catch(error => {
                    console.error('Questions loading:', error);
                });
        },
        data() {
            return {
                questions: [],
                pendingQuestions: [],
                answers: []
            };
        }
    }
</script>
