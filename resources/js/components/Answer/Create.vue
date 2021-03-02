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
                        @update="updateInputState('answer', ...arguments)"
                    ></b-form-textarea>
                </b-form-group>
                <b-form-group
                    id="input-group-translation"
                    :label="answerLabel('en')"
                    label-for="input-translation"
                    invalid-feedback="English translation is required"
                    :state="form.state.translation"
                    v-if="!isEnglish"
                >
                    <b-form-textarea
                        id="input-translation"
                        v-model="form.translation"
                        required
                        :state="form.state.translation"
                        rows="2"
                        max-rows="6"
                        trim
                        @update="updateInputState('translation', ...arguments)"
                    ></b-form-textarea>
                </b-form-group>
            </form>
        </b-modal>
</template>

<script>
    import { mapState } from 'vuex'
    import ToastHelpers from '../../mixins/ToastHelpers'

    export default {
        props: ['language'],
        mixins: [ToastHelpers],
        computed: {
            ...mapState({
                languages: state => state.app.languages
            }),
            modalId() {
                return 'answer-create'
            },
            isEnglish() {
                return this.language === 'en'
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
                if (this.isEnglish) {
                    return this.form.state.answer
                }

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

                if (!this.isEnglish) {
                    data.descriptions.push({
                        code: 'en',
                        value: this.form.translation
                    })
                }

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
                    this.displayHttpError(error)
                })
            },
            answerLabel(code) {
                const language = this.languages.find(language => language.code === code)

                return `Answer in ${language ? language.name : code}`
            },
            updateInputState(name, value) {
                this.form.state[name] = value.length > 0
            }
        }
    }
</script>
