<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Add new question"
            ok-title="Create"
            @show="resetModal"
            @hidden="resetModal"
            @ok="handleSave"
            :ok-disabled="!canSave()"
            :busy="isBusy"
        >
            <form
                ref="form"
                @submit.stop.prevent="handleSubmit"
            >
                <b-form-group
                    id="input-group-question"
                    :label="questionLabel(language)"
                    label-for="input-question"
                    invalid-feedback="Question is required"
                    :state="form.state.question"
                >
                    <b-form-input
                        id="input-question"
                        v-model="form.question"
                        type="text"
                        required
                        :state="form.state.question"
                        trim
                        @update="updateInputState('question', ...arguments)"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-translation"
                    :label="questionLabel('en')"
                    label-for="input-translation"
                    invalid-feedback="English translation is required"
                    :state="form.state.translation"
                    v-if="!isEnglish"
                >
                    <b-form-input
                        id="input-translation"
                        v-model="form.translation"
                        type="text"
                        required
                        :state="form.state.translation"
                        trim
                        @update="updateInputState('translation', ...arguments)"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-topic"
                    label="Category"
                    label-for="input-topic"
                >
                    <b-form-select
                        v-model="form.topic"
                        :options="topicOptions"
                        id="input-topic"
                    ></b-form-select>
                </b-form-group>
                <b-form-group
                    id="input-group-answer"
                    label="Answer"
                    label-for="input-answer"
                >
                    <form-answer
                        :options="answerOptions"
                        v-model="form.answer"
                        :language="language"
                    ></form-answer>
                </b-form-group>
            </form>
        </b-modal>
</template>

<script>
    import { mapState, mapGetters } from 'vuex'
    import ToastHelpers from '../../mixins/ToastHelpers'
    import FormAnswer from '../Input/FormAnswer'

    export default {
        props: ['language'],
        components: {
            FormAnswer
        },
        mixins: [ToastHelpers],
        computed: {
            ...mapState({
                languages: state => state.app.languages
            }),
            ...mapGetters({
                answers: 'answers/forQuestion',
                topics: 'topics/sortedAsc'
            }),
            modalId() {
                return 'question-create'
            },
            topicOptions() {
                const options = this.topics.map(topic => {
                    return {
                        value: topic.id,
                        text: topic.description
                    }
                })
                options.unshift({
                    value: '',
                    text: ''
                })

                return options
            },
            answerOptions() {
                const options = this.answers
                .map(answer => {
                    return {
                        value: answer.id,
                        text: answer.descriptions.hasOwnProperty(this.language) ? answer.descriptions[this.language] : answer.descriptions.en
                    }
                })

                return options
            },
            isEnglish() {
                return this.language === 'en'
            }
        },
        data() {
            return {
                form: {
                    question: '',
                    translation: '',
                    topic: '',
                    answer: '',
                    state: {
                        question: null,
                        translation: null
                    }
                },
                isBusy: false
            }
        },
        methods: {
            resetModal() {
                this.form.question = ''
                this.form.translation = ''
                this.form.topic = ''
                this.form.answer = ''
                this.form.state.question = null
                this.form.state.translation = null
            },
            canSave() {
                if (this.isEnglish) {
                    return this.form.state.question
                }

                return this.form.state.question && this.form.state.translation
            },
            handleSave(bvModelEvent) {
                bvModelEvent.preventDefault()
                this.handleSubmit()
            },
            handleSubmit() {
                if (!this.$refs.form.checkValidity()) {
                    return
                }

                this.isBusy = true

                const data = {
                    descriptions: [],
                    status: this.form.status
                }

                if (!this.isEnglish) {
                    data.descriptions.push({
                        code: 'en',
                        value: this.form.translation
                    })
                }

                data.descriptions.push({
                    code: this.language,
                    value: this.form.question
                })

                if (this.form.topic) {
                    data.topic = this.form.topic
                }

                if (this.form.answer) {
                    data.answer = this.form.answer
                }

                axios.post('/questions', data)
                .then(response => {
                    this.isBusy = false
                    this.$store.dispatch('questions/insertQuestion', response.data)

                    this.$nextTick(() => {
                        this.$bvModal.hide(this.modalId)
                    })
                })
                .catch(error => {
                    this.isBusy = false
                    console.error(error)
                    this.displayHttpError(error)
                })
            },
            questionLabel(code) {
                const language = this.languages.find(language => language.code === code)

                return `Question in ${language ? language.name : code}`
            },
            updateInputState(input, value) {
                this.form.state[input] = value.length > 0
            }
        }
    }
</script>
