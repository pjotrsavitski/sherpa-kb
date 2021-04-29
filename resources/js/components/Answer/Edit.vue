<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Edit answer"
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
                        :disabled="!canEdit()"
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
                        :disabled="!canEdit()"
                    ></b-form-textarea>
                </b-form-group>
                <b-form-group
                    description="Changing status to Translated will send the answer for review by SELFIE master. You and other Language Experts would still be able to make changes as needed."
                >
                    <b-form-checkbox
                        v-model="form.translated"
                        name="translated"
                        switch
                        :disabled="!canChangeStatus()"
                    >
                        <b>Change status to translated</b>
                    </b-form-checkbox>
                </b-form-group>

                <div class="text-right" v-if="canDelete()">
                    <b-button
                        variant="danger"
                        @click="handleDelete()"
                        :disabled="isBusy"
                        size="sm"
                    >
                        Delete
                    </b-button>
                </div>
            </form>
        </b-modal>
</template>

<script>
    import { mapState } from 'vuex'
    import ToastHelpers from '../../mixins/ToastHelpers'

    export default {
        props: ['language', 'answer'],
        mixins: [ToastHelpers],
        computed: {
            ...mapState({
                languages: state => state.app.languages
            }),
            modalId() {
                return `answer-edit`
            },
            totalLanguages() {
                return this.$store.getters['app/totalLanguages']
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
                    translated: false,
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
                this.form.answer = this.answer.descriptions.hasOwnProperty(this.language) ? this.answer.descriptions[this.language] : ''
                this.form.translation = this.answer.descriptions.en
                this.form.translated = false
                this.form.state = {
                    answer: this.form.answer.length > 0,
                    translation: this.form.translation.length > 0
                }
            },
            canEdit() {
                return this.answer.status.value === 'in_translation' || this.answer.status.value === 'translated'
            },
            canSave() {
                return this.canEdit() && (this.isEnglish ? this.form.state.answer : this.form.state.answer && this.form.state.translation)
            },
            canDelete() {
                return this.canEdit()
            },
            canChangeStatus() {
                // TODO Check if this check is correct (saving last missing language should allow status to be changed)
                return this.answer.status.value === 'in_translation' && this.form.state.answer && this.form.state.translation
                //return this.answer.status.value === 'in_translation' && Object.keys(this.answer.descriptions).length === this.totalLanguages
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

                if (this.form.translated) {
                    data.status = 'translated'
                }

                axios.put(`/answers/${this.answer.id}`, data)
                .then(response => {
                    this.isBusy = false
                    this.$store.dispatch('answers/updateAnswer', response.data)

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
            },
            handleDelete() {
                this.$bvModal.msgBoxConfirm(`Are you sure you want to delete answer with ID of ${this.answer.id}?`,
                    {
                        title: 'Please confirm',
                        size: 'sm',
                        buttonSize: 'sm',
                        okVariant: 'danger',
                        okTitle: 'Confirm',
                        cancelTitle: 'Cancel',
                        footerClass: 'p-2',
                        hideHeaderClose: false,
                        centered: true
                    })
                    .then(value => {
                        if (value) {
                            this.isBusy = true
                            this.$store.dispatch('answers/deleteAnswer', this.answer)
                                .then(() => {
                                    this.isBusy = false
                                    this.$nextTick(() => {
                                        this.$bvModal.hide(this.modalId)
                                    })
                                })
                                .catch(err => {
                                    this.isBusy = false
                                    console.error(err)
                                    this.displayHttpError(err)
                                })
                        }
                    })
                    .catch(err => {
                        console.error('Delete answer confirmation dialog error', err)
                    })
            }
        }
    }
</script>
