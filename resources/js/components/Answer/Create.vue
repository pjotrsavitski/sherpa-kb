<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Add new answer"
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
                    id="input-group-answer"
                    :label="answerLabel(language)"
                    label-for="input-answer"
                    invalid-feedback="Answer is required"
                    :state="form.state.answer"
                >
                    <b-form-textarea
                        id="input-answer"
                        v-model="form.answer"
                        required
                        :state="form.state.answer"
                        rows="2"
                        max-rows="6"
                        trim
                        debounce="250"
                        @update="updateAnswerState"
                    ></b-form-textarea>
                </b-form-group>
                <b-form-group
                    id="input-group-translation"
                    :label="answerLabel('en')"
                    label-for="input-translation"
                    invalid-feedback="English translation is required"
                    :state="form.state.translation"
                >
                    <b-form-textarea
                        id="input-translation"
                        v-model="form.translation"
                        required
                        :state="form.state.translation"
                        rows="2"
                        max-rows="6"
                        trim
                        debounce="250"
                        @update="updateTranslationState"
                    ></b-form-textarea>
                </b-form-group>
            </form>
        </b-modal>
</template>

<script>
    import { mapState } from 'vuex'

    export default {
        props: ['language'],
        computed: {
            ...mapState({
                languages: state => state.app.languages
            }),
            modalId() {
                return 'question-create'
            }
        },
        data() {
            return {
                form: {
                    answer: '',
                    translation: '',
                    state: {
                        answer: null,
                        translation: null
                    }
                },
                isBusy: false
            }
        },
        methods: {
            resetModal() {
                this.form.answer = ''
                this.form.translation = ''
                this.form.state = {
                    answer: null,
                    translation: null
                }
            },
            canSave() {
                return this.form.state.answer && this.form.state.translation
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
                    descriptions: []
                }
                data.descriptions.push({
                    code: 'en',
                    value: this.form.translation
                })
                data.descriptions.push({
                    code: this.language,
                    value: this.form.answer
                })

                axios.post('/answers', data)
                .then(response => {
                    this.isBusy = false
                    this.$store.dispatch('answers/insertAnswer', response.data)

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
            answerLabel(code) {
                const language = this.languages.find(language => language.code === code)

                return `Answer in ${language ? language.name : code}`
            },
            updateInputState(value, name) {
                this.form.state[name] = value.length > 0
            },
            updateAnswerState(value) {
                this.updateInputState(value, 'answer')
            },
            updateTranslationState(value) {
                this.updateInputState(value, 'translation')
            }
        }
    }
</script>
