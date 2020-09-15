<template>
        <b-modal
            ref="modal"
            :id="modalId"
            title="Create user"
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
                    id="input-group-name"
                    label="Name"
                    label-for="input-name"
                    invalid-feedback="Name is required"
                    :state="nameState"
                >
                    <b-form-input
                        id="input-name"
                        v-model="form.name"
                        type="text"
                        required
                        :state="nameState"
                        trim
                        :disabled="!canEdit()"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-email"
                    label="Email"
                    label-for="input-emai"
                    invalid-feedback="Email is required"
                    :state="emailState"
                >
                    <b-form-input
                        id="input-email"
                        v-model="form.email"
                        type="email"
                        required
                        :state="emailState"
                        trim
                        :disabled="!canEdit()"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-password"
                    label="Password"
                    label-for="input-password"
                >
                    <b-form-input
                        id="input-password"
                        v-model="form.password"
                        type="password"
                        trim
                        required
                        :disabled="!canEdit()"
                    ></b-form-input>
                </b-form-group>
                <b-form-group
                    id="input-group-confirmation"
                    label="Password confirmation"
                    label-for="input-confirmation"
                >
                    <b-form-input
                        id="input-confirmation"
                        v-model="form.confirmation"
                        type="password"
                        trim
                        required
                        :disabled="!canEdit()"
                    ></b-form-input>
                </b-form-group>
            </form>
        </b-modal>
</template>

<script>
    export default {
        computed: {
            modalId() {
                return 'user-create'
            },
            nameState() {
                return (this.form.name && this.form.name.length) > 0 ? true : false
            },
            emailState() {
                return (this.form.email && this.form.email.length) > 0 ? true : false
            }
        },
        data() {
            return {
                form: {
                    name: "",
                    email: "",
                    password: "",
                    confirmation: ""
                },
                isBusy: false
            };
        },
        methods: {
            resetModal() {
                this.form.name = ""
                this.form.email = ""
                this.form.password = ""
                this.form.confirmation = ""
            },
            canEdit() {
                return true
            },
            canSave() {
                return this.canEdit() && this.nameState && this.emailState
            },
            handleSave(bvModelEvent) {
                bvModelEvent.preventDefault()
                this.handleSubmit()
            },
            handleSubmit() {
                if (!this.$refs.form.checkValidity()) {
                    return
                }

                this.isBusy = true;
                axios.post('/users', {
                    name: this.form.name,
                    email: this.form.email,
                    password: this.form.password,
                    password_confirmation: this.form.confirmation
                })
                .then(response => {
                    this.isBusy = false;
                    this.$root.$emit('userCreated', response.data)

                    this.$nextTick(() => {
                        this.$bvModal.hide(this.modalId)
                    });
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
            }
        }
    }
</script>
