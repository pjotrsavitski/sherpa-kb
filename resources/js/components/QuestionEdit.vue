<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Edit question"
            ok-title="Save"
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
                    :state="questionState"
                >
                    <b-form-input
                        id="input-question"
                        v-model="form.question"
                        type="text"
                        required
                        :state="questionState"
                        trim
                        :disabled="!canEdit()"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-translation"
                    :label="questionLabel('en')"
                    label-for="input-translation"
                    invalid-feedback="English translation is required"
                    :state="translationState"
                    description="Translation is not needed if suggestion was submitted in English."
                >
                    <b-form-input
                        id="input-translation"
                        v-model="form.translation"
                        type="text"
                        required
                        :state="translationState"
                        trim
                        :disabled="!canEdit()"
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
                        :disabled="!canEdit()"
                    ></b-form-select>
                </b-form-group>
                <b-form-group
                    id="input-group-sttaus"
                    label="Status"
                    label-for="input-sttaus"
                >
                    <b-form-select
                        v-model="form.status"
                        :options="statusOptions"
                        id="input-status"
                        :disabled="!canEdit()"
                    ></b-form-select>
                </b-form-group>
            </form>
        </b-modal>
</template>

<script>
    import { mapState } from 'vuex'

    export default {
        props: ['question', 'language'],
        computed: {
            ...mapState({
                states: state => state.questions.states,
                topics: state => state.questions.topics,
                languages: state => state.app.languages
            }),
            modalId() {
                return `question-edit-${this.question.id}`
            },
            questionState() {
                return (this.form.question && this.form.question.length) > 0 ? true : false
            },
            translationState() {
                return (this.form.translation && this.form.translation.length) > 0 ? true : false
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
            statusOptions() {
                return this.states
                .filter(state => state.value !== 'published')
                .map(state => {
                    return {
                        value: state.value,
                        text: state.text,
                        disabled: ((this.question.status.transitionable.indexOf(state.value) !== -1 || this.question.status.value === state.value)) ? false : true
                    }
                })
            }
        },
        data() {
            return {
                form: {
                    question: "",
                    translation: "",
                    topic: "",
                    status: ""
                },
                isBusy: false
            }
        },
        methods: {
            getDescription(item) {
                return item.descriptions[this.language]
            },
            getEnglishDescription(item) {
                return item.descriptions.hasOwnProperty('en') ? item.descriptions.en : ''
            },
            resetModal() {
                this.form.question = this.getDescription(this.question)
                this.form.translation = this.getEnglishDescription(this.question)
                this.form.topic = this.question.topic ? this.question.topic.id : ''
                this.form.status = this.question.status.value
            },
            canEdit() {
                return this.question.status.value === 'in_translation'
            },
            canSave() {
                return this.canEdit()
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
                data.descriptions.push({
                    code: 'en',
                    value: this.form.translation
                })
                data.descriptions.push({
                    code: this.language,
                    value: this.form.question
                })

                if (this.form.topic) {
                    data.topic = this.form.topic
                }

                axios.put(`/questions/${this.question.id}`, data)
                .then(response => {
                    this.isBusy = false
                    this.$store.dispatch('questions/updateQuestion', response.data)

                    this.$nextTick(() => {
                        this.$bvModal.hide(this.modalId)
                    })
                })
                .catch(error => {
                    this.isBusy = false
                    console.error(error)

                    let message = error.message

                    if (error.response && error.response.data && error.response.data.message) {
                        message = error.response.data.message
                    }

                    this.$bvToast.toast(message, {
                        variant: 'danger',
                        solid: true,
                        autoHideDelay: 2500,
                        noCloseButton: true
                    })
                })
            },
            questionLabel(code) {
                const language = this.languages.find(language => language.code === code)

                return `Question in ${language ? language.name : code}`
            }
        }
    }
</script>
