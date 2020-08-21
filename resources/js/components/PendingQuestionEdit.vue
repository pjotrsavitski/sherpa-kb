<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Edit pending question"
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
                    label="Question"
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
                    label="English translation"
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
                        :disabled="!canEdit() || isEnglishOnly()"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    description="Changing status to Propagated will send the question for review by SELFIE master. This would also prevent you from making any changes to the question itself or English translation."
                >
                    <b-form-checkbox v-model="form.propagate" name="propagate" switch :disabled="!canBePropagated()">
                        <b>Change status to propagated</b>
                    </b-form-checkbox>
                </b-form-group>
            </form>
        </b-modal>
</template>

<script>
    export default {
        props: ['pendingQuestion'],
        computed: {
            modalId() {
                return 'pending-question-edit-' + this.pendingQuestion.id;
            },
            questionState() {
                return (this.form.question && this.form.question.length) > 0 ? true : false;
            },
            translationState() {
                return (this.form.translation && this.form.translation.length) > 0 ? true : false;
            }
        },
        data() {
            return {
                form: {
                    question: "",
                    translation: "",
                    propagate: false
                },
                isBusy: false
            };
        },
        methods: {
            getDescription(item) {
                const languages = Object.keys(item.descriptions);

                if (languages.length === 1 && languages[0] === 'en') {
                    return item.descriptions.en;
                }

                return (languages[0] !== 'en') ? item.descriptions[languages[0]] : item.descriptions[languages[1]];
            },
            getEnglishDescription(item) {
                return item.descriptions.hasOwnProperty('en') ? item.descriptions.en : '';
            },
            resetModal() {
                this.form.question = this.getDescription(this.pendingQuestion);
                this.form.translation = this.getEnglishDescription(this.pendingQuestion);
                this.form.propagate = false;
            },
            canBePropagated() {
                return this.form.question
                    && this.form.translation
                    && this.form.question.trim()
                    && this.form.translation.trim()
                    && this.pendingQuestion.status.value === 'pending';
            },
            isEnglishOnly() {
                const languages = Object.keys(this.pendingQuestion.descriptions);

                return languages.length === 1 && languages[0] === 'en';
            },
            canEdit() {
                return this.pendingQuestion.status.value === 'pending';
            },
            canSave() {
                return this.canEdit();
            },
            handleSave(bvModelEvent) {
                bvModelEvent.preventDefault();
                this.handleSubmit();
            },
            handleSubmit() {
                if (!this.$refs.form.checkValidity()) {
                    return;
                }

                this.isBusy = true;
                axios.put('/pending_questions/' + this.pendingQuestion.id, {
                    question: this.form.question,
                    translation: this.form.translation,
                    propagate: this.form.propagate,
                })
                .then(response => {
                    this.isBusy = false;
                    this.$store.dispatch('pendingQuestions/updatePendingQuestion', response.data)

                    this.$nextTick(() => {
                        this.$bvModal.hide(this.modalId);
                    });
                })
                .catch(error => {
                    this.isBusy = false;
                    console.error(error);

                    let message = error.message;

                    if (error.response && error.response.data && error.response.data.message) {
                        message = error.response.data.message;
                    }

                    this.$bvToast.toast(message, {
                        variant: 'danger',
                        solid: true,
                        autoHideDelay: 2500,
                        noCloseButton: true
                    });
                });
            }
        }
    }
</script>
